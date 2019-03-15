<?php
declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Framework\Encryption\Encryptor;

class EncryptorIgnoreEmptyHash
{
    public function aroundIsValidHash(Encryptor $encryptor, \Closure $proceed, $password, $hash)
    {
        if ($hash === null) {
            return false;
        }
        return $proceed($password, $hash);
    }
}
