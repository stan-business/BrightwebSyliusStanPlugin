<?php

namespace spec\Brightweb\SyliusStanPlugin\Payum\Action;

use ArrayAccess;
use Brightweb\SyliusStanPlugin\Payum\Action\ConvertPaymentAction;
use PhpSpec\ObjectBehavior;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\GetCurrency;

class ConvertPaymentActionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ConvertPaymentAction::class);
        $this->shouldImplement(GatewayAwareInterface::class);
        $this->shouldImplement(ActionInterface::class);
    }

    function it_executes(
        Convert $request,
        PaymentInterface $payment,
        GatewayInterface $gateway
    )
    {
        $this->setGateway($gateway);

        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');

        $paymentDetails = [
            'currency_code' => 'EUR',
            'amount' => 123,
            'reason' => 'test'
        ];

        $payment->getDetails()->willReturn($paymentDetails);
        $payment->getCurrencyCode()->willReturn('EUR');
        $payment->getTotalAmount()->willReturn(123);
        $payment->getDescription()->willReturn('test');

        $request->setResult($paymentDetails)->shouldBeCalled();

        $this->execute($request);
    }

    public function it_supports_only_convert_payment_request(Convert $request, PaymentInterface $source): void {
        $request->getSource()->willReturn($source);
        $request->getTo()->willReturn('array');

        $this->supports($request)->shouldReturn(true);
    }
}
