<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use \Firebase\JWT\JWT;

/**
 * Class JWTService
 * @package AppBundle\Service
 */
class JWTService
{
    private $rsakeyService;
    private $host;

    /**
     * JWTService constructor.
     * @param RSAKeyService $rsakeyService
     * @param string $host
     */
    public function __construct(RSAKeyService $rsakeyService, $host)
    {
        $this->rsakeyService = $rsakeyService;
        $this->host = $host;
    }

    /**
     * Generate a Json Web Token
     * @param User $user
     * @return null|string
     */
    public function generate(User $user)
    {
        $privateKey = $this->rsakeyService->getPrivateKey();

        if (!$privateKey) {
            return null;
        }
        
        $token = array(
            "iss" => $this->host,
            "aud" => $this->host,
            "iat" => time(),
            "nbf" => time(),
            "exp" => time() + 1000,
            "uid" => $user->getUid()
        );

        try {
            return JWT::encode($token, $privateKey, RSAKeyService::ALG);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verify if Json Web Token is valid
     * @param string $token
     * @return bool
     */
    public function verify($token)
    {
        $publicKey = $this->rsakeyService->getPublicKey();

        try {
            JWT::decode($token, $publicKey, array(RSAKeyService::ALG));
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Decode Json Web Token to plain object
     * @param string $token
     * @return null|object
     */
    public function decode($token)
    {
        $publicKey = $this->rsakeyService->getPublicKey();

        try {
            $jwt = JWT::decode($token, $publicKey, array(RSAKeyService::ALG));
        } catch (\Exception $e) {
            return null;
        }

        return $jwt;
    }
}
