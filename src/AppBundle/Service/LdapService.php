<?php

namespace AppBundle\Service;

/**
 * Class LdapService
 * @package AppBundle\Service
 */
class LdapService
{
    private $host;
    private $fakeUser;    
    private $env;
    private $ds;

    /**
     * LdapService constructor.
     * @param array $ldap
     * @param $env
     */
    public function __construct(array $ldap, $env)
    {
        $this->host = $ldap["host"];
        $this->fakeUser = $ldap["fake_user"];        
        $this->env = $env;
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
