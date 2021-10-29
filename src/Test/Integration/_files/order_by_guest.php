<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

require __DIR__ .
    '/../../../../../../../dev/tests/integration/testsuite/Magento/Sales/_files/guest_quote_with_addresses.php';

/** @var \Magento\Quote\Model\Quote $quote */
/** @var \Magento\Framework\ObjectManagerInterface $objectManager */

$quoteManagement = $objectManager->get(\Magento\Quote\Api\CartManagementInterface::class);
$quoteManagement->placeOrder($quote->getId(), $quote->getPayment());
