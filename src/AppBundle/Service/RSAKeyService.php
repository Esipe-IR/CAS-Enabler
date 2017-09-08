<?php
/*
 * This file is part of UPEM API project.
 *
 * Based on https://github.com/Esipe-IR/UPEM-API
 *
 * (c) 2016-2017 Vincent Rasquier <vincent.rsbs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace AppBundle\Service;

/**
 * Class RSAKeyService
 */
class RSAKeyService
{
    const ALG = 'RS256';

    /**
     * @var string
     */
    private $keysDir;

    /**
     * @var string
     */
    private $passphrase;

    /**
     * RSAKeyService constructor.
     * @param string $kernelDir
     * @param string $keysDir
     * @param string $passphrase
     */
    public function __construct($kernelDir, $keysDir, $passphrase)
    {
        $this->keysDir = $kernelDir.$keysDir;
        $this->passphrase = $passphrase;
    }

    /**
     * @return resource|null
     */
    public function getPrivateKey()
    {
        try {
            $key = file_get_contents($this->keysDir."master");

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
            $key = file_get_contents($this->keysDir."master.pub");

            return openssl_pkey_get_public($key);
        } catch (\Exception $e) {
            return null;
        }
    }
}
