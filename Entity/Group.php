<?php

namespace Lthrt\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="app_group")
 * @ORM\Entity(repositoryClass="\Lthrt\UserBundle\Repository\GroupRepository")
 * @UniqueEntity(fields="name", message="Group already defined")
 */
class Group
{
    use \Lthrt\EntityBundle\Entity\ActiveTrait;
    use \Lthrt\EntityBundle\Entity\DescriptionTrait;
    use \Lthrt\EntityBundle\Entity\DoctrineEntityTrait;
    use \Lthrt\EntityBundle\Entity\NameTrait;

    /**
     * @var \Lthrt\UserBundle\Entity\Group
     *
     * @ORM\ManyToMany(targetEntity="Group", mappedBy="superGroup")
     */
    private $subGroup;

    /**
     * @var \Lthrt\UserBundle\Entity\Group
     *
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="subGroup")
     * @ORM\JoinTable(name="supergroup__subgroup",
     *      joinColumns={@ORM\JoinColumn(name="subgroup_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="supergroup_id", referencedColumnName="id")}
     * )
     */
    private $superGroup;

    public function addSubGroup(Group $group)
    {
        // Doctrine Entity Trait automatically creates addSuperGroup
        // via magic methods
        //
        // Super is the owning side, so doctrine only persists form there
        //
        // Now either method call results in persistance
        $group->addSuperGroup($this);
    }

    // Not needed because doctrine will persist from owning side automatically
    // public function inverseSubGroup()
    // {
    //     return "superGroup";
    // }

    public function inverseSuperGroup()
    {
        return "subGroup";
    }

    public function __construct()
    {
        $this->active     = true;
        $this->subGroup   = new \Doctrine\Common\Collections\ArrayCollection();
        $this->superGroup = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getAllGroups($type = 'sub')
    {
        $group    = $type . 'Group';
        $response = clone $this->$group;
        $working  = clone $this->$group;

        while (!$working->isEmpty()) {
            foreach ($working as $g) {
                $working->removeElement($g);
                if (!$g->$group->isEmpty()) {
                    foreach ($g->$group as $sg) {
                        $response->add($sg);
                        $working->add($sg);
                    }
                }

            }
        }

        return $response;
    }

    public function getAllSubGroups()
    {
        return $this->getAllGroups('sub');
    }

    public function getAllSuperGroups()
    {
        return $this->getAllGroups('super');
    }
}
