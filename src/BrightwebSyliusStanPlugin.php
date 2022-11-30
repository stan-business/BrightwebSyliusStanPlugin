<?php

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;

final class BrightwebSyliusStanPlugin extends Bundle
{
    use SyliusPluginTrait;

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedDrivers(): array
    {
        return [
            SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
        ];
    }
}
