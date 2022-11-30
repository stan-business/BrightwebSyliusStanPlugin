<?php

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface StanConnectInterface extends ResourceInterface
{

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return string|null
     */
    public function getClientId(): ?string;

    /**
     * @param string|null $clientId
     */
    public function setClientId(?string $clientId): void;

    /**
     * @return string|null
     */
    public function getClientSecret(): ?string;

    /**
     * @param string|null $clientSecret
     */
    public function setClientSecret(?string $clientSecret): void;
}
