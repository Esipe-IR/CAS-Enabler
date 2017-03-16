<?php

namespace AppBundle\Service;

use AppBundle\Entity\Service;
use AppBundle\Entity\User;
use \Firebase\JWT\JWT;

/**
 * Class JWTService
 * @package AppBundle\Service
 */
class JWTService
{
    private $keysDir;
    private $passphrase;
    private $host;

    /**
     * JWTService constructor.
     * @param $kernelDir
     * @param $keysDir
     * @param $passphrase
     * @param $host
     */
    public function __construct($kernelDir, $keysDir, $passphrase, $host)
    {
        $this->keysDir = $kernelDir . $keysDir;
        $this->passphrase = $passphrase;
        $this->host = $host;
    }

    /**
     * @param User $user
     * @param Service $service
     * @return string
     */
    public function generate(User $user, Service $service)
    {
        $token = array(
            "iss" => $this->host,
            "aud" => $service->getUid(),
            "iat" => time(),
            "nbf" => time() + 1,
            "exp" => time() + 1000,
            "usr" => json_encode($user->toArray())
        );
        
        $key = file_get_contents($this->keysDir . $service->getUid() . ".key");
        $privateKey = openssl_pkey_get_private($key, $this->passphrase);

        return JWT::encode($token, $privateKey, 'RS256');
    }

    /**
     * @param $token
     * @return string||null
     */
    public function verify(Service $service, $token)
    {
        $key = file_get_contents($this->keysDir . $service->getUid() . ".key.pub");
        $publicKey = openssl_pkey_get_public($key);

        try {
            $decoded = JWT::decode($token, $publicKey, array('RS256'));
        } catch (\Exception $e) {
            return null;
        }

        return $decoded;
    }
}
