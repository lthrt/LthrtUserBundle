<?php

namespace Lthrt\UserBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="app_user")
 * @ORM\Entity(repositoryClass="\Lthrt\UserBundle\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Email already taken")
 * @UniqueEntity(fields="username", message="Username already taken")
 */
class TestUser implements AdvancedUserInterface
{
    public function __toString()
    {
        return 'testuser';
    }

    public function getUsername()
    {
        return 'testusername';
    }

    public function getSalt()
    {
        return 'testusersalt';
    }

    public function getRoles()
    {
        return [];
    }

    public function getPassword()
    {
        return 'testuserpassword';
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
        return null;
    }

    public function isEqualTo(UserInterface $user)
    {
        // trying to impersonate someone falsely
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
        return true;
    }
}
