<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * Class UserService
 * @package AppBundle\Service
 */
class UserService
{
    private $em;
    private $ldapService;
    private $classMapping;

    /**
     * UserService constructor.
     * @param EntityManager $em
     * @param LdapService $ldapService
     * @param array $classMapping
     */
    public function __construct(EntityManager $em, LdapService $ldapService, array $classMapping)
    {
        $this->em = $em;
        $this->ldapService = $ldapService;
        $this->classMapping = $classMapping;
    }

    /**
     * @param $homeDir
     * @return string
     */
    public function homeDirToClass($homeDir)
    {
        $arr = explode("/", $homeDir);
        $class = $arr[2];

        if (isset($this->classMapping[$class])) {
            $class = $this->classMapping[$class];
        }

        return $class;
    }

    /**
     * @param $uid
     * @return User
     */
    public function getUser($uid)
    {
        $user = new User();
        $ldapUser = $this->ldapService->getUser($uid);

        if ($this->ldapService->isValid($ldapUser)) {
            $user->setUid($uid);
            $user->setName($ldapUser["givenname"][0]);
            $user->setLastname($ldapUser["sn"][0]);
            $user->setUid($ldapUser["uid"][0]);
            $user->setEmail($ldapUser["mail"][0]);
            $user->setEtuId((int)$ldapUser["supannetuid"][0]);
            $user->setClass($this->homeDirToClass($ldapUser["homedirectory"][0]));
            $user->setStatus((bool)$ldapUser["accountstatus"][0]);
            $user->setHomeDir($ldapUser["homedirectory"][0]);
        }

        return $user;
    }
}
