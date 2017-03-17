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
    public function generate(Service $service)
    {
        $config = array(
            'digest_alg' => self::ALG,
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        );

        $privKey = openssl_pkey_new($config);
        $privPath = $this->keysDir . $service->getUid();
        $privStatus = openssl_pkey_export_to_file($privKey, $privPath, $this->passphrase);

        if (!$privStatus) {
            return false;
        }

        $pubPath = $privPath . ".pub";
        $pubKey = openssl_pkey_get_details($privKey)["key"];
        $pubStatus = file_put_contents($pubPath, $pubKey);
        
        if ($pubStatus === false) {
            return false;
        }

        return $pubKey;
    }

    /**
     * @param Service $service
     * @return resource|null
     */
    public function getPrivateKey(Service $service)
    {
        try {
            $key = file_get_contents($this->keysDir . $service->getUid());

            return openssl_pkey_get_private($key, $this->passphrase);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param Service $service
     * @return resource|null
     */
    public function getPublicKey(Service $service)
    {
        try {
            $key = file_get_contents($this->keysDir . $service->getUid() . ".pub");

            return openssl_pkey_get_public($key);
        } catch (\Exception $e) {
            return null;
        }
    }
}
