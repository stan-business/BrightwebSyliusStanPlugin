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
use Brightweb\SyliusStanPlugin\Payum\Request\Api\PreparePayment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;
use Stan\Model\PaymentRequestBody;

class PreparePaymentAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = StanPayClient::class;
    }

    /**
     * @param PreparePayment $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if ($details['stan_payment_id']) {
            throw new LogicException(sprintf('The transaction has already been created for this payment. stan_payment_id: %s', $details['stan_payment_id']));
        }

        $details->validateNotEmpty(['int_amount', 'currency_code', 'return_url', 'order_id']);

        $paymentBody = new PaymentRequestBody();
        $paymentBody
            ->setOrderId($details['order_id'])
            ->setAmount($details['int_amount'])
            ->setReturnUrl($details['return_url'])
        ;

        if (isset($details['token_hash'])) {
            $paymentBody->setState($details['token_hash']);
        }
        if (isset($details['stan_customer_id'])) {
            $paymentBody->setCustomerId($details['stan_customer_id']);
        }

        $preparedPayment = $this->api->preparePayment($paymentBody);

        $details->replace([
            'stan_payment_id' => $preparedPayment->getPaymentId(),
            'payment_url' => $preparedPayment->getRedirectUrl(),
        ]);

        throw new HttpRedirect($details['payment_url']);
    }

    public function supports($request): bool
    {
        return $request instanceof PreparePayment &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
