<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */
$objectManager   = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$customerFixture = new \TddWizard\Fixtures\Customer\CustomerFixture(
    \TddWizard\Fixtures\Customer\CustomerBuilder::aCustomer()
        ->withAddresses(
            \TddWizard\Fixtures\Customer\AddressBuilder::anAddress()
                ->asDefaultBilling()->asDefaultShipping()
        )
        ->build()
);
$product         = \TddWizard\Fixtures\Catalog\ProductBuilder::aSimpleProduct()
    ->withIsInStock(true)
    ->withStockQty(10)
    ->withPrice(100)
    ->build();
$productFixture = new \TddWizard\Fixtures\Catalog\ProductFixture(
    $product
);
$customerFixture->login();
$checkout = \TddWizard\Fixtures\Checkout\CustomerCheckout::fromCart(
    \TddWizard\Fixtures\Checkout\CartBuilder::forCurrentSession()
        ->withSimpleProduct(
            $productFixture->getSku()
        )
        ->build()
);
$checkout->placeOrder();