<?php

namespace spec\Brightweb\SyliusStanPlugin\Payum\Action;

use Brightweb\SyliusStanPlugin\Payum\Action\ConvertPaymentAction;
use PhpSpec\ObjectBehavior;

class ConvertPaymentActionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ConvertPaymentAction::class);
    }
}
