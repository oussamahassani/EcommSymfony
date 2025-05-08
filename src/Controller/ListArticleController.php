<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;


final class ListArticleController extends AbstractController
{
    // src/Controller/ArticleController.php

    #[Route('/list/article', name: 'app_listarticle')]
    public function index(ArticleRepository $articleRepository , PaginatorInterface $paginator, Request $request): Response
    {
        // Utiliser la méthode personnalisée pour récupérer les articles avec stock > 0
        $articles = $articleRepository->findArticlesWithStockGreaterThanZero();

       // $query = $articleRepository->createQueryBuilder('a')->getQuery();

        // Pagination : 6 articles par page
       /* $articles = $paginator->paginate(
            $query, 
            $request->query->getInt('page', 1), // Numéro de page par défaut = 1
            6 // Nombre d'articles par page
        );*/

        return $this->render('list_article/index.html.twig', [
            'articles' => $articles,
        ]);
        
    
    }


    #[Route('/list/article/search', name: 'article_index')]
    public function search(Request $request, ArticleRepository $articleRepository): Response
    {
        $nomArticle = $request->query->get('nom_article');

        if ($nomArticle) {
            $articles = $articleRepository->findByNom($nomArticle);
        } else {
            $articles = $articleRepository->findAll();
        }

        return $this->render('list_article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/list/article/category/{category}', name: 'list_article_by_categorie')]
    public function filterByCategory(string $category, ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findBy(['category' => $category]);

        return $this->render('list_article/index.html.twig', [
            'articles' => $articles,
        ]);
    }
}