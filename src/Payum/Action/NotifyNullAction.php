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
 * @psalm-suppress PropertyNotSetInConstructor
 */
class NotifyNullAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    private GetHttpRequest $httpRequest;

    public function __construct(GetHttpRequest $httpRequest)
    {
        $this->httpRequest = $httpRequest;
    }

    /**
     * @param mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute($this->httpRequest);

        if (!array_key_exists('state', $this->httpRequest->query)) {
            throw new HttpResponse('state is missing in URI query', 400);
        }

        /** @var string $state */
        $state = $this->httpRequest->query['state'];

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
