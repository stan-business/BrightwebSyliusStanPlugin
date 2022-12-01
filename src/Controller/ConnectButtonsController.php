<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
*/

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Controller;

use Brightweb\SyliusStanPlugin\Api\ConnectUserApiInterface;
use Brightweb\SyliusStanPlugin\Provider\StanConfigurationProviderInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ConnectButtonsController
{
    private Environment $twig;

    private ChannelContextInterface $channelContext;

    private CartContextInterface $cartContext;

    private ConnectUserApiInterface $stanConnectApi;

    private StanConfigurationProviderInterface $stanConfigurationProvider;

    public function __construct(
        Environment $twig,
        ChannelContextInterface $channelContext,
        CartContextInterface $cartContext,
        ConnectUserApiInterface $stanConnectApi,
        StanConfigurationProviderInterface $stanConfigurationProvider,
    ) {
        $this->twig = $twig;
        $this->channelContext = $channelContext;
        $this->cartContext = $cartContext;
        $this->stanConnectApi = $stanConnectApi;
        $this->stanConfigurationProvider = $stanConfigurationProvider;
    }

    public function renderAddressingButton(Request $request): Response
    {
        if (false === $this->stanConfigurationProvider->getStanConnectEnabled($this->channelContext->getChannel())) {
            return new Response('');
        }

        $order = $this->cartContext->getCart();
        $customer = $order->getCustomer();

        // dont display Stan Connect if infos already filled
        if (null !== $customer) {
            return new Response('');
        }

        try {
            return new Response($this->twig->render('@BrightwebSyliusStanPlugin/stan_connect_button.html.twig', [
                'connect_url' => $this->stanConnectApi->getConnectUrl(),
            ]));
        } catch (\InvalidArgumentException $exception) {
            return new Response('');
        }
    }
}
