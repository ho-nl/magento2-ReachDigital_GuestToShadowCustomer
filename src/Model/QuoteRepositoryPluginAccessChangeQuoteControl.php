<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

class QuoteRepositoryPluginAccessChangeQuoteControl extends \Magento\Quote\Model\QuoteRepository\Plugin\AccessChangeQuoteControl
{
    /**
     * Checks if change quote's customer id is allowed for current user.
     *
     * @inheritdoc
     */
    public function beforeSave(CartRepositoryInterface $subject, CartInterface $quote)
    {
    }
}