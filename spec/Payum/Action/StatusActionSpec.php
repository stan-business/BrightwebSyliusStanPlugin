<?php

namespace spec\Brightweb\SyliusStanPlugin\Payum\Action;

use ArrayAccess;
use Brightweb\SyliusStanPlugin\Payum\Action\StatusAction;
use PhpSpec\ObjectBehavior;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;

use Stan\Model\Payment;

class StatusActionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(StatusAction::class);
        $this->shouldImplement(ActionInterface::class);
    }

    public function it_executes_when_stan_payment_id_is_missing(
        GetStatusInterface $request
    )
    {
        $details = new ArrayObject([]);
        $request->getModel()->willReturn($details);

        $request->markNew()->shouldBeCalled();
        $this->execute($request);
    }

    public function it_executes_when_stan_payment_status_is_missing(
        GetStatusInterface $request
    )
    {
        $details = new ArrayObject([
            'stan_payment_id' => 'test'
        ]);
        $request->getModel()->willReturn($details);

        $request->markNew()->shouldBeCalled();
        $this->execute($request);
    }

    public function it_executes_when_stan_payment_status_is_failure(
        GetStatusInterface $request
    )
    {
        $details = new ArrayObject([
            'stan_payment_id' => 'test',
            'stan_payment_status' => Payment::PAYMENT_STATUS_FAILURE
        ]);
        $request->getModel()->willReturn($details);

        $request->markFailed()->shouldBeCalled();
        $this->execute($request);
    }

    public function it_executes_when_stan_payment_status_is_expired(
        GetStatusInterface $request
    )
    {
        $details = new ArrayObject([
            'stan_payment_id' => 'test',
            'stan_payment_status' => Payment::PAYMENT_STATUS_EXPIRED
        ]);
        $request->getModel()->willReturn($details);

        $request->markFailed()->shouldBeCalled();
        $this->execute($request);
    }

    public function it_executes_when_stan_payment_status_is_prepared(
        GetStatusInterface $request
    )
    {
        $details = new ArrayObject([
            'stan_payment_id' => 'test',
            'stan_payment_status' => Payment::PAYMENT_STATUS_PREPARED
        ]);
        $request->getModel()->willReturn($details);

        $request->markNew()->shouldBeCalled();
        $this->execute($request);
    }

    public function it_executes_when_stan_payment_status_is_cancelled(
        GetStatusInterface $request
    )
    {
        $details = new ArrayObject([
            'stan_payment_id' => 'test',
            'stan_payment_status' => Payment::PAYMENT_STATUS_CANCELLED
        ]);
        $request->getModel()->willReturn($details);

        $request->markCanceled()->shouldBeCalled();
        $this->execute($request);
    }

    public function it_executes_when_stan_payment_status_is_pending(
        GetStatusInterface $request
    )
    {
        $details = new ArrayObject([
            'stan_payment_id' => 'test',
            'stan_payment_status' => Payment::PAYMENT_STATUS_PENDING
        ]);
        $request->getModel()->willReturn($details);

        $request->markPending()->shouldBeCalled();
        $this->execute($request);
    }

    public function it_executes_when_stan_payment_status_is_holding(
        GetStatusInterface $request
    )
    {
        $details = new ArrayObject([
            'stan_payment_id' => 'test',
            'stan_payment_status' => Payment::PAYMENT_STATUS_HOLDING
        ]);
        $request->getModel()->willReturn($details);

        $request->markPending()->shouldBeCalled();
        $this->execute($request);
    }

    public function it_executes_when_stan_payment_status_is_success(
        GetStatusInterface $request
    )
    {
        $details = new ArrayObject([
            'stan_payment_id' => 'test',
            'stan_payment_status' => Payment::PAYMENT_STATUS_SUCCESS
        ]);
        $request->getModel()->willReturn($details);

        $request->markCaptured()->shouldBeCalled();
        $this->execute($request);
    }

    public function it_executes_when_stan_payment_status_is_unknown(
        GetStatusInterface $request
    )
    {
        $details = new ArrayObject([
            'stan_payment_id' => 'test',
            'stan_payment_status' => 'unknown status'
        ]);
        $request->getModel()->willReturn($details);

        $request->markUnknown()->shouldBeCalled();
        $this->execute($request);
    }

    public function it_supports_only_get_status_request(GetStatusInterface $request, ArrayAccess $model): void
    {
        $request->getModel()->willReturn($model);

        $this->supports($request)->shouldReturn(true);
    }
}
