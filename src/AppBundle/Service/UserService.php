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
     * @param $username
     * @return User|null|object
     */
    public function checkIfExist($username)
    {
        $user = $this->em->getRepository("AppBundle:User")->findOneBy(array("uid" => $username));

        if (!$user) {
            $user = new User();
            $user->setUid($username);
            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
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
     * @param User $user
     * @return User
     */
    public function getLdapUser(User $user)
    {
        $ldapUser = $this->ldapService->getUser($user->getUid());

        if ($this->ldapService->isValid($ldapUser)) {
            $user->setName($ldapUser["givenname"][0]);
            $user->setLastname($ldapUser["sn"][0]);
            $user->setUid($ldapUser["uid"][0]);
            $user->setEmail($ldapUser["mail"][0]);
            $user->setEtuId((int)$ldapUser["supannetuid"][0]);
            $user->setClass($this->homeDirToClass($ldapUser["homedirectory"][0]));
            $user->setStatus((bool)$ldapUser["accountstatus"][0]);
        }

        return $user;
    }

    /**
     * @param $uid
     * @return User
     */
    public function getUserByUid($uid)
    {
        $user = $this->checkIfExist($uid);
        
        return $this->getLdapUser($user);
    }
}
