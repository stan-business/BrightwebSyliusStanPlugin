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
use Brightweb\SyliusStanPlugin\Payum\Request\Api\CreateCustomer;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Stan\Model\Address;
use Stan\Model\Customer;
use Stan\Model\CustomerRequestBody;

class CreateCustomerAction implements ActionInterface, ApiAwareInterface
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

        if (!$this->api instanceof StanPayClient) {
            return;
        }

        /** @var array $options */
        $options = $this->api->options;

        if (true === (bool) $options['only_for_stanner']) {
            return;
        }

        /** @var ArrayObject $details
         * @phpstan-ignore-next-line assertSupports called
         */
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $customerBody = new CustomerRequestBody();

        $customerAddress = new Address();
        $customerAddress = $customerAddress
            ->setFirstname(strval($details['customer_firstname']))
            ->setLastname(strval($details['customer_lastname']))
            ->setStreetAddress(strval($details['customer_street_address']))
            // ->setStreetAddressLine2() TODO get line2 from shipping address
            ->setLocality(strval($details['customer_city']))
            ->setZipCode(strval($details['customer_postcode']))
            ->setCountry(strval($details['customer_country_code']))
        ;

        $customerBody = $customerBody
            ->setEmail(strval($details['customer_email']))
            ->setName(strval($details['customer_fullname']))
            ->setAddress($customerAddress)
        ;

        /** @var Customer $createdCustomer */
        $createdCustomer = $this->api->createCustomer($customerBody);

        $details->replace([
            'stan_customer_id' => $createdCustomer->getId(),
        ]);
    }

    public function supports($request): bool
    {
        return $request instanceof CreateCustomer &&
            $request->getModel() instanceof ArrayAccess &&
            $this->api instanceof StanPayClient
        ;
    }
}
