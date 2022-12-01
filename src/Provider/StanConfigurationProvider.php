<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
 */

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Provider;

use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Webmozart\Assert\Assert;

final class StanConfigurationProvider implements StanConfigurationProviderInterface
{
    private PaymentMethodRepositoryInterface $paymentMethodRepository;

    public function __construct(PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function getStanConnectEnabled(ChannelInterface $channel): bool
    {
        $config = $this->getStanPaymentMethodConfig($channel);
        Assert::keyExists($config, 'stan_connect');
        Assert::keyExists($config['stan_connect'], 'enable_stan_connect');

        return (bool) $config['stan_connect']['enable_stan_connect'];
    }

    public function getStanConnectClientId(ChannelInterface $channel): string
    {
        $config = $this->getStanPaymentMethodConfig($channel);
        Assert::keyExists($config, 'stan_connect');
        Assert::keyExists($config['stan_connect'], 'client_id');

        return (string) $config['stan_connect']['client_id'];
    }

    public function getStanConnectClientSecret(ChannelInterface $channel): string
    {
        $config = $this->getStanPaymentMethodConfig($channel);
        Assert::keyExists($config, 'stan_connect');
        Assert::keyExists($config['stan_connect'], 'client_secret');

        return (string) $config['stan_connect']['client_secret'];
    }

    public function getScope(): string
    {
        return 'openid phone email address profile';
    }

    /**
     * @throws \InvalidArgumentException no Stan Pay method found
     */
    private function getStanPaymentMethodConfig(ChannelInterface $channel): array
    {
        $methods = $this->paymentMethodRepository->findEnabledForChannel($channel);

        /** @var PaymentMethodInterface $method */
        foreach ($methods as $method) {
            /** @var GatewayConfigInterface $gatewayConfig */
            $gatewayConfig = $method->getGatewayConfig();

            if ($gatewayConfig->getFactoryName() !== 'stan_pay') {
                continue;
            }

            return $gatewayConfig->getConfig();
        }

        throw new \InvalidArgumentException('No Stan Pay payment method defined');
    }
}
