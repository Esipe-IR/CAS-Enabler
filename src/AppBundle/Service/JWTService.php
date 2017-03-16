<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Namshi\JOSE\SimpleJWS;

/**
 * Class JWTService
 * @package AppBundle\Service
 */
class JWTService
{
    private $sslPrivateKeyPath;
    private $sslPrivateKeyPassPhrase;
    private $sslPublicKeyPath;

    /**
     * JWTService constructor.
     * @param $privateKeyPath
     * @param $privateKeyPassPhrase
     * @param $publicKeyPath
     */
    public function __construct($privateKeyPath, $privateKeyPassPhrase, $publicKeyPath)
    {
        $this->sslPrivateKeyPath = $privateKeyPath;
        $this->sslPrivateKeyPassPhrase = $privateKeyPassPhrase;
        $this->sslPublicKeyPath = $publicKeyPath;
    }

    /**
     * @param User $user
     * @return string
     */
    public function generate(User $user)
    {
        $date = new \DateTime('tomorrow');
        $payload = array(
            "usr" => json_encode($user->toArray()),
            "exp" => $date->format('U'),
        );

        $jws  = new SimpleJWS(array(
            'alg' => 'RS256'
        ));
        $jws->setPayload($payload);
        $privateKey = openssl_pkey_get_private($this->sslPrivateKeyPath, $this->sslPrivateKeyPassPhrase);
        $jws->sign($privateKey);

        return $jws->getTokenString();
    }

    /**
     * @param $token
     * @return string||null
     */
    public function verify($token)
    {
        $jws = SimpleJWS::load($token);
        $public_key = openssl_pkey_get_public($this->sslPublicKeyPath);

        if ($jws->verify($public_key, 'RS256')) {
            $payload = $jws->getPayload();
            
            return $payload["usr"];
        }
        
        return null;
    }
}
