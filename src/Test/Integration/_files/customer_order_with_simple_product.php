<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/customer_quote_ready_for_order.php';

$objectManager = Bootstrap::getObjectManager();
/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
/** @var CartManagementInterface $quoteManagement */
$quoteManagement = $objectManager->get(CartManagementInterface::class);

$quote = $quoteRepository->getActiveForCustomer(1);
$quoteManagement->placeOrder($quote->getId());
