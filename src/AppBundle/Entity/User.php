<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/02/28
 * Time: 15:47
 */
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 *
 */
class User implements UserInterface
{
    /**
     * @ORM\Id;
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     *
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=255,unique=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="string",length=40)
     */
    protected $name;

    /**
     * @ORM\Column(type="string",length=50)
     */
    protected $role;

    /**
     * @Assert\Length(max=4096)
     */
    protected $plainPassword;

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $password;


    /**
     * Returns the role granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return ['ROLE_USER'];
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRole()
    {
        return $this->role;
    }


    /**
     * Set the role for a specific User
     */
    public function setRole($role=null)
    {
        $this->role = $role;
    }


    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return ['ROLE_USER'];
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return [$this->getRole()];
    }

    /**
     * Returns the id generated for the User
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the ID for User
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    /**
     * Returns the name generated for the User
     */
    public function getName()
    {
        return $this->name;
    }





    /**
     * Set the name for User
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the email generated for the User
     */
    public function getEmail()
    {
        return $this->email;
    }


    /**
     * Set the email for the User
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Set the password for User
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the password for User
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        return null;
    }

}