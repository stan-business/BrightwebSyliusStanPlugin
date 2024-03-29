<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
*/

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Controller;

use Brightweb\SyliusStanPlugin\Api\ConnectUserApiInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Stan\ApiException;
use Stan\Model\User as StanUser;
use Sylius\Component\Core\Factory\AddressFactoryInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ConnectController
{
    private LoggerInterface $logger;

    private UrlGeneratorInterface $router;

    private CartContextInterface $cartContext;

    private AddressFactoryInterface $addressFactory;

    private FactoryInterface $customerFactory;

    private StateMachineFactoryInterface $stateMachineFactory;

    private ObjectManager $orderManager;

    private CustomerRepositoryInterface $customerRepository;

    private ConnectUserApiInterface $stanConnectApi;

    public function __construct(
        LoggerInterface $logger,
        UrlGeneratorInterface $router,
        CartContextInterface $cartContext,
        AddressFactoryInterface $addressFactory,
        FactoryInterface $customerFactory,
        StateMachineFactoryInterface $stateMachineFactory,
        ObjectManager $orderManager,
        CustomerRepositoryInterface $customerRepository,
        ConnectUserApiInterface $stanConnectApi,
    ) {
        $this->logger = $logger;
        $this->router = $router;
        $this->addressFactory = $addressFactory;
        $this->customerFactory = $customerFactory;
        $this->stateMachineFactory = $stateMachineFactory;
        $this->orderManager = $orderManager;
        $this->cartContext = $cartContext;
        $this->customerRepository = $customerRepository;
        $this->stanConnectApi = $stanConnectApi;
    }

    public function connectUserWithAuthorizationCode(Request $request): Response
    {
        /** @var OrderInterface $order */
        $order = $this->cartContext->getCart();

        /** @var string $err */
        $err = $request->query->get('error', '');
        if ($err !== '') {
            $this
                ->logger
                ->error(sprintf('connect user with authorization code (redirect URI), requested URL is %s : %s', $request->getUri(), $err))
            ;
            $this->renderError($request);

            return new RedirectResponse($this->router->generate('sylius_shop_checkout_address'));
        }

        $code = (string) $request->query->get('code');

        if ($code === "") {
            $this->renderError($request);

            return new RedirectResponse($this->router->generate('sylius_shop_checkout_address'));
        }

        try {
            $user = $this->stanConnectApi->getUserWithAuthorizationCode($code);

            if (null === $user) {
                $this->renderError($request);

                return new RedirectResponse($this->router->generate('sylius_shop_checkout_address'));
            }

            /** @var CustomerInterface|null $customer */
            $customer = $order->getCustomer();

            if (null === $customer) {
                $customer = $this->getOrderCustomer($user);
                $order->setCustomer($customer);
            }

            $stateMachine = $this->stateMachineFactory->get($order, OrderCheckoutTransitions::GRAPH);

            $address = $this->getCustomerAddress($customer, $user);

            if (null !== $address) {
                $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_ADDRESS);
                $order->setShippingAddress(clone $address);
                $order->setBillingAddress(clone $address);
            }

            $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING);

            if ($order->isShippingRequired()) {
                if (null === $address) {
                    /** @var FlashBagInterface $flashBag */
                    $flashBag = $request->getSession()->getBag('flashes');
                    $flashBag->add('info', 'brightweb.stan_plugin.need_address_info');

                    $redirect = new RedirectResponse($this->router->generate('sylius_shop_checkout_address'));
                } else {
                    $redirect = new RedirectResponse($this->router->generate('sylius_shop_checkout_select_shipping'));
                }
            } else {
                $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT);
                $redirect = new RedirectResponse($this->router->generate('sylius_shop_checkout_select_payment'));
            }

            $this->orderManager->flush();

            return $redirect;
        } catch(ApiException $e) {
            $this->renderError($request);

            return new RedirectResponse($this->router->generate('sylius_shop_checkout_address', [
                'stan_connect_error' => 'server_error',
            ]));
        }
    }

    private function getOrderCustomer(StanUser $user): CustomerInterface
    {
        /** @var CustomerInterface|null $existingCustomer */
        $existingCustomer = $this->customerRepository->findOneBy(['email' => $user->getEmail()]);
        if (null !== $existingCustomer) {
            return $existingCustomer;
        }

        /** @var CustomerInterface $customer */
        $customer = $this->customerFactory->createNew();
        $customer->setEmail($user->getEmail());
        $customer->setFirstName($user->getGivenName());
        $customer->setLastName($user->getFamilyName());
        $customer->setPhoneNumber($user->getPhone());

        return $customer;
    }

    private function getCustomerAddress(CustomerInterface $customer, StanUser $user): ?AddressInterface
    {
        $stanAddress = $user->getShippingAddress();

        if (null === $stanAddress || null === $stanAddress->getStreetAddress()) {
            return null;
        }

        $address = $this->addressFactory->createForCustomer($customer);

        $address->setFirstName($stanAddress->getFirstname() ?? $user->getGivenName());
        $address->setLastName($stanAddress->getLastname() ?? $user->getFamilyName());
        $address->setStreet($stanAddress->getStreetAddress());
        $address->setCity($stanAddress->getLocality());
        $address->setPostcode($stanAddress->getZipCode());
        $address->setCountryCode('FR'); // TODO should be variable from user infos
        $address->setPhoneNumber($user->getPhone());

        return $address;
    }

    private function renderError(Request $request): void
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', 'brightweb.stan_plugin.auth_error');
    }
}
