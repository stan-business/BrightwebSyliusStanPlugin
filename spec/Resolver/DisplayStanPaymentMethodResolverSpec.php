<?php

namespace spec\Brightweb\SyliusStanPlugin\Resolver;

use Brightweb\SyliusStanPlugin\Resolver\DisplayStanPaymentMethodResolver;
use PhpSpec\ObjectBehavior;

use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;

class DisplayStanPaymentMethodResolverSpec extends ObjectBehavior
{
    public function let(
        PaymentMethodsResolverInterface $decoratedPaymentMethodsResolver
    )
    {
        $this->beConstructedWith($decoratedPaymentMethodsResolver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DisplayStanPaymentMethodResolver::class);
    }
}
