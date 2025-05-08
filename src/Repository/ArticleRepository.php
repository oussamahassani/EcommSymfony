<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findByNom(string $nom): array
    {
    return $this->createQueryBuilder('a')
        ->where('a.nom LIKE :nom')
        ->setParameter('nom', '%' . $nom . '%')
        ->getQuery()
        ->getResult();
    }
    
    public function findByNomArticle($nomArticle)
    {
        return $this->createQueryBuilder('a')
            ->where('a.nom LIKE :nom')
            ->setParameter('nom', '%' . $nomArticle . '%') // Utilise LIKE pour la recherche partielle
            ->getQuery()
            ->getResult();
    }
    // src/Repository/ArticleRepository.php

    public function findArticlesWithStockGreaterThanZero()
    {
        return $this->createQueryBuilder('a')
            ->where('a.quantitestock > 0')
            ->getQuery()
            ->getResult();
    }

    public function deleteArticle($article)
    {
        $entityManager = $this->getEntityManager();
    
        // Supprimer les favoris liés à cet article
        $entityManager->createQuery('DELETE FROM App\Entity\Favorie f WHERE f.article = :article')
                  ->setParameter('article', $article)
                  ->execute();
    
        // Maintenant, on peut supprimer l'article
        $entityManager->remove($article);
        $entityManager->flush();
    }

    public function getTotalQuantitiesByProduct()
    {
        return $this->createQueryBuilder('a')
            ->select('a.nom', 'SUM(a.quantitestock) as quantitestock')
            ->groupBy('a.nom')
            ->getQuery()
            ->getResult();
    }


    public function getArticlesWithStockZero()
    {
        return $this->createQueryBuilder('a')
            ->where('a.quantitestock = 0')
            ->groupBy('a.nom')
            ->getQuery()
            ->getResult();
    }

    

    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}