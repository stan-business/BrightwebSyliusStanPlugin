<?php

namespace spec\Brightweb\SyliusStanPlugin\Api;

use Brightweb\SyliusStanPlugin\Api\ConnectUserApi;
use PhpSpec\ObjectBehavior;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Brightweb\SyliusStanPlugin\Client\StanConnectClientInterface;

class ConnectUserApiSpec extends ObjectBehavior
{
    public function let(
        UrlGeneratorInterface $router,
        StanConnectClientInterface $stanConnectClient
    )
    {
        $this->beConstructedWith($router, $stanConnectClient);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectUserApi::class);
    }
}
