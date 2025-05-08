<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProduitsController extends AbstractController
{
    #[Route('/produits', name: 'app_produits')]
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();

        return $this->render('produits/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/produits/search', name: 'produits_index')]
    public function search(Request $request, ArticleRepository $articleRepository): Response
    {
        $nomArticle = $request->query->get('nom_article');

        if ($nomArticle) {
            $articles = $articleRepository->findByNom($nomArticle);
        } else {
            $articles = $articleRepository->findAll();
        }

        return $this->render('produits/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/produits/category/{category}', name: 'produits_by_categorie')]
    public function filterByCategory(string $category, ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findBy(['category' => $category]);

        return $this->render('produits/index.html.twig', [
            'articles' => $articles,
        ]);
    }


    
}