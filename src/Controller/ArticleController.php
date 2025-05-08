<?php
namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    private string $uploadsDirectory;

    public function __construct(string $uploadsDirectory)
    {
        $this->uploadsDirectory = $uploadsDirectory;
    }

   /* #[Route('/admin/listArticles', name: 'list_articles_admin')]
    public function listArticles(ArticleRepository $articleRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $articles = $articleRepository->findAll();

        // Create a new article form
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        // Handle form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $article->setDatecreation(new \DateTimeImmutable());

            // Handle image upload
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                try {
                    $file->move($this->uploadsDirectory, $fileName);
                    $article->setImage($fileName);
                } catch (FileException $e) {
                    // Handle file upload error
                    $this->addFlash('error', 'There was an error uploading your Product image: ' . $e->getMessage());
                    return $this->redirectToRoute('list_articles_admin');
                }
            } else {
                $this->addFlash('error', $article->getNom() . ' was added without an Image!');
                $article->setImage('default-image.jpg');
            }
            $this->addFlash('success', $article->getNom() . ' was added successfully!');

            $entityManager->persist($article);
            $entityManager->flush();

            // Redirect back to the list of articles
            return $this->redirectToRoute('list_articles_admin');
        }

        return $this->render('article/list_articles_dashboard.html.twig', [
            'articles' => $articles,
            'form' => $form->createView(),
        ]);
    }*/
    #[Route('/admin/listArticles', name: 'list_articles_admin')]
public function listArticles(ArticleRepository $articleRepository, Request $request, EntityManagerInterface $entityManager): Response
{
    // Pagination
    $page = $request->query->getInt('page', 1); // Récupère le numéro de la page depuis l'URL, par défaut 1
    $pageSize = 3; // Nombre d'articles par page

    // Récupérer les articles paginés
    $query = $articleRepository->createQueryBuilder('a')
        ->getQuery();

    $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);
    $totalItems = count($paginator);
    $pagesCount = ceil($totalItems / $pageSize);

    $paginator
        ->getQuery()
        ->setFirstResult($pageSize * ($page - 1)) // Offset
        ->setMaxResults($pageSize); // Limit

    // Créer un nouveau formulaire pour l'article
    $article = new Article();
    $form = $this->createForm(ArticleType::class, $article);

    // Gérer la soumission du formulaire
    $form->handleRequest($request);
    $formErrors = false; // Initialisation de la variable formErrors

    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            $article = $form->getData();
            $article->setDatecreation(new \DateTimeImmutable());

            // Gérer l'upload de l'image
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                try {
                    $file->move($this->uploadsDirectory, $fileName);
                    $article->setImage($fileName);
                } catch (FileException $e) {
                    // Gérer l'erreur d'upload
                    $this->addFlash('error', 'There was an error uploading your Product image: ' . $e->getMessage());
                    return $this->redirectToRoute('list_articles_admin');
                }
            } else {
                $this->addFlash('error', $article->getNom() . ' was added without an Image!');
                $article->setImage('default-image.jpg');
            }
            $this->addFlash('success', $article->getNom() . ' was added successfully!');

            $entityManager->persist($article);
            $entityManager->flush();

            // Rediriger vers la liste des articles
            return $this->redirectToRoute('list_articles_admin');
        } else {
            // Si le formulaire est soumis mais non valide, nous définissons formErrors à true
            $formErrors = true;
            $this->addFlash('error', 'Il y a un erreur de saisie dans la formulaire.');
        }
    }

    return $this->render('article/list_articles_dashboard.html.twig', [
        'articles' => $paginator, // Passer les articles paginés au template
        'form' => $form->createView(),
        'formErrors' => $formErrors, // Passer la variable formErrors à Twig
        'totalItems' => $totalItems, // Passer le nombre total d'articles
        'pagesCount' => $pagesCount, // Passer le nombre total de pages
        'currentPage' => $page, // Passer la page actuelle
    ]);
}


    #[Route('/admin/editArticle/{id}', name: 'edit_article_admin')]
    public function editArticle(int $id, ArticleRepository $articleRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Retrieve the article to be edited
        $article = $articleRepository->find($id);
        if (!$article) {
            throw $this->createNotFoundException('The article does not exist.');
        }

        // Create an edit form for the article
        $form = $this->createForm(ArticleType::class, $article);

        // Handle form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            // Handle image upload
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                try {
                    $file->move($this->uploadsDirectory, $fileName);
                    $article->setImage($fileName);
                } catch (FileException $e) {
                    // Handle file upload error
                    $this->addFlash('error', 'There was an error uploading your Product image: ' . $e->getMessage());
                    return $this->redirectToRoute('list_articles_admin');
                }
            }
            // Debug: Check the article before persisting
            $this->addFlash('success', 'Updating Article: ' . $article->getNom() . ' - Image: ' . $article->getImage());
            $entityManager->persist($article);
            $entityManager->flush();
            // Redirect back to the list of articles
            return $this->redirectToRoute('list_articles_admin');
        }

        return $this->render('article/edit_article_dashboard.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/deleteArticle/{id}', name: 'delete_article_admin')]
    public function deleteArticle(EntityManagerInterface $entityManager, int $id): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Article non existant');
        }
        $entityManager->remove($article);
        $entityManager->flush();
        $this->addFlash('success', $article->getNom() . " has been successfully removed!");
        return $this->redirectToRoute('list_articles_admin');
    }
}