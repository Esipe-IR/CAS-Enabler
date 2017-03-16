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
    private $sslPrivateKeyPath;
    private $sslPrivateKeyPassPhrase;
    private $sslPublicKeyPath;
    private $kernelRootDir;
    private $host;

    /**
     * JWTService constructor.
     * @param $privateKeyPath
     * @param $privateKeyPassPhrase
     * @param $publicKeyPath
     */
    public function __construct(
        $privateKeyPath,
        $privateKeyPassPhrase,
        $publicKeyPath,
        $kernelRootDir,
        $host
    ) {
        $this->sslPrivateKeyPath = $privateKeyPath;
        $this->sslPrivateKeyPassPhrase = $privateKeyPassPhrase;
        $this->sslPublicKeyPath = $publicKeyPath;
        $this->kernelRootDir = $kernelRootDir;
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
        
        $path = $this->kernelRootDir . $this->sslPrivateKeyPath;
        $key = file_get_contents($path);
        $privateKey = openssl_pkey_get_private($key, $this->sslPrivateKeyPassPhrase);

        return JWT::encode($token, $privateKey, 'RS256');
    }

    /**
     * @param $token
     * @return string||null
     */
    public function verify($token)
    {
        $path = $this->kernelRootDir . $this->sslPublicKeyPath;
        $key = file_get_contents($path);
        $publicKey = openssl_pkey_get_public($key);

        try {
            $decoded = JWT::decode($token, $publicKey, array('RS256'));
        } catch (\Exception $e) {
            return null;
        }

        return $decoded;
    }
}
