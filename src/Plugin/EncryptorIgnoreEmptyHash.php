<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Framework\Encryption\Encryptor;

class EncryptorIgnoreEmptyHash
{
    /**
     * @param Encryptor $encryptor
     * @param \Closure $proceed
     * @param $password
     * @param $hash
     *
     * @return bool|mixed
     */
    public function aroundIsValidHash(Encryptor $encryptor, \Closure $proceed, $password, $hash)
    {
        if ($hash === null) {
            return false;
        }

        return $proceed($password, $hash);
    }
}
