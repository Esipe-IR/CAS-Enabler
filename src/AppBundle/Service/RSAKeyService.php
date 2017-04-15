<?php

namespace AppBundle\Service;

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
}
