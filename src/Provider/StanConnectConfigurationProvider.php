<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
 */

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Provider;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Brightweb\SyliusStanPlugin\Doctrine\ORM\StanConnectRepository;
use Webmozart\Assert\Assert;

use Brightweb\SyliusStanPlugin\Entity\StanConnect;

final class StanConnectConfigurationProvider implements StanConnectConfigurationProviderInterface
{
    private StanConnectRepository $stanConnectConfigRepository;

    public function __construct(StanConnectRepository $stanConnectConfigRepository)
    {
        $this->stanConnectConfigRepository = $stanConnectConfigRepository;
    }

    public function getClientId(): string
    {
        $config = $this->getConfiguration();
        return $config->getClientId();
    }

    public function getClientSecret(): string
    {
        $config = $this->getConfiguration();
        return $config->getClientSecret();
    }

    public function getScope(): string
    {
        return 'openid phone email address profile';
    }

    private function getConfiguration(): StanConnect
    {
        $configs = $this->stanConnectConfigRepository->findAll();
        if (0 === count($configs)) {
            $stanConnect = new StanConnect();
            $stanConnect->setClientId('');
            $stanConnect->setClientSecret('');
            return $stanConnect;
        }
        return array_pop($configs);
    }
}
