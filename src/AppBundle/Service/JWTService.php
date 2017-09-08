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

use AppBundle\Entity\User;
use \Firebase\JWT\JWT;

/**
 * Class JWTService
 */
class JWTService
{
    /**
     * @var RSAKeyService
     */
    private $rsakeyService;

    /**
     * @var string
     */
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
     * @param string $uid
     *
     * @return null|string
     */
    public function generate($uid)
    {
        $privateKey = $this->rsakeyService->getPrivateKey();

        if (!$privateKey || !$uid) {
            return null;
        }

        $token = [
            "iss" => $this->host,
            "aud" => $this->host,
            "iat" => time(),
            "nbf" => time(),
            "exp" => time() + 1000,
            "uid" => $uid,
        ];

        try {
            return JWT::encode($token, $privateKey, RSAKeyService::ALG);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verify if Json Web Token is valid
     * @param string $token
     *
     * @return bool
     */
    public function verify($token)
    {
        $publicKey = $this->rsakeyService->getPublicKey();

        try {
            JWT::decode($token, $publicKey, [RSAKeyService::ALG]);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Decode Json Web Token to plain object
     * @param string $token
     *
     * @return null|object
     */
    public function decode($token)
    {
        $publicKey = $this->rsakeyService->getPublicKey();

        try {
            $jwt = JWT::decode($token, $publicKey, [RSAKeyService::ALG]);
        } catch (\Exception $e) {
            return null;
        }

        return $jwt;
    }
}
