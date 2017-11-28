<?php

namespace Lthrt\UserBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use Lthrt\UserBundle\Entity\User;

class LoginDataRotator
{
    private $em;
    private $dataLength;

    public function __construct(
        ObjectManager $em,
                      $dataLength
    ) {
        $this->em         = $em;
        $this->dataLength = $dataLength;
    }

    public function getCutoffDate(User $user)
    {
        $qb = $this->em->getRepository('LthrtUserBundle:LoginData')->createQueryBuilder('data');
        $qb->addOrderBy('data.updated', 'DESC');
        $qb->andWhere($qb->expr()->eq('data.user', ':user'));
        $qb->setMaxResults($this->dataLength);
        $qb->setParameter('user', $user);
        $results = $qb->getQuery()->getResult();

        return min(array_map(function ($r) {return $r->updated;}, $results));
    }

    public function purgeOlder(User $user)
    {
        $qb = $this->em->getRepository('LthrtUserBundle:LoginData')->createQueryBuilder('data');
        $qb->delete();
        $qb->where($qb->expr()->lt('data.updated', ':cutoff'));
        $qb->setParameter('cutoff', $this->getCutoffDate($user));
        $qb->getQuery()->execute();
    }
}
