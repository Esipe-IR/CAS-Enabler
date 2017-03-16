<?php

namespace AppBundle\Service;

use AppBundle\Entity\Service;

class RSAKeyService
{
    private $keysDir;
    private $passphrase;

    public function __construct($kernelDir, $keysDir, $passphrase)
    {
        $this->keysDir = $kernelDir . $keysDir;
        $this->passphrase = $passphrase;
    }

    public function generate(Service $service)
    {
        $config = array(
            'digest_alg' => 'RS256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        );

        $privKey = openssl_pkey_new($config);
        $privPath = $this->keysDir . $service->getUid() . ".key";
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
}
