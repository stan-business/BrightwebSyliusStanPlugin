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
use Stan\Model\CustomerRequestBody;
use Stan\Model\Customer;

class CreateCustomerAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = StanPayClient::class;
    }

    /**
     * @param CreateCustomer $request
     */
    public function execute($request): void
    {
        if (true === (bool) $this->api->options['only_for_stanner']) {
            return;
        }

        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $customerBody = new CustomerRequestBody();

        $customerAddress = new Address();
        $customerAddress = $customerAddress
            ->setFirstname($details['customer_firstname'])
            ->setLastname($details['customer_lastname'])
            ->setStreetAddress($details['customer_street_address'])
            // ->setStreetAddressLine2() TODO get line2 from shipping address
            ->setLocality($details['customer_city'])
            ->setZipCode($details['customer_postcode'])
            ->setCountry($details['customer_country_code'])
        ;

        $customerBody = $customerBody
            ->setEmail($details['customer_email'])
            ->setName($details['customer_fullname'])
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
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
