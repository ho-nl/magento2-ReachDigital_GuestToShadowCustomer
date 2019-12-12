<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Ui\Component\Listing\Column\IsShadow;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __('Yes'),
            ],
            [
                'value' => 0,
                'label' => __('No'),
            ],
        ];
    }
}
