<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Test\Integration;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CustomerHasShadowFlagEavAttributeTest extends TestCase
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test if customer has EAV attribute is_shadow
     */
    public function testCustomerHasShadowEavAttributeFlag()
    {
        $attributeRepository = $this->objectManager->create(AttributeRepositoryInterface::class);
        $attributeRepository->get(1, 'is_shadow');
    }

}