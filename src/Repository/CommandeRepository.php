<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function findByUser($user)
{
    return $this->createQueryBuilder('a')
        ->andWhere('a.user = :user')
        ->setParameter('user', $user)
        ->getQuery()
        ->getResult();
}

    public function countSalesByProduct(): array
    {
        $conn = $this->getEntityManager()->getConnection();
    
        // Récupérer tous les article_ids depuis la table commande
        $sql = "SELECT article_ids FROM commande";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $commandes = $resultSet->fetchAllAssociative();
    
        // Récupérer tous les articles depuis la table article
        $sqlArticles = "SELECT id, nom FROM article";
        $stmtArticles = $conn->prepare($sqlArticles);
        $resultSetArticles = $stmtArticles->executeQuery();
        $articles = $resultSetArticles->fetchAllAssociative();
    
        // Créer un tableau associatif pour mapper les IDs des articles à leurs noms
        $articleMap = [];
        foreach ($articles as $article) {
            $articleMap[$article['id']] = $article['nom'];
        }
    
        $productSales = [];
    
        // Parcourir les commandes et compter les ventes par produit
        foreach ($commandes as $commande) {
            $articleIds = json_decode($commande['article_ids'], true);
    
            if (is_array($articleIds)) {
                foreach ($articleIds as $productId) {
                    if (!isset($productSales[$productId])) {
                        $productSales[$productId] = [
                            'nom' => $articleMap[$productId] ?? 'Unknown Product', // Utiliser le nom de l'article
                            'sales' => 0
                        ];
                    }
                    $productSales[$productId]['sales']++;
                }
            }
        }
    
        // Convertir en format SQL-friendly
        $result = [];
        foreach ($productSales as $productId => $data) {
            $result[] = [
                'product_id' => $productId,
                'nom' => $data['nom'],
                'sales' => $data['sales']
            ];
        }
    
        return $result;
    }

    // Dans CommandeRepository
    public function countPaymentsByMode(): array
    {
        return $this->createQueryBuilder('c')
            ->select('
                SUM(CASE WHEN c.modePaiement = :card THEN 1 ELSE 0 END) AS card,
                SUM(CASE WHEN c.modePaiement = :especes THEN 1 ELSE 0 END) AS especes
            ')
            ->setParameter('card', 'card')
            ->setParameter('especes', 'especes')
            ->getQuery()
            ->getSingleResult();
    }

    public function findAllWithClientAndArticleNames()
    {
        // Récupérer toutes les commandes avec les informations du client
        $commandes = $this->createQueryBuilder('c')
            ->leftJoin('c.client', 'client')
            ->addSelect('client') // Charger l'entité client
            ->getQuery()
            ->getResult();

        // Pour chaque commande, récupérer les noms des articles
        $entityManager = $this->getEntityManager();
        $articleRepository = $entityManager->getRepository(\App\Entity\Article::class);

        foreach ($commandes as &$commande) {
            $articleIds = $commande->getArticleIds();
            $articleNames = [];

            if (!empty($articleIds)) {
                $articles = $articleRepository->findBy(['id' => $articleIds]);
                foreach ($articles as $article) {
                    $articleNames[] = $article->getNom();
                }
            }

            $commande->articleNames = $articleNames; // Ajouter les noms des articles à la commande
        }

        return $commandes;
    }
/*
    public function findPaginatedWithClientAndArticleNames(int $page, int $limit)
{
    // Définir la pagination : calculer l'offset et la limite
    $offset = ($page - 1) * $limit;

    // Récupérer les commandes avec les informations du client, limitées par la pagination
    $commandes = $this->createQueryBuilder('c')
        ->leftJoin('c.client', 'client')
        ->addSelect('client') // Charger l'entité client
        ->setFirstResult($offset)
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();

    // Récupérer les noms des articles pour chaque commande
    $entityManager = $this->getEntityManager();
    $articleRepository = $entityManager->getRepository(\App\Entity\Article::class);

    foreach ($commandes as &$commande) {
        $articleIds = $commande->getArticleIds();
        $articleNames = [];

        if (!empty($articleIds)) {
            $articles = $articleRepository->findBy(['id' => $articleIds]);
            foreach ($articles as $article) {
                $articleNames[] = $article->getNom();
            }
        }

        $commande->articleNames = $articleNames; // Ajouter les noms des articles à la commande
    }

    return $commandes;
}
*/
public function findPaginatedWithClientAndArticleNames(int $page, int $limit)
{
    // Définir la pagination : calculer l'offset et la limite
    $offset = ($page - 1) * $limit;

    // Récupérer les commandes avec les informations du client, limitées par la pagination
    $commandes = $this->createQueryBuilder('c')
        ->leftJoin('c.client', 'client')
        ->addSelect('client') // Charger l'entité client
        ->setFirstResult($offset)
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();

    // Récupérer les articles et leurs quantités pour chaque commande
    $entityManager = $this->getEntityManager();
    $articleRepository = $entityManager->getRepository(\App\Entity\Article::class);

    foreach ($commandes as &$commande) {
        // Récupérer les IDs des articles et leurs quantités
        $articleIds = $commande->getArticleIds();
        $quantites = $commande->getQuantites();

        $articlesWithQuantities = [];
        if (!empty($articleIds)) {
            // Récupérer les articles correspondants
            $articles = $articleRepository->findBy(['id' => $articleIds]);

            // Associer chaque article à sa quantité
            foreach ($articles as $article) {
                $articleId = $article->getId();
                $articlesWithQuantities[] = [
                    'name' => $article->getNom(),
                    'quantity' => $quantites[$articleId] ?? 0, // Utiliser la quantité correspondante
                ];
            }
        }

        // Ajouter les articles et leurs quantités à la commande
        $commande->articlesWithQuantities = $articlesWithQuantities;
    }

    return $commandes;
}
public function countAll()
{
    return $this->createQueryBuilder('c')
        ->select('COUNT(c.id)')
        ->getQuery()
        ->getSingleScalarResult();
}


    //    /**
    //     * @return Commande[] Returns an array of Commande objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Commande
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}