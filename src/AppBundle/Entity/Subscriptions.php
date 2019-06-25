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
 *@ORM\Table(name="`subscriptions`")
 */
class Subscriptions
{

    /**
     * @ORM\Id;
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     *
     */
    protected $id;

    /**
     * @ORM\Column(name="`name`",type="string",length=255)
     */
    protected $name;

    /**
     * @ORM\Column(name="`user_id`",type="string",length=255)
     */
    protected $user_id;

    /**
     * @ORM\Column(name="`status",type="string",length=255)
     */
    protected $status;

    /**
     * @ORM\Column(name="`product_id`",type="string",length=255)
     */
    protected $product_id;

    /**
     * @ORM\Column(name="`usage`",type="text")
     */
    protected $usage;

    /**
     * @ORM\Column(name="`created_at`",type="datetime",columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
     */
    protected $created_at;

    /**
     * @ORM\Column(name="`expiry`",type="datetime",columnDefinition="TIMESTAMP")
     */
    protected $expiry;

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
    public function getUserId()
    {
        return $this->user_id;
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
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getUsage()
    {
        return $this->usage;
    }

    /**
     * @return mixed
     */
    public function getExpiry()
    {
        return $this->expiry;
    }
    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @param mixed $created_at
     */
    public function setCreatedAt($created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @param mixed $product_id
     */
    public function setProductId($product_id): void
    {
        $this->product_id = $product_id;
    }
    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @param mixed $usage
     */
    public function setUsage($usage): void
    {
        $this->usage = $usage;
    }

    /**
     * @param mixed $expiry
     */
    public function setExpiry($expiry): void
    {
        $this->expiry = $expiry;
    }



}