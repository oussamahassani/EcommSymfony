<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\ListArticle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListArticle>
 */
class ListArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListArticle::class);
    }

    public function findByCategorie(string $category)
    {
        return $this->createQueryBuilder('la')
            ->join('la.article', 'a')  // On joint l'entité Article
            ->where('a.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();
    }

    public function findByUser(User $user)
{
    return $this->createQueryBuilder('l')
        ->andWhere('l.user = :user')
        ->setParameter('user', $user)
        ->getQuery()
        ->getResult();
}
   


    //    /**
    //     * @return ListArticle[] Returns an array of ListArticle objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ListArticle
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}