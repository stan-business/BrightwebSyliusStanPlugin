<?php

declare(strict_types=1);

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
*/

namespace Brightweb\SyliusStanPlugin\Payum\Factory;

use Brightweb\SyliusStanPlugin\Client\StanPayClient;
use Brightweb\SyliusStanPlugin\Payum\Action\Api\CreateCustomerAction;
use Brightweb\SyliusStanPlugin\Payum\Action\Api\GetPaymentAction;
use Brightweb\SyliusStanPlugin\Payum\Action\Api\PreparePaymentAction;
use Brightweb\SyliusStanPlugin\Payum\Action\CaptureAction;
use Brightweb\SyliusStanPlugin\Payum\Action\ConvertPaymentAction;
use Brightweb\SyliusStanPlugin\Payum\Action\NotifyAction;
use Brightweb\SyliusStanPlugin\Payum\Action\NotifyNullAction;
use Brightweb\SyliusStanPlugin\Payum\Action\StatusAction;
use Brightweb\SyliusStanPlugin\Payum\Action\SyncAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactory;
use Payum\Core\Request\GetHttpRequest;
use Stan\Configuration as StanConfiguration;

class StanPayGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        if (false === class_exists(StanConfiguration::class)) {
            throw new LogicException('You must install "stan-business/stan-php" library.');
        }

        $config->defaults([
            'payum.factory_name' => 'stan_pay',
            'payum.factory_title' => 'Stan Pay',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.notify_null' => new NotifyNullAction(new GetHttpRequest()),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),

            'payum.action.api.prepare_payment' => new PreparePaymentAction(),
            'payum.action.api.get_payment' => new GetPaymentAction(),
            'payum.action.api.create_customer' => new CreateCustomerAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'environment' => StanPayClient::STAN_MODE_TEST,
                'client_id' => '',
                'client_secret' => '',
                'client_test_id' => '',
                'client_test_secret' => '',
                'only_for_stanner' => '',
            ];
            $config->defaults((array) $config['payum.default_options']);

            $config['payum.required_options'] = ['environment', 'live_api_client_id', 'live_api_secret'];

            $config['payum.api'] = function (ArrayObject $config): StanPayClient {
                $config->validateNotEmpty((array) $config['payum.required_options']);

                return new StanPayClient([
                    'environment' => $config['environment'],
                    'client_id' => $config['live_api_client_id'],
                    'client_secret' => $config['live_api_secret'],
                    'client_test_id' => $config['test_api_client_id'],
                    'client_test_secret' => $config['test_api_secret'],
                    'only_for_stanner' => $config['only_for_stanner'],
                ]);
            };
        }
    }
}
