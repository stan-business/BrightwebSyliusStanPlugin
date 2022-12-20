<?php

namespace spec\Brightweb\SyliusStanPlugin\Payum\Action;

use Brightweb\SyliusStanPlugin\Payum\Action\CaptureAction;
use Brightweb\SyliusStanPlugin\Payum\Request\Api\CreateCustomer;
use Brightweb\SyliusStanPlugin\Payum\Request\Api\PreparePayment;

use ArrayAccess;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;

use Payum\Core\Payum;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Storage\IdentityInterface;

use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

class CaptureActionSpec extends ObjectBehavior
{

    public function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);

    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CaptureAction::class);
        $this->shouldImplement(ActionInterface::class);
        $this->shouldImplement(GatewayAwareInterface::class);
        $this->shouldImplement(GenericTokenFactoryAwareInterface::class);
    }

    public function it_executes_when_no_stan_payment_id_is_set(
        Capture $request,
        AddressInterface $address,
        CustomerInterface $customer,
        OrderItemInterface $orderItem,
        OrderInterface $order,
        Payum $payum,
        GenericTokenFactory $tokenFactory,
        TokenInterface $token,
        TokenInterface $notifyToken,
        IdentityInterface $identity,
        GatewayInterface $gateway
    ): void {
        $this->setGateway($gateway);

        $details = new ArrayObject();

        $request->getFirstModel()->willReturn($orderItem);
        $request->getModel()->willReturn($details);
        $request->getToken()->willReturn($token);

        $orderItem->getOrder()->willReturn($order);
        $order->getCustomer()->willReturn($customer);
        $order->getBillingAddress()->willReturn($address);
        $order->getNumber()->willReturn(1);
        $order->getTotal()->willReturn(101);
        $order->getTaxTotal()->willReturn(125);
        $order->getShippingTotal()->willReturn(102);
        $order->getOrderPromotionTotal()->willReturn(0);

        $customer->getEmail()->willReturn('john.do@stan-app.fr');

        $token->getHash()->willReturn('test');
        $token->getGatewayName()->willReturn('test');
        $token->getDetails()->willReturn($identity);
        $token->getTargetUrl()->willReturn('tokenurl.fr');

        $tokenFactory->createNotifyToken('test', $identity)->willReturn($notifyToken);
        $notifyToken->getTargetUrl()->willReturn('url');
        $notifyToken->getHash()->willReturn('test');

        $this->setGenericTokenFactory($tokenFactory);
        $payum->getTokenFactory()->willReturn($tokenFactory);

        $request->getModel()->shouldBeCalled();

        $details = new ArrayObject([
            'return_url' => 'tokenurl.fr',
            'token_hash' => 'test',
            'order_id' => '1',
            'int_amount' => 101,
            'int_subtotal_amount' => -24,
            'int_tax_amount' => 125,
            'int_shipping_amount' => 102,
            'int_discount_amount' => 0,
            'customer_firstname' => null,
            'customer_lastname' => null,
            'customer_street_address' => null,
            'customer_city' => null,
            'customer_postcode' => null,
            'customer_country_code' => null,
            'customer_email' => 'john.do@stan-app.fr',
            'customer_fullname' => null
        ]);

        $gateway->execute(new Sync($details))->shouldBeCalled();
        $gateway->execute(new PreparePayment($details))->shouldBeCalled();
        $gateway->execute(new CreateCustomer($details))->shouldBeCalled();

        $this->execute($request);
    }

    public function it_executes_when_payment_id_is_set(
        Capture $request,
        OrderItemInterface $orderItem,
        OrderInterface $order,
        GatewayInterface $gateway
    )
    {
        $this->setGateway($gateway);

        $details = new ArrayObject([
            'stan_payment_id' => 'payment_id'
        ]);

        $request->getFirstModel()->willReturn($orderItem);
        $request->getModel()->willReturn($details);
        $orderItem->getOrder()->willReturn($order);

        $gateway->execute(new Sync($details))->shouldBeCalled();

        $this->execute($request);
    }

    public function it_supports_only_capture_request(Capture $request, ArrayAccess $model): void {
        $request->getModel()->willReturn($model);

        $this->supports($request)->shouldReturn(true);
    }
}
