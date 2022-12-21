<?php

namespace spec\Brightweb\SyliusStanPlugin\Payum\Action;

use ArrayAccess;
use Brightweb\SyliusStanPlugin\Payum\Action\NotifyAction;
use PhpSpec\ObjectBehavior;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpResponse;

class NotifyActionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NotifyAction::class);
        $this->shouldImplement(GatewayAwareInterface::class);
        $this->shouldImplement(ActionInterface::class);
    }

    public function it_executes(
        Notify $request,
        GatewayInterface $gateway
    )
    {
        $this->setGateway($gateway);

        $details = new ArrayObject();

        $request->getModel()->willReturn($details);

        $gateway->execute(new Sync($details))->shouldBeCalled();

        $this->shouldThrow(new HttpResponse('OK', 200))->during('execute', array($request));
    }

    public function it_supports_only_notify_request(Notify $request, ArrayAccess $model): void
    {
        $request->getModel()->willReturn($model);

        $this->supports($request)->shouldReturn(true);
    }
}
