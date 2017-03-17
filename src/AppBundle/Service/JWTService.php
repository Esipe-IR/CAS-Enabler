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
     * @param Service $service
     * @param User $user
     * @return string|null
     */
    public function generate(Service $service, User $user)
    {
        $token = array(
            "iss" => $this->host,
            "aud" => $service->getUid(),
            "iat" => time(),
            "nbf" => time() + 1,
            "exp" => time() + 1000,
            "usr" => json_encode($user->toArray())
        );
        
        $privateKey = $this->rsakeyService->getPrivateKey($service);

        if (!$privateKey) {
            return null;
        }

        try {
            return JWT::encode($token, $privateKey, RSAKeyService::ALG);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verify if Json Web Token is valid
     * @param Service $service
     * @param string $token
     * @return bool
     */
    public function verify(Service $service, $token)
    {
        $publicKey = $this->rsakeyService->getPublicKey($service);

        try {
            JWT::decode($token, $publicKey, array(RSAKeyService::ALG));
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Decode Json Web Token to plain object
     * @param Service $service
     * @param string $token
     * @return null|object
     */
    public function decode(Service $service, $token)
    {
        $publicKey = $this->rsakeyService->getPublicKey($service);

        try {
            $jwt = JWT::decode($token, $publicKey, array(RSAKeyService::ALG));
        } catch (\Exception $e) {
            return null;
        }

        return $jwt;
    }
}
