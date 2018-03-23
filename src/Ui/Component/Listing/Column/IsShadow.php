<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class IsShadow extends Column
{

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name        = $this->getData('name');
                $item[$name] = ($item[$name] ? __('Yes') : __('No'));
            }
        }
        return $dataSource;
    }
}
