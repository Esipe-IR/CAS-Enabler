<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;

class LdapService
{
    private $host;
    private $env;
    private $classMapping;
    private $fakeUser;
    private $ds;

    public function __construct($host, $env, array $classMapping, array $fakeUser)
    {
        $this->host = $host;
        $this->env = $env;
        $this->classMapping = $classMapping;
        $this->fakeUser = $fakeUser;
        $this->ds = null;
    }

    public function connect()
    {
        $this->ds = ldap_connect($this->host);
        ldap_bind($this->ds);
    }

    public function getUser($uid)
    {
        if ($this->env !== "prod") {
            return $this->fakeUser;
        }

        if (!$this->ds) {
            $this->connect();
        }

        $dn = "ou=Users,ou=Etudiant,dc=univ-mlv,dc=fr";
        $filter = "(&(uid=$uid)(UmlvWWW=TRUE))";
        $ldapResult = ldap_get_entries($this->ds, ldap_search($this->ds, $dn, $filter));

        if (!isset($ldapResult["count"]) || $ldapResult["count"] < 1) {
            return null;
        }

        return $ldapResult[0];
    }

    public function homeDirToClass($homeDir)
    {
        $arr = explode("/", $homeDir);
        $class = $arr[2];

        if (isset($this->classMapping[$class])) {
            $class = $this->classMapping[$class];
        }

        return $class;
    }

    public function transformToUser($ldapUser)
    {
        $class = $this->homeDirToClass($ldapUser["homedirectory"][0]);

        $user = new User();
        $user->setName($ldapUser["givenname"][0]);
        $user->setLastname($ldapUser["sn"][0]);
        $user->setUid($ldapUser["uid"][0]);
        $user->setEmail($ldapUser["mail"][0]);
        $user->setEtuId((int) $ldapUser["supannetuid"][0]);
        $user->setClass($class);
        $user->setStatus((bool) $ldapUser["accountstatus"][0]);

        return $user;
    }
}
