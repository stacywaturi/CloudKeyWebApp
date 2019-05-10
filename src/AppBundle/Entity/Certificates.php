<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 *@ORM\Table(name="`certificates`")
 */
class Certificates
{
    /**
     * @ORM\Id;
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     *
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=255)
     */
    protected $name;

    /**
     * @ORM\Column(type="string",length=255)
     */
    protected $user_id;

    /**
     * @ORM\Column(type="string",length=255)
     */
    protected $key_id;

    /**
     * @ORM\Column(type="string",length=5000)
     */
    protected $csr;

    /**
     * @ORM\Column(type="string",length=5000, nullable=true)
     */
    protected $certificate;

    /**
     * @ORM\Column(type="string",length=255, nullable=true)
     */
    protected $previous_id;

    /**
     * @ORM\Column(type="datetime",columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="string",length=255, nullable=true)
     */
    protected $expiry;

    /**
     * @ORM\Column(type="string",length=255, nullable=true)
     */
    protected $common_name;

    /**
     * @ORM\Column(type="string",length=255, nullable=true)
     */
    protected $serial_number;

    /**
     * @ORM\Column(type="string",length=255, nullable=true)
     */
    protected $issuer;

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @param mixed $created_at
     */
    public function setCreatedAt($created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @param mixed $key_id
     */
    public function setKeyId($key_id): void
    {
        $this->key_id = $key_id;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @param mixed $certificate
     */
    public function setCertificate($certificate): void
    {
        $this->certificate = $certificate;
    }

    /**
     * @param mixed $common_name
     */
    public function setCommonName($common_name): void
    {
        $this->common_name = $common_name;
    }

    /**
     * @param mixed $csr
     */
    public function setCsr($csr): void
    {
        $this->csr = $csr;
    }

    /**
     * @param mixed $expiry
     */
    public function setExpiry($expiry): void
    {
        $this->expiry = $expiry;
    }

    /**
     * @param mixed $issuer
     */
    public function setIssuer($issuer): void
    {
        $this->issuer = $issuer;
    }

    /**
     * @param mixed $previous_id
     */
    public function setPreviousId($previous_id): void
    {
        $this->previous_id = $previous_id;
    }

    /**
     * @param mixed $serial_number
     */
    public function setSerialNumber($serial_number): void
    {
        $this->serial_number = $serial_number;
    }

    /**
     * @return mixed
     */
    public function getKeyId()
    {
        return $this->key_id;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @return mixed
     */
    public function getCommonName()
    {
        return $this->common_name;
    }

    /**
     * @return mixed
     */
    public function getCsr()
    {
        return $this->csr;
    }

    /**
     * @return mixed
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * @return mixed
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * @return mixed
     */
    public function getPreviousId()
    {
        return $this->previous_id;
    }

    /**
     * @return mixed
     */
    public function getSerialNumber()
    {
        return $this->serial_number;
    }






}
