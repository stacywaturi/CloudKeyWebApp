<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/05/30
 * Time: 14:52
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 *@ORM\Table(name="`products`")
 */
class Products
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
    protected $duration;

    /**
     * @ORM\Column(type="integer", name="`usage`")
     */
    protected $usage;



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
    public function getUsage()
    {
        return $this->usage;
    }

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @param mixed $usage
     */
    public function setUsage($usage): void
    {
        $this->usage = $usage;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration($duration): void
    {
        $this->duration = $duration;
    }


}