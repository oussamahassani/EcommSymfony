<?php

namespace App\Controller;

use App\Entity\Favorie;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\FavorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

final class FavorieController extends AbstractController
{
    #[Route('/favorie', name: 'app_favorie')]
    public function index(FavorieRepository $favorieRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        if (!$user) {
            // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
            return $this->redirectToRoute('login');
        }

        // Récupérer les favoris non expirés de l'utilisateur connecté
        $favories = $favorieRepository->findNonExpiredFavoriesByUser($user);

        return $this->render('favorie/index.html.twig', [
            'favories' => $favories,
        ]);
    }

    #[Route('/add-to-favorites/{articleId}', name: 'add_to_favorites')]
public function addToFavorites(
    int $articleId,
    EntityManagerInterface $em,
    ArticleRepository $articleRepository,
    Security $security
): RedirectResponse {
    // Récupérer l'utilisateur connecté
    $user = $this->getUser();

    if (!$user) {
        // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
        return $this->redirectToRoute('login');
    }
    // Récupérer l'article
    $article = $articleRepository->find($articleId);

    if (!$article) {
        $this->addFlash('error', 'Article non trouvé.');
        return $this->redirectToRoute('app_listarticle');
    }

    // Récupérer l'utilisateur actuel
    $user = $security->getUser();

    // Vérifier si l'article est déjà dans les favoris
    $existingFavorite = $em->getRepository(Favorie::class)->findOneBy([
        'article' => $article,
        'user' => $user,
    ]);

    // Si l'article est déjà dans les favoris
    if ($existingFavorite) {
        // Vérifier si la date d'expiration est dépassée
        if ($existingFavorite->getDateExpiration() < new \DateTime()) {
            // Si la date est dépassée, mettre à jour la date d'expiration
            $existingFavorite->setDateExpiration((new \DateTime())->modify('+1 day'));
            $em->flush();
            $this->addFlash('success', 'Article ajouté aux favoris à nouveau.');
        } else {
            // Si la date n'est pas dépassée, afficher un message d'erreur
            $this->addFlash('error', 'Cet article est déjà dans vos favoris.');
        }
    } else {
        // Si l'article n'est pas dans les favoris, l'ajouter
        $favorie = new Favorie();
        $favorie->setArticle($article);
        $favorie->setUser($user);
        $favorie->setDateCreation(new \DateTime());
        $favorie->setDateExpiration((new \DateTime())->modify('+1 day'));

        $em->persist($favorie);
        $em->flush();

        $this->addFlash('success', 'Article ajouté aux favoris.');
    }

    return $this->redirectToRoute('app_listarticle');
}
    


    #[Route('/favorites', name: 'list_favorites')]
    public function listFavorites(FavorieRepository $favorieRepository): Response
    {
        $favories = $favorieRepository->findAll();

        return $this->render('favorie/list.html.twig', [
            'favories' => $favories,
        ]);
    }

    #[Route('/favorie/search', name: 'favorie_index')]
    public function search(Request $request, ArticleRepository $articleRepository, FavorieRepository $favorieRepository): Response
    {
        $nomArticle = $request->query->get('nom_article'); // Récupérer le terme de recherche

        // Si un nom d'article est fourni
        if ($nomArticle) {
            // Trouver les articles par nom
            $articles = $articleRepository->findByNom($nomArticle);

            // Trouver les favoris liés aux articles trouvés
            $favories = $favorieRepository->findByArticles($articles);
        } else {
            // Si aucun terme de recherche n'est donné, afficher tous les favoris
            $favories = $favorieRepository->findAll();
        }

        return $this->render('favorie/index.html.twig', [
            'favories' => $favories,
        ]);
    }

    #[Route('/supprimer/{id}', name: 'supprimer')]
    public function supprimer(FavorieRepository $favorieRepository, $id, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Récupérer l'article favori par ID
        $favorie = $favorieRepository->find($id);

        if ($favorie) {
            // Supprimer l'article des favoris
            $entityManager->remove($favorie);
            $entityManager->flush();

            // Ajoutez un message flash pour informer l'utilisateur
            $this->addFlash('success', 'Article supprimé des favoris.');
        }

        return $this->redirectToRoute('app_favorie'); // Redirige vers la liste des favoris
    }

    #[Route('/clean-expired-favorites', name: 'clean_expired_favorites')]
public function cleanExpiredFavorites(FavorieRepository $favorieRepository, EntityManagerInterface $entityManager): Response
{
    // Récupérer tous les favoris expirés
    $expiredFavories = $favorieRepository->findExpiredFavories(new \DateTime());

    foreach ($expiredFavories as $favorie) {
        // Supprimer le favori expiré
        $entityManager->remove($favorie);
    }

    // Enregistrer les modifications dans la base de données
    $entityManager->flush();

    return new Response('Expired favorites cleaned successfully.');
}
}