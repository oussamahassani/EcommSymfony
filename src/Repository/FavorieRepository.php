<?php

namespace App\Repository;

use App\Entity\Favorie;
use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Favorie>
 */
class FavorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favorie::class);
    }

    public function findByArticles(array $articles)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.article IN (:articles)')
            ->setParameter('articles', $articles)
            ->getQuery()
            ->getResult();
    }

    public function findNonExpiredFavoriesByUser($user): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->andWhere('f.date_expiration >= :now')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }
    
    
    public function findFavoriteClientsAndArticles(): array
    {
        return $this->createQueryBuilder('f')
            ->select(
                'u.id as clientId', 
                'u.name as clientName', 
                'u.lastName as clientLastName', 
                'a.nom as articleName'
            )
            ->innerJoin('f.user', 'u') // Jointure avec la table User
            ->innerJoin('f.article', 'a') // Jointure avec la table Article
            ->getQuery()
            ->getResult();
    }

    public function findExpiredFavories(\DateTime $currentDate)
{
    return $this->createQueryBuilder('f')
        ->where('f.dateExpiration < :currentDate')
        ->setParameter('currentDate', $currentDate)
        ->getQuery()
        ->getResult();
}

//    /**
//     * @return Favorie[] Returns an array of Favorie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Favorie
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
