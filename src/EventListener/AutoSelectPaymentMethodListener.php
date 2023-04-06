<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
*/

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\EventListener;

use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;

class AutoSelectPaymentMethodListener
{

    private PaymentMethodRepositoryInterface $paymentMethodRepository;

    public function __construct(PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    // Selects Stan Pay by default if user is browsing the website using Stan
    public function onInitializePayment(ResourceControllerEvent $event): void
    {
        /** @var string $userAgent */
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if ($this->checkIfStanner($userAgent)) {
            $order = $event->getSubject();

            if (!$order instanceof OrderInterface) {
                return;
            }

            $stanPayCode = 'stan_pay';

            $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => $stanPayCode]);

            if (!$paymentMethod instanceof PaymentMethodInterface) {
                return;
            }

            $lastPayment = $order->getLastPayment();
            if ($lastPayment !== null) {
                $lastPayment->setMethod($paymentMethod);
            }
        }
    }

    private function checkIfStanner(string $userAgent): bool
    {
        return $userAgent !== '' && mb_strpos($userAgent, 'StanApp') !== false;
    }
}
