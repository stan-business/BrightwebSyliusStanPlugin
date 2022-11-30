<?php

/*
 * This file was created by developers working at Brightweb, editor of Stan
 * Visit our website https://stan-business.fr
 * For more information, contact jonathan@brightweb.cloud
*/

declare(strict_types=1);

namespace Brightweb\SyliusStanPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;

/**
 * Handles /payment/notify/unsafe/stan_pay?state={token_hash}
 */
class NotifyNullAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param Notify $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        // TODO handle the case when state is missing
        /** @var string $state */
        $state = $httpRequest->query['state'];
        if (empty($state)) {
            throw new HttpResponse('state is missing in URI query', 400);
        }

        $this->gateway->execute($token = new GetToken($state));
        $this->gateway->execute(new Notify($token->getToken()));
    }

    public function supports($request): bool
    {
        return $request instanceof Notify &&
            null === $request->getModel()
        ;
    }
}
