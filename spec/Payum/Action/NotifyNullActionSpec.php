<?php

namespace spec\Brightweb\SyliusStanPlugin\Payum\Action;

use Brightweb\SyliusStanPlugin\Payum\Action\NotifyNullAction;
use PhpSpec\ObjectBehavior;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetToken;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Request\Notify;
use Payum\Core\Reply\HttpResponse;

class NotifyNullActionSpec extends ObjectBehavior
{
    public function let(
        GetHttpRequest $httpRequest
    )
    {
        $this->beConstructedWith($httpRequest);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotifyNullAction::class);
        $this->shouldImplement(GatewayAwareInterface::class);
        $this->shouldImplement(ActionInterface::class);
    }

    public function it_executes_with_state(
        Notify $request,
        TokenInterface $token,
        GetHttpRequest $httpRequest,
        GatewayInterface $gateway
    )
    {
        $this->setGateway($gateway);

        $state = 'token_hash';
        $httpRequest->query = ['state' => $state];

        $getToken = new GetToken($state);
        $notify = new Notify(null);

        $gateway->execute($httpRequest)->shouldBeCalled();
        $gateway->execute($getToken)->shouldBeCalled();
        $gateway->execute($notify)->shouldBeCalled();

        $this->execute($request);
    }

    public function it_executes_with_empty_state(
        Notify $request,
        TokenInterface $token,
        GetToken $getToken,
        Notify $notify,
        GetHttpRequest $httpRequest,
        GatewayInterface $gateway
    )
    {
        $this->setGateway($gateway);

        $state = 'token_hash';
        $httpRequest->query = [];

        $getToken->getToken()->willReturn($token);

        $gateway->execute($httpRequest)->shouldBeCalled();

        $this->shouldThrow(new HttpResponse('state is missing in URI query', 400))->during('execute', array($request));
    }

    public function it_supports_only_notify_request(Notify $request): void
    {
        $request->getModel()->willReturn(null);

        $this->supports($request)->shouldReturn(true);
    }
}
