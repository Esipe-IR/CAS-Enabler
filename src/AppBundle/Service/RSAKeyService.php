<?php

namespace AppBundle\Service;

use AppBundle\Entity\Service;

class RSAKeyService
{
    const ALG = 'RS256';

    private $keysDir;
    private $passphrase;

    /**
     * RSAKeyService constructor.
     * @param string $kernelDir
     * @param string $keysDir
     * @param string $passphrase
     */
    public function __construct($kernelDir, $keysDir, $passphrase)
    {
        $this->keysDir = $kernelDir . $keysDir;
        $this->passphrase = $passphrase;
    }

    /**
     * @param Service $service
     * @return bool
     */
    public function isValid(Service $service)
    {
        if (file_exists($this->keysDir . $service->getUid() . ".pub")) {
            return true;
        }

        return false;
    }
    
    /**
     * @param Service $service
     * @return bool
     */
    public function generate(Service $service, $passphrase)
    {
        $config = array(
            'digest_alg' => self::ALG,
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        );

        $pKey = openssl_pkey_new($config);

        if(!openssl_pkey_export($pKey, $privKey, $passphrase)) {
            return false;
        }

        $pubPath = $this->keysDir . $service->getUid() . ".pub";
        $pubKey = openssl_pkey_get_details($pKey)["key"];

        if (!$pubKey) {
            return false;
        }

        $pubStatus = file_put_contents($pubPath, $pubKey);
        
        if ($pubStatus === false) {
            return false;
        }

        return $privKey;
    }

    /**
     * @return resource|null
     */
    public function getPrivateKey()
    {
        try {
            $key = file_get_contents($this->keysDir . "master");

            return openssl_pkey_get_private($key, $this->passphrase);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return resource|null
     */
    public function getPublicKey()
    {
        try {
            $key = file_get_contents($this->keysDir . "master.pub");

            return openssl_pkey_get_public($key);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param Service $service
     * @return null|resource
     */
    public function getServicePublicKey(Service $service)
    {
        try {
            $key = file_get_contents($this->keysDir . $service->getUid() . ".pub");

            return openssl_pkey_get_public($key);
        } catch (\Exception $e) {
            return null;
        }
    }
}
