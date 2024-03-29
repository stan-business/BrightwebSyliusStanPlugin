<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
*/

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Resolver;

use Payum\Core\Bridge\Spl\ArrayObject;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;

final class DisplayStanPaymentMethodResolver implements PaymentMethodsResolverInterface
{
    private PaymentMethodsResolverInterface $decoratedPaymentMethodsResolver;

    public function __construct(PaymentMethodsResolverInterface $decoratedPaymentMethodsResolver)
    {
        $this->decoratedPaymentMethodsResolver = $decoratedPaymentMethodsResolver;
    }

    public function getSupportedMethods(PaymentInterface $subject): array
    {
        $supportedMethods = $this->decoratedPaymentMethodsResolver->getSupportedMethods($subject);

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            /** @var string $userAgent */
            $userAgent = $_SERVER['HTTP_USER_AGENT'];

            $isStanner = $this->checkIfStanner($userAgent);

            foreach ($supportedMethods as $index => $method) {
                if ($method instanceof PaymentMethodInterface) {
                    $config = $method->getGatewayConfig();
                    if ($config !== null) {
                        $gatewayConfig = $config->getConfig();
                        if (isset($gatewayConfig['only_for_stanner'])) {;
                            if (true === (bool) $gatewayConfig['only_for_stanner'] && !$isStanner) {
                                unset($supportedMethods[$index]);

                                break;
                            }
                        }
                    }
                }
            }

            if ($isStanner) {
                $stanPayCode = 'stan_pay';
                /**
                 * @param PaymentMethodInterface[] $supportedMethods
                 * @phpstan-ignore-next-line it returns int (-1, 1 or 0)
                 * @psalm-suppress ArgumentTypeCoercion
                 */
                usort($supportedMethods, function (PaymentMethodInterface $a, PaymentMethodInterface $b) use ($stanPayCode): int {
                    if ($a->getCode() === $stanPayCode) {
                        return -1;
                    }

                    if ($b->getCode() === $stanPayCode) {
                        return 1;
                    }

                    return 0;
                });
            }
        }

        return $supportedMethods;
    }

    public function supports(PaymentInterface $subject): bool
    {
        return $this->decoratedPaymentMethodsResolver->supports($subject);
    }

    private function checkIfStanner(string $userAgent): bool
    {
        return $userAgent !== '' && mb_strpos($userAgent, 'StanApp') !== false;
    }
}
