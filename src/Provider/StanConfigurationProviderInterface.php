<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
 */

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Provider;

use Sylius\Component\Core\Model\ChannelInterface;

interface StanConfigurationProviderInterface
{
    public function getStanConnectEnabled(ChannelInterface $channel): bool;

    public function getStanConnectClientId(ChannelInterface $channel): string;

    public function getStanConnectClientSecret(ChannelInterface $channel): string;

    public function getScope(): string;
}
