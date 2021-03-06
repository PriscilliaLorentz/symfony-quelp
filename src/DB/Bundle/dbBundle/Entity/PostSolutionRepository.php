<?php

namespace DB\Bundle\dbBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PostSolutionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PostSolutionRepository extends EntityRepository
{
    public function FindComment()
    {
        $qb = $this->createQueryBuilder('p');
        $qb ->select('p.subject')
            ->orderBy('p.editDate', 'DESC');
        return $qb->getQuery()->getResult();
    }
}
