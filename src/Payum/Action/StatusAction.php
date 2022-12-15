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
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($details['stan_payment_id'])) {
            $request->markNew();

            return;
        }

        if (!isset($details['stan_payment_status'])) {
            $request->markNew();

            return;
        }

        switch ($details['stan_payment_status']) {
            case Payment::PAYMENT_STATUS_FAILURE:
            case Payment::PAYMENT_STATUS_EXPIRED:
                $request->markFailed();

                break;
            case Payment::PAYMENT_STATUS_CANCELLED:
                $request->markCanceled();

                break;
            case Payment::PAYMENT_STATUS_PENDING:
            case Payment::PAYMENT_STATUS_HOLDING:
            case Payment::PAYMENT_STATUS_PREPARED:
                $request->markPending();

                break;
            case Payment::PAYMENT_STATUS_SUCCESS:
                $request->markCaptured();

                break;
            default:
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
