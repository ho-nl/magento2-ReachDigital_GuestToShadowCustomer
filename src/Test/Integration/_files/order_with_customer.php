<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */

include __DIR__ . '/order.php';
include __DIR__ . '/../../../../../../../dev/tests/integration/testsuite/Magento/Customer/_files/customer.php';

/** @var $order \Magento\Sales\Model\Order */
$order->setCustomerId(1)->setCustomerIsGuest(false)->save();
