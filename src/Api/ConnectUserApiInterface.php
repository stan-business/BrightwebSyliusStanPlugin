<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
*/

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Api;

use Stan\Model\User;

interface ConnectUserApiInterface
{
    public function getUserWithAuthorizationCode(string $code): ?User;

    public function getConnectUrl(string|null $state): string;

    public function getRedirectUri(): string;
}
