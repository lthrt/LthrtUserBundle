<?php

namespace Lthrt\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Role\Role as BaseRole;

/**
 * @ORM\Table(name="app_role")
 * @ORM\Entity(repositoryClass="\Lthrt\UserBundle\Repository\RoleRepository")
 * @UniqueEntity(fields="role", message="Role already defined")
 */
class Role extends BaseRole implements \Serializable
{
    use \Lthrt\EntityBundle\Entity\ActiveTrait;
    use \Lthrt\EntityBundle\Entity\DescriptionTrait;
    use \Lthrt\EntityBundle\Entity\DoctrineEntityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=255)
     */
    private $role;

    public function __construct($role = 'ROLE')
    {
        parent::__construct($role);
    }

    public function __toString()
    {
        return $this->role;
    }

    /**
     * This function necessary for interface requirements
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * This function necessary for interface requirements
     *
     * @return string
     */
    public function serialize()
    {
        return \serialize(
            [$this->role, $this->id]
        );
    }

    /**
     * This function necessary for interface requirements
     *
     * @param serialized
     */
    public function unserialize($serialized)
    {
        list($this->role, $this->id) = \unserialize(
            $serialized
        );
    }
}
