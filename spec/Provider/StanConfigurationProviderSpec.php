<?php

namespace spec\Brightweb\SyliusStanPlugin\Provider;

use Brightweb\SyliusStanPlugin\Provider\StanConfigurationProvider;
use PhpSpec\ObjectBehavior;

use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;

class StanConfigurationProviderSpec extends ObjectBehavior
{
    public function let(
        PaymentMethodRepositoryInterface $paymentMethodRepository
    )
    {
        $this->beConstructedWith($paymentMethodRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(StanConfigurationProvider::class);
    }
}
