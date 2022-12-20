<?php

namespace spec\Brightweb\SyliusStanPlugin\Payum\Action;

use Brightweb\SyliusStanPlugin\Payum\Action\SyncAction;
use PhpSpec\ObjectBehavior;

class SyncActionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SyncAction::class);
    }
}
