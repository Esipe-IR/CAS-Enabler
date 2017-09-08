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

/**
 * Class LdapService
 */
class LdapService
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var array
     */
    private $fakeUser;

    /**
     * @var string
     */
    private $env;

    /**
     * @var resource
     */
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

    /**
     * Connect
     */
    public function connect()
    {
        $this->ds = ldap_connect($this->host);
        ldap_bind($this->ds);
    }

    /**
     * @param string $uid
     *
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
     *
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
