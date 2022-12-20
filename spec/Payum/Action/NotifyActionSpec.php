<?php

namespace spec\Brightweb\SyliusStanPlugin\Payum\Action;

use Brightweb\SyliusStanPlugin\Payum\Action\NotifyAction;
use PhpSpec\ObjectBehavior;

class NotifyActionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NotifyAction::class);
    }
}
