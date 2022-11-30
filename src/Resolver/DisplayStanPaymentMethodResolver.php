<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
*/

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Resolver;

use Payum\Core\Bridge\Spl\ArrayObject;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;

final class DisplayStanPaymentMethodResolver implements PaymentMethodsResolverInterface
{
    private PaymentMethodsResolverInterface $decoratedPaymentMethodsResolver;

    public function __construct(PaymentMethodsResolverInterface $decoratedPaymentMethodsResolver)
    {
        $this->decoratedPaymentMethodsResolver = $decoratedPaymentMethodsResolver;
    }

    public function getSupportedMethods(BasePaymentInterface $subject): array
    {
        $supportedMethods = $this->decoratedPaymentMethodsResolver->getSupportedMethods($subject);

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            /** @var string $userAgent */
            $userAgent = $_SERVER['HTTP_USER_AGENT'];

            foreach ($supportedMethods as $index => $method) {
                /** @var ArrayObject $gatewayConfig */
                $gatewayConfig = $method->getGatewayConfig()->getConfig();

                if (isset($gatewayConfig['only_for_stanner'])) {
                    if (true === (bool) $gatewayConfig['only_for_stanner'] && !$this->checkIfStanner($userAgent)) {
                        unset($supportedMethods[$index]);

                        break;
                    }
                }
            }
        }

        return $supportedMethods;
    }

    public function supports(BasePaymentInterface $subject): bool
    {
        return $this->decoratedPaymentMethodsResolver->supports($subject);
    }

    private function checkIfStanner(string $userAgent): bool
    {
        return $userAgent !== '' && mb_strpos($userAgent, 'StanApp') !== false;
    }
}
