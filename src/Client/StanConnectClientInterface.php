<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
 */

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Client;

use Stan\Model\User;

interface StanConnectClientInterface
{
    public function getAccessToken(string $code, string $redirectUri): string;

    public function getUser(string $accessToken): User;

    public function getConnectUrl(string $redirectUri, string $state): string;
}
