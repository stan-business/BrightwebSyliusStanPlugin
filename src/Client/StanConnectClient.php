<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
 */

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Client;

use Psr\Log\LoggerInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;

// use Brightweb\SyliusStanPlugin\Provider\StanConnectConfigurationProviderInterface;
use Stan\Model\User;
use Stan\Configuration;
use Stan\Utils\ConnectUtils;
use Stan\Api\StanClient as Api;
use Stan\Model\ConnectAccessTokenRequestBody;

use Stan\ApiException;

final class StanConnectClient implements StanConnectClientInterface
{
    private LoggerInterface $logger;

    // private StanConnectConfigurationProviderInterface $stanConnectConfigurationProvider;

    private ChannelContextInterface $channelContext;

    private string $baseUrl;

    public function __construct(
        LoggerInterface $logger,
        // StanConnectConfigurationProviderInterface $stanConnectConfigurationProvider,
        ChannelContextInterface $channelContext,
        string $baseUrl
    ) {
        $this->logger = $logger;
        // $this->stanConnectConfigurationProvider = $stanConnectConfigurationProvider;
        $this->channelContext = $channelContext;
        $this->baseUrl = $baseUrl;
    }

    public function getAccessToken(string $code, string $redirectUri): string
    {
        $clientId = $this->stanConnectConfigurationProvider->getClientId();
        $clientSecret = $this->stanConnectConfigurationProvider->getClientSecret();

        $accessTokenPayload = new ConnectAccessTokenRequestBody();
        $accessTokenPayload = $accessTokenPayload
            ->setClientId($clientId)
            ->setClientSecret($clientSecret)
            ->setCode($code)
            ->setGrantType('authorization_code')
            ->setScope($this->stanConnectConfigurationProvider->getScope())
            ->setRedirectUri($redirectUri);

        try {
            $client = $this->getApiClient();
            $accessTokenRes = $client
                ->connectApi
                ->createConnectAccessToken($accessTokenPayload)
            ;
            return $accessTokenRes->getAccessToken();
        } catch (ApiException $e) {
            $this
                ->logger
                ->error(sprintf('getting access token with client ID %s failed (base URL is %s): %s', $clientId, $this->baseUrl, $e))
            ;
            throw $e;
        }
    }

    public function getUser(string $accessToken): User
    {
        $client = $this->getApiClient($accessToken);

        try {
            $user = $client->userApi->getUser();
            return $user;
        } catch(ApiException $e) {
            $this
                ->logger
                ->error(sprintf('getting user infos with access token %s failed (base URL is %s): $s', $accessToken, $this->baseUrl, $e))
            ;
            throw $e;
        }
    }

    public function getConnectUrl(string $redirectUri, string $state): string
    {
        $clientId = $this->stanConnectConfigurationProvider->getClientId();
        $config = $this->getApiConfiguration();

        return ConnectUtils::generateAuthorizeRequestLink(
            $clientId,
            $redirectUri,
            $state,
            [ConnectUtils::ScopePhone, ConnectUtils::ScopeEmail, ConnectUtils::ScopeAddress, ConnectUtils::ScopeProfile],
            $config
        );
    }

    private function getApiClient(?string $accessToken = null): Api
    {
        if (null !== $accessToken) {
            $apiConfig->setAccessToken($accessToken);
        }

        return new Api($apiConfig);
    }

    private function getApiConfiguration(): Configuration
    {
        $config = new Configuration();

        $config->setHost($this->baseUrl);

        return $config;
    }
}