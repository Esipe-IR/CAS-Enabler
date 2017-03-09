<?php

namespace AppBundle\Service;

class LdapService
{
    private $host;
    private $env;
    private $fakeUser;
    private $ds;

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
    
    public function isValid(array $ldapUser)
    {
        if (!isset($ldapUser["givenname"]) || !isset($ldapUser["givenname"][0])) {
            return false;
        }
        
        if (!isset($ldapUser["sn"]) || !isset($ldapUser["sn"][0])) {
            return false;
        }
        
        if (!isset($ldapUser["uid"]) || !isset($ldapUser["uid"][0])) {
            return false;
        }

        if (!isset($ldapUser["mail"]) || !isset($ldapUser["mail"][0])) {
            return false;
        }

        if (!isset($ldapUser["supannetuid"]) || !isset($ldapUser["supannetuid"][0])) {
            return false;
        }

        if (!isset($ldapUser["homedirectory"]) || !isset($ldapUser["homedirectory"][0])) {
            return false;
        }

        if (!isset($ldapUser["accountstatus"]) || !isset($ldapUser["accountstatus"][0])) {
            return false;
        }
        
        return true;
    }
}
