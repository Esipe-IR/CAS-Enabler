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
use Doctrine\ORM\EntityManager;

/**
 * Class UserService
 */
class UserService
{
    private $em;
    private $ldapService;

    /**
     * UserService constructor.
     * @param EntityManager $em
     * @param LdapService $ldapService
     */
    public function __construct(EntityManager $em, LdapService $ldapService)
    {
        $this->em = $em;
        $this->ldapService = $ldapService;
    }

    /**
     * @param $uid
     *
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
            $user->setEtuId((int) $ldapUser["supannetuid"][0]);
            $user->setStatus((bool) $ldapUser["accountstatus"][0]);
            $user->setHomeDir($ldapUser["homedirectory"][0]);
        }

        return $user;
    }
}
