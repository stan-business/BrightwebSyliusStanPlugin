<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
*/

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Payum\Action\Api;

use ArrayAccess;
use Brightweb\SyliusStanPlugin\Client\StanPayClient;
use Brightweb\SyliusStanPlugin\Payum\Request\Api\GetPayment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Stan\Model\Payment;

class GetPaymentAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = StanPayClient::class;
    }

    /**
     * @param mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /**
         * @phpstan-ignore-next-line assertSupports called
         */
        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $details['stan_payment_id']) {
            throw new LogicException('The parameter "stan_payment_id" must be set. Have you run PrepareAction?');
        }

        /**
         * @var Payment $payment
         * @phpstan-ignore-next-line assertSupports called
         */
        $payment = $this->api->getPayment($details['stan_payment_id']);

        $details->replace([
            'stan_payment_status' => $payment->getPaymentStatus(),
        ]);
    }

    public function supports($request): bool
    {
        return $request instanceof GetPayment &&
            $request->getModel() instanceof ArrayAccess &&
            $this->api instanceof StanPayClient
        ;
    }
}
