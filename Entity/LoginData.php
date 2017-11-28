<?php

namespace Lthrt\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="login_data")
 * @ORM\Entity(repositoryClass="\Lthrt\UserBundle\Repository\RoleRepository")
 * @UniqueEntity(fields="name", message="Role already defined")
 */
class LoginData
{
    use \Lthrt\EntityBundle\Entity\DoctrineEntityTrait;
    use \Lthrt\EntityBundle\Entity\LoggingDisabledTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @var \Lthrt\UserBundle\User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="loginData")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * )
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, nullable=true)
     */
    private $username;

    /**
     * @var boolean
     *
     * @ORM\Column(name="success", type="boolean")
     */
    private $success;

    public function __toString()
    {
        return $this->id . "";
    }
}
