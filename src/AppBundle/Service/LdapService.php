<?php

namespace AppBundle\Service;

/**
 * Class LdapService
 * @package AppBundle\Service
 */
class LdapService
{
    private $host;
    private $env;
    private $fakeUser;
    private $ds;

    /**
     * LdapService constructor.
     * @param $host
     * @param $env
     * @param array $fakeUser
     */
    public function __construct($host, $env, array $fakeUser)
    {
        $this->host = $host;
        $this->env = $env;
        $this->fakeUser = $fakeUser;
        $this->ds = null;
    }
    
    public function connect()
    {
        $this->ds = ldap_connect($this->host);
        ldap_bind($this->ds);
    }

    /**
     * @param $uid
     * @return array|null
     */
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

        foreach ($ldapResult[0] as $key=>$value) {
            $ldapResult[0][$key] = $value[0];
        }

        return $ldapResult[0];
    }

    /**
     * @param array $ldapUser
     * @return bool
     */
    public function isValid(array $ldapUser)
    {
        if (!isset($ldapUser["givenname"])) {
            return false;
        }
        
        if (!isset($ldapUser["sn"])) {
            return false;
        }
        
        if (!isset($ldapUser["uid"])) {
            return false;
        }

        if (!isset($ldapUser["mail"])) {
            return false;
        }

        if (!isset($ldapUser["supannetuid"])) {
            return false;
        }

        if (!isset($ldapUser["homedirectory"])) {
            return false;
        }

        if (!isset($ldapUser["accountstatus"])) {
            return false;
        }
        
        return true;
    }
}
