<?php

namespace spec\Brightweb\SyliusStanPlugin\Payum\Action;

use Brightweb\SyliusStanPlugin\Payum\Action\NotifyNullAction;
use PhpSpec\ObjectBehavior;

class NotifyNullActionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NotifyNullAction::class);
    }
}
