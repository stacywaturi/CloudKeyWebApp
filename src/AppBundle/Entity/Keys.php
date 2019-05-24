<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/26
 * Time: 16:06
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * @ORM\Entity
 *@ORM\Table(name="`keys`")
 */
class Keys
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
     * @ORM\Column(name = "`use`",type="string",length=255)
     */
    protected $use;

    /**
     * @ORM\Column(type="string", length=20000)
     */
    protected $public_key_n;

    /**
     * @ORM\Column(type="text")
     */
    protected $public_key_e;

    /**
     * @ORM\Column(type="text")
     */
    protected $key_operations;

    /**
     * @ORM\Column(type="text")
     */
    protected $key_type;
    /**
     * @ORM\Column(type="text")
     */
    protected $key_size;


    /**
     * @ORM\Column(type="datetime",columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
     */
    protected $created_at;


    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param mixed $key_id
     */
    public function setKeyId($key_id): void
    {
        $this->key_id = $key_id;
    }

    /**
     * @param mixed $public_key
     */
    public function setPublicKey($public_key): void
    {
        $this->public_key = $public_key;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @param mixed $use
     */
    public function setUse($use): void
    {
        $this->use = $use;
    }

    /**
     * @param mixed $vault_id
     */
    public function setVaultId($vault_id): void
    {
        $this->vault_id = $vault_id;
    }

    /**
     * @param mixed $created_at
     */
    public function setCreatedAt($created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @param mixed $key_operations
     */
    public function setKeyOperations($key_operations): void
    {
        $this->key_operations = $key_operations;
    }

    /**
     * @param mixed $key_size
     */
    public function setKeySize($key_size): void
    {
        $this->key_size = $key_size;
    }

    /**
     * @param mixed $key_type
     */
    public function setKeyType($key_type): void
    {
        $this->key_type = $key_type;
    }

    /**
     * @param mixed $public_key_e
     */
    public function setPublicKeyE($public_key_e): void
    {
        $this->public_key_e = $public_key_e;
    }

    /**
     * @param mixed $public_key_n
     */
    public function setPublicKeyN($public_key_n): void
    {
        $this->public_key_n = $public_key_n;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getPublicKey()
    {
        return $this->public_key;
    }

    /**
     * @return mixed
     */
    public function getUse()
    {
        return $this->use;
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
    public function getVaultId()
    {
        return $this->vault_id;
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
    public function getKeyId()
    {
        return $this->key_id;
    }

    /**
     * @return mixed
     */
    public function getKeyOperations()
    {
        return $this->key_operations;
    }

    /**
     * @return mixed
     */
    public function getKeySize()
    {
        return $this->key_size;
    }

    /**
     * @return mixed
     */
    public function getKeyType()
    {
        return $this->key_type;
    }

    /**
     * @return mixed
     */
    public function getPublicKeyE()
    {
        return $this->public_key_e;
    }

    /**
     * @return mixed
     */
    public function getPublicKeyN()
    {
        return $this->public_key_n;
    }

}