<?php

namespace AppBundle\Entity;

/**
 * User
 */
class User
{
    private $uid;
    private $name;
    private $lastname;
    private $email;
    private $etuId;
    private $status;
    private $homeDir;

    /**
     * Set uid
     *
     * @param string $uid
     *
     * @return User
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * Get uid
     *
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set etuId
     *
     * @param integer $etuId
     *
     * @return User
     */
    public function setEtuId($etuId)
    {
        $this->etuId = $etuId;

        return $this;
    }

    /**
     * Get etuId
     *
     * @return int
     */
    public function getEtuId()
    {
        return $this->etuId;
    }

    /**
     * Set status
     *
     * @param boolean $status
     *
     * @return User
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set homeDir
     *
     * @param string $homeDir
     *
     * @return User
     */
    public function setHomeDir($homeDir)
    {
        $this->homeDir = $homeDir;

        return $this;
    }

    /**
     * Get homeDir
     *
     * @return string
     */
    public function getHomeDir()
    {
        return $this->homeDir;
    }

    public function toArray()
    {
        return array(
            "uid" => $this->getUid(),
            "name" => $this->getName(),
            "lastname" => $this->getLastname(),
            "email" => $this->getEmail(),
            "etu_id" => $this->getEtuId(),
            "status" => $this->getStatus(),
            "home_dir" => $this->getHomeDir()
        );
    }
}
