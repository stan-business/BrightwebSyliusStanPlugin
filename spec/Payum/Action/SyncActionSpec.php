<?php

namespace spec\Brightweb\SyliusStanPlugin\Payum\Action;

use ArrayAccess;
use Brightweb\SyliusStanPlugin\Payum\Action\SyncAction;
use Brightweb\SyliusStanPlugin\Payum\Request\Api\GetPayment;
use PhpSpec\ObjectBehavior;

use Payum\Core\Request\Sync;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

class SyncActionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SyncAction::class);
        $this->shouldImplement(GatewayAwareInterface::class);
        $this->shouldImplement(ActionInterface::class);
    }

    function it_executes_when_stan_payment_id_is_set(
        Sync $request,
        GatewayInterface $gateway
    )
    {
        $this->setGateway($gateway);

        $details = new ArrayObject([
            'stan_payment_id' => 'payment_id'
        ]);

        $request->getModel()->willReturn($details);

        $gateway->execute(new GetPayment($details))->shouldBeCalled();

        $this->execute($request);
    }

    function it_executes_when_stan_payment_id_is_not_set(
        Sync $request,
        GatewayInterface $gateway
    )
    {
        $this->setGateway($gateway);

        $details = new ArrayObject();

        $request->getModel()->willReturn($details);

        $gateway->execute(new GetPayment($details))->shouldNotBeCalled();

        $this->execute($request);
    }

    public function it_supports_only_sync_request(Sync $request, ArrayAccess $model): void {
        $request->getModel()->willReturn($model);

        $this->supports($request)->shouldReturn(true);
    }
}
