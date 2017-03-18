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
     * @param User $user
     * @return null|string
     */
    public function generate(User $user)
    {
        $privateKey = $this->rsakeyService->getPrivateKey();

        if (!$privateKey) {
            return null;
        }

        $success = openssl_private_encrypt($user->getUid(), $uid, $privateKey);

        if (!$success) {
            return null;
        }
        
        $token = array(
            "iss" => $this->host,
            "aud" => $this->host,
            "iat" => time(),
            "nbf" => time(),
            "exp" => time() + 1000,
            "uid" => base64_encode($uid)
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

    /**
     * Decode uid contain in Json Web Token
     * @param $uid
     * @return null|string
     */
    public function decodeUid($uid)
    {
        $publicKey = $this->rsakeyService->getPublicKey();
        $success = openssl_public_decrypt(base64_decode($uid), $usr, $publicKey);

        if (!$success) {
            return null;
        }

        return $usr;
    }

    /**
     * @param Service $service
     * @param User $user
     * @return null|string
     */
    public function encodeUser(Service $service, User $user)
    {
        $publicKey = $this->rsakeyService->getServicePublicKey($service);

        if (!$publicKey) {
            return null;
        }

        $json = json_encode($user->toArray());
        $success = openssl_public_encrypt($json, $usr, $publicKey);

        if (!$success) {
            return null;
        }

        return base64_encode($usr);
    }
}
