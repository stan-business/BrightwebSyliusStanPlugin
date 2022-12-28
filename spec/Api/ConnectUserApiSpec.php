<?php

namespace spec\Brightweb\SyliusStanPlugin\Api;

use Brightweb\SyliusStanPlugin\Api\ConnectUserApi;
use Brightweb\SyliusStanPlugin\Api\ConnectUserApiInterface;
use PhpSpec\ObjectBehavior;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Brightweb\SyliusStanPlugin\Client\StanConnectClientInterface;

use Stan\Utils\StanUtils;
use Stan\Model\User;

class ConnectUserApiSpec extends ObjectBehavior
{

    private $stanConnectClient;

    private $router;

    public function let(
        UrlGeneratorInterface $router,
        StanConnectClientInterface $stanConnectClient,
        RequestContext $req,
        StanUtils $stanUtils,
    )
    {
        $req->getScheme()->willReturn("https");
        $req->getHost()->willReturn("stan-business.fr");
        $router->getContext()->willReturn($req);

        $this->beConstructedWith($router, $stanConnectClient);

        $this->stanConnectClient = $stanConnectClient;
        $this->router = $router;
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectUserApi::class);
        $this->shouldImplement(ConnectUserApiInterface::class);
    }

    function it_should_get_user_with_valid_authorization_code()
    {
        $code = "abc";

        $this->stanConnectClient
            ->getAccessToken($code, "https://stan-business.fr/stan-connect")
            ->shouldBeCalled()
            ->willReturn("token");

        $this->stanConnectClient
            ->getUser("token")
            ->willReturn(new User());

        $this->getUserWithAuthorizationCode($code);
    }

    function it_should_get_user_with_unvalid_authorization_code()
    {
        $code = "";

        $this->stanConnectClient
            ->getAccessToken($code, "https://stan-business.fr/stan-connect")
            ->shouldBeCalled()
            ->willReturn(null);

        $this->getUserWithAuthorizationCode($code);
    }

    function it_should_get_redirect_uri()
    {
        $this->router->getContext()->shouldBeCalled();
        $this->getRedirectUri()->shouldBe("https://stan-business.fr/stan-connect");
    }

    function it_should_get_connect_url()
    {
        $state = "state";

        $this->stanConnectClient
            ->getConnectUrl("https://stan-business.fr/stan-connect", $state)
            ->shouldBeCalled()
            ->willReturn("https://stan-business.fr/stan-connect?state=state");

        $this->getConnectUrl($state);
    }
}
