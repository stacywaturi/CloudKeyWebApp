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
 *@ORM\Table(name="`documents`")
 */
class Documents
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
     * @ORM\Column(type="datetime",columnDefinition="TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="string",length=2000)
     */
    protected $file_path;


}