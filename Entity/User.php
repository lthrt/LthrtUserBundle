<?php

namespace Lthrt\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_user")
 * @ORM\Entity(repositoryClass="\Lthrt\UserBundle\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Email already taken")
 * @UniqueEntity(fields="username", message="Username already taken")
 */
class User implements AdvancedUserInterface, \Serializable
{
    use \Lthrt\EntityBundle\Entity\ActiveTrait;

    // Seems redundant, because DoctrineGetSetTrait also uses this trait;
    // but EntityUserProvider does not work without this.
    // use \Lthrt\EntityBundle\Entity\DoctrineEntityTrait;
    use \Lthrt\EntityBundle\Entity\DoctrineGetSetTrait;
    use \Lthrt\EntityBundle\Entity\IdTrait;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $salt;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var \Lthrt\UserBundle\Role
     *
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="app_user__app_role",
     *      joinColumns={@ORM\JoinColumn(name="app_user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="app_role_id", referencedColumnName="id")}
     * )
     */
    private $role;

    /**
     * @var \Lthrt\UserBundle\Group
     *
     * @ORM\ManyToMany(targetEntity="Group")
     * @ORM\JoinTable(name="app_user__app_group",
     *      joinColumns={@ORM\JoinColumn(name="app_user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="app_group_id", referencedColumnName="id")}
     * )
     */
    private $group;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @ORM\OneToMany(targetEntity="LoginData", mappedBy="user")
     */
    private $loginData;

    public function __construct()
    {
        $this->active    = true;
        $this->group     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->loginData = new \Doctrine\Common\Collections\ArrayCollection();
        $this->role      = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->username;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getRoles()
    {
        return $this->role->map(function ($r) {return $r->role;})->toArray();
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function encodePassword(PasswordEncoderInterface $encoder)
    {
        if ($this->plainPassword) {
            $this->salt     = sha1(uniqid(mt_rand(0, 999999) . $this->email));
            $this->password = $encoder->encodePassword($this->plainPassword, $this->salt);
            $this->eraseCredentials();
        }
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->active,
        ]);
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->active
        ) = unserialize($serialized);
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof \Lthrt\UserBundle\Entity\User) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        return true;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->active;
    }
}
