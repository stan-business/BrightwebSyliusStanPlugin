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

use Stan\Configuration;
use Stan\Api\StanClient as Api;
use Stan\Model\ApiSettingsRequestBody;
use Stan\Model\Customer;
use Stan\Model\CustomerRequestBody;
use Stan\Model\Payment;
use Stan\Model\PaymentRequestBody;
use Stan\Model\PreparedPayment;

use Stan\ApiException;

final class StanPayClient implements StanPayClientInterface
{

    public const STAN_API_URL = 'https://api-staging.stan-app.fr/v1'; // TODO get from env

    public const STAN_MODE_TEST = 'TEST';

    public const STAN_MODE_LIVE = 'LIVE';

    public const PAYMENT_PREPARED = 'payment_prepared';

    public const PAYMENT_PENDING = 'payment_pending';

    public const PAYMENT_FAILURE = 'payment_failure';

    public const PAYMENT_SUCCESS = 'payment_success';

    public const PAYMENT_CANCELLED = 'payment_cancelled';

    public const PAYMENT_HOLDING = 'payment_holding';

    public array $options;

    public function __construct(array $options) {
        $this->options = $options;
    }

    public function preparePayment(PaymentRequestBody $paymentBody): PreparedPayment
    {
        $apiClient = $this->getApiClient();
        return $apiClient->paymentApi->create($paymentBody);
    }

    public function getPayment(string $paymentId): Payment
    {
        $apiClient = $this->getApiClient();

        return $apiClient->paymentApi->getPayment($paymentId);
    }

    public function createCustomer(CustomerRequestBody $customerBody): Customer
    {
        $apiClient = $this->getApiClient();

        return $apiClient->customerApi->create($customerBody);
    }

    public function updateApiSettings(ApiSettingsRequestBody $apiSettings): void
    {
        $this->getApiClient()->apiSettingsApi->updateApiSettings($apiSettings);
    }

    private function getApiClient(): Api
    {
        /** @var string $environment */
        $environment = $this->options['environment'];

        /** @var string $confApiClientId */
        $confApiClientId = $environment === self::STAN_MODE_TEST
            ? $this->options['client_test_id']
            : $this->options['client_id'];

        /** @var string $confApiClientSecret */
        $confApiClientSecret = $environment === self::STAN_MODE_TEST
            ? $this->options['client_test_secret']
            : $this->options['client_secret'];

        $apiConfig = new Configuration();
        $apiConfig
            ->setHost(self::STAN_API_URL)
            ->setClientId($confApiClientId)
            ->setClientSecret($confApiClientSecret)
        ;

        return new Api($apiConfig);
    }
}
