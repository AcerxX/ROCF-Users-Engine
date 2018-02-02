<?php

namespace App\Repository;

use App\Entity\Token;
use Doctrine\ORM\EntityRepository;

class TokenRepository extends EntityRepository
{
    /**
     * @param $tokenString
     * @return Token|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getValidToken($tokenString): ?Token
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.token = :token')->setParameter('token', $tokenString)
            ->andWhere('t.expireDate > :now')->setParameter('now', new \DateTime());

        return $qb->getQuery()
            ->getOneOrNullResult();
    }
}
