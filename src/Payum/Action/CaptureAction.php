<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
*/

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Payum\Action;

use ArrayAccess;
use Brightweb\SyliusStanPlugin\Payum\Request\Api\CreateCustomer;
use Brightweb\SyliusStanPlugin\Payum\Request\Api\PreparePayment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\OrderInterface;

class CaptureAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * @param Capture $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = $request->getModel();
        $details = ArrayObject::ensureArrayObject($model);

        /** @var OrderInterface $order */
        $order = $request->getFirstModel()->getOrder();

        // creates a payment
        if (null === $details['stan_payment_id']) {
            /** @var TokenInterface $token */
            $token = $request->getToken();

            $details['return_url'] = $token->getTargetUrl();
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $token->getGatewayName(),
                $token->getDetails(),
            );
            $details['token_hash'] = $notifyToken->getHash();

            $orderTotalAmount = $order->getTotal();
            $orderTaxTotalAmount = $order->getTaxTotal();

            $details['order_id'] = $order->getNumber();
            $details['int_amount'] = $orderTotalAmount;
            $details['int_subtotal_amount'] = $orderTotalAmount - $orderTaxTotalAmount;
            $details['int_tax_amount'] = $orderTaxTotalAmount;
            $details['int_shipping_amount'] = $order->getShippingTotal();
            $details['int_discount_amount'] = $order->getOrderPromotionTotal();

            $customer = $order->getCustomer();
            $billingAddress = $order->getBillingAddress();

            if (null !== $customer && null !== $billingAddress) {
                $details->replace([
                    'customer_firstname' => $billingAddress->getFirstName(),
                    'customer_lastname' => $billingAddress->getLastName(),
                    'customer_street_address' => $billingAddress->getStreet(),
                    'customer_city' => $billingAddress->getCity(),
                    'customer_postcode' => $billingAddress->getPostcode(),
                    'customer_country_code' => $billingAddress->getCountryCode(),
                    'customer_email' => $customer->getEmail(),
                    'customer_fullname' => $billingAddress->getFullName(),
                ]);

                $this->gateway->execute(new CreateCustomer($details));
            }
            $this->gateway->execute(new PreparePayment($details));
        }

        $this->gateway->execute(new Sync($details));
    }

    public function supports($request): bool
    {
        return $request instanceof Capture &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
