<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
 */

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Client;

use Stan\Model\ApiSettingsRequestBody;
use Stan\Model\Customer;
use Stan\Model\CustomerRequestBody;
use Stan\Model\Payment;
use Stan\Model\PaymentRequestBody;
use Stan\Model\PreparedPayment;

interface StanPayClientInterface
{
    public function preparePayment(PaymentRequestBody $paymentBody): PreparedPayment;

    public function getPayment(string $paymentId): Payment;

    public function createCustomer(CustomerRequestBody $customerBody): Customer;

    public function updateApiSettings(ApiSettingsRequestBody $apiSettings): void;
}
