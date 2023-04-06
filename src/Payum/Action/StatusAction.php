<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
*/

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Payum\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

use Stan\Model\Payment;

class StatusAction implements ActionInterface
{
    /**
     * @param mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /**
         * @phpstan-ignore-next-line assertSupports called
         * @psalm-suppress MixedMethodCall
         */
        $details = ArrayObject::ensureArrayObject($request->getModel());

        /**
         * @psalm-suppress MixedMethodCall
         */
        if (!isset($details['stan_payment_id'])) {
            /**
             * @phpstan-ignore-next-line assertSupports called
             * @psalm-suppress MixedMethodCall
             */
            $request->markNew();

            return;
        }

        if (!isset($details['stan_payment_status'])) {
            /**
             * @phpstan-ignore-next-line assertSupports called
             * @psalm-suppress MixedMethodCall
             */
            $request->markNew();

            return;
        }

        switch ($details['stan_payment_status']) {
            case Payment::PAYMENT_STATUS_FAILURE:
            case Payment::PAYMENT_STATUS_EXPIRED:
                /**
                 * @phpstan-ignore-next-line assertSupports called
                 * @psalm-suppress MixedMethodCall
                 */
                $request->markFailed();

                break;
            case Payment::PAYMENT_STATUS_PREPARED:
                /**
                 * @phpstan-ignore-next-line assertSupports called
                 * @psalm-suppress MixedMethodCall
                 */
                $request->markNew();
                break;
            case Payment::PAYMENT_STATUS_CANCELLED:
                /**
                 * @phpstan-ignore-next-line assertSupports called
                 * @psalm-suppress MixedMethodCall
                 */
                $request->markCanceled();

                break;
            case Payment::PAYMENT_STATUS_PENDING:
            case Payment::PAYMENT_STATUS_HOLDING:
                /**
                 * @phpstan-ignore-next-line assertSupports called
                 * @psalm-suppress MixedMethodCall
                 */
                $request->markPending();

                break;
            case Payment::PAYMENT_STATUS_SUCCESS:
                /**
                 * @phpstan-ignore-next-line assertSupports called
                 * @psalm-suppress MixedMethodCall
                 */
                $request->markCaptured();

                break;
            default:
                /**
                 * @phpstan-ignore-next-line assertSupports called
                 * @psalm-suppress MixedMethodCall
                 */
                $request->markUnknown();

                break;
        }
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
