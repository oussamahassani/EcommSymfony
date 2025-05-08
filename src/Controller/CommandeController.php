<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Article;
use App\Entity\User;
use App\Entity\ListArticle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse; // Utilisez cette classe
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ListArticleRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Repository\CommandeRepository;

final class CommandeController extends AbstractController
{
    private $security;
    private $listArticleRepository;

    public function __construct(Security $security, ListArticleRepository $listArticleRepository)
    {
        $this->security = $security;
        $this->listArticleRepository = $listArticleRepository;
    }
/*
    #[Route('/add-to-cart/{id}', name: 'add_to_cart', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Assure que l'utilisateur est connecté
    public function addToCart(int $id, EntityManagerInterface $em, Request $request, Security $security): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter un article au panier.');
            return $this->redirectToRoute('login'); // Redirection vers la page de connexion
        }
    
        // Récupérer l'article à partir de l'ID
        $article = $em->getRepository(Article::class)->find($id);
    
        if (!$article) {
            $this->addFlash('error', 'Article non trouvé.');
            return $this->redirect($request->headers->get('referer'));
        }
    
        // Vérifier la quantité de stock
        if ($article->getQuantiteStock() == 0) {
            $this->addFlash('error', 'Cet article est épuisé.');
            return $this->redirect($request->headers->get('referer')); // Retour à la page précédente
        }
    
        // Vérifier si l'article est déjà dans le panier pour cet utilisateur
        $existingCartItem = $em->getRepository(ListArticle::class)->findOneBy([
            'article' => $article,
            'user' => $user // Assurez-vous que la relation User est bien définie dans ListArticle
        ]);
    
        if ($existingCartItem) {
            $existingCartItem->setQuantite($existingCartItem->getQuantite() + 1);
        } else {
            $cartItem = new ListArticle();
            $cartItem->setArticle($article);
            $cartItem->setPrixUnitaire($article->getPrix());
            $cartItem->setQuantite(1);
            $cartItem->setUser($user); // Associer l'article ajouté à l'utilisateur
            $em->persist($cartItem);
        }
    
        $em->flush();
    
        $this->addFlash('success', 'Article ajouté au panier.');
    
        return $this->redirect($request->headers->get('referer'));
    }
  */
  #[Route('/add-to-cart/{id}', name: 'add_to_cart', methods: ['GET', 'POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')] // Assure que l'utilisateur est connecté
public function addToCart(int $id, EntityManagerInterface $em, Request $request, Security $security): Response
{
    // Vérifier si l'utilisateur est connecté
    $user = $security->getUser();
    if (!$user) {
        $this->addFlash('error', 'Vous devez être connecté pour ajouter un article au panier.');
        return $this->redirectToRoute('login'); // Redirection vers la page de connexion
    }

    // Récupérer l'article à partir de l'ID
    $article = $em->getRepository(Article::class)->find($id);

    if (!$article) {
        $this->addFlash('error', 'Article non trouvé.');
        return $this->redirect($request->headers->get('referer'));
    }

    // Vérifier la quantité de stock
    $stockDisponible = $article->getQuantiteStock();
    if ($stockDisponible == 0) {
        $this->addFlash('error', 'Cet article est épuisé.');
        return $this->redirect($request->headers->get('referer')); // Retour à la page précédente
    }

    // Vérifier si l'article est déjà dans le panier pour cet utilisateur
    $existingCartItem = $em->getRepository(ListArticle::class)->findOneBy([
        'article' => $article,
        'user' => $user // Assurez-vous que la relation User est bien définie dans ListArticle
    ]);

    if ($existingCartItem) {
        $nouvelleQuantite = $existingCartItem->getQuantite() + 1;

        // Vérifier si la nouvelle quantité dépasse le stock disponible
        if ($nouvelleQuantite > $stockDisponible) {
            $this->addFlash('error', 'Quantité maximale atteinte. Il ne reste que ' . $stockDisponible . ' article(s) en stock.');
            return $this->redirect($request->headers->get('referer'));
        }

        // Sinon, mettre à jour la quantité dans le panier
        $existingCartItem->setQuantite($nouvelleQuantite);
    } else {
        // Vérifier si on peut ajouter au moins 1 article
        if ($stockDisponible < 1) {
            $this->addFlash('error', 'Stock insuffisant pour ajouter cet article.');
            return $this->redirect($request->headers->get('referer'));
        }

        // Ajouter un nouvel article au panier
        $cartItem = new ListArticle();
        $cartItem->setArticle($article);
        $cartItem->setPrixUnitaire($article->getPrix());
        $cartItem->setQuantite(1);
        $cartItem->setUser($user);
        $em->persist($cartItem);
    }

    $em->flush();

    $this->addFlash('success', 'Article ajouté au panier.');

    return $this->redirect($request->headers->get('referer'));
}
#[Route('/add-cart/{id}', name: 'add_cart', methods: ['GET', 'POST'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')] // Assure que l'utilisateur est connecté
public function addCart(int $id, EntityManagerInterface $em, Request $request, Security $security): Response
{
    // Vérifier si l'utilisateur est connecté
    $user = $security->getUser();
    if (!$user) {
        $this->addFlash('error', 'Vous devez être connecté pour ajouter un article au panier.');
        return $this->redirectToRoute('login'); // Redirection vers la page de connexion
    }

    // Récupérer l'article à partir de l'ID
    $article = $em->getRepository(Article::class)->find($id);

    if (!$article) {
        $this->addFlash('error', 'Article non trouvé.');
        return $this->redirect($request->headers->get('referer'));
    }

    // Vérifier la quantité de stock
    $stockDisponible = $article->getQuantiteStock();
    if ($stockDisponible == 0) {
        $this->addFlash('error', 'Cet article est épuisé.');
        return $this->redirect($request->headers->get('referer'));
    }

    // Vérifier si l'article est déjà dans le panier pour cet utilisateur
    $existingCartItem = $em->getRepository(ListArticle::class)->findOneBy([
        'article' => $article,
        'user' => $user // Assurez-vous que la relation User est bien définie dans ListArticle
    ]);

    if ($existingCartItem) {
        $nouvelleQuantite = $existingCartItem->getQuantite() + 1;

        // Vérifier si la nouvelle quantité dépasse le stock disponible
        if ($nouvelleQuantite > $stockDisponible) {
            $this->addFlash('error', 'Stock insuffisant. Il ne reste que ' . $stockDisponible . ' article(s).');
            return $this->redirect($request->headers->get('referer'));
        }

        // Sinon, mettre à jour la quantité dans le panier
        $existingCartItem->setQuantite($nouvelleQuantite);
    } else {
        // Vérifier si on peut ajouter au moins 1 article
        if ($stockDisponible < 1) {
            $this->addFlash('error', 'Stock insuffisant pour ajouter cet article.');
            return $this->redirect($request->headers->get('referer'));
        }

        // Ajouter un nouvel article au panier
        $cartItem = new ListArticle();
        $cartItem->setArticle($article);
        $cartItem->setPrixUnitaire($article->getPrix());
        $cartItem->setQuantite(1);
        $cartItem->setUser($user);
        $em->persist($cartItem);
    }

    $em->flush();

    $this->addFlash('success', 'Article ajouté au panier.');

    return $this->redirect($request->headers->get('referer'));
}

   /* #[Route('/add-cart/{id}', name: 'add_cart', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Assure que l'utilisateur est connecté
    public function addCart(int $id, EntityManagerInterface $em, Request $request, Security $security): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter un article au panier.');
            return $this->redirectToRoute('login'); // Redirection vers la page de connexion
        }
    
        // Récupérer l'article à partir de l'ID
        $article = $em->getRepository(Article::class)->find($id);
    
        if (!$article) {
            $this->addFlash('error', 'Article non trouvé.');
            return $this->redirect($request->headers->get('referer'));
        }
    
        // Vérifier la quantité de stock
        if ($article->getQuantiteStock() == 0) {
            $this->addFlash('error', 'Cet article est épuisé.');
            return $this->redirect($request->headers->get('referer')); // Retour à la page précédente
        }
    
        // Vérifier si l'article est déjà dans le panier pour cet utilisateur
        $existingCartItem = $em->getRepository(ListArticle::class)->findOneBy([
            'article' => $article,
            'user' => $user // Assurez-vous que la relation User est bien définie dans ListArticle
        ]);
    
        if ($existingCartItem) {
            $existingCartItem->setQuantite($existingCartItem->getQuantite() + 1);
        } else {
            $cartItem = new ListArticle();
            $cartItem->setArticle($article);
            $cartItem->setPrixUnitaire($article->getPrix());
            $cartItem->setQuantite(1);
            $cartItem->setUser($user); // Associer l'article ajouté à l'utilisateur
            $em->persist($cartItem);
        }
    
        $em->flush();
    
        $this->addFlash('success', 'Article ajouté au panier.');
    
        return $this->redirect($request->headers->get('referer'));
    } 
    */
    #[Route('/commande', name: 'app_commande')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(ListArticleRepository $listarticleRepository, Security $security): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
    
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à votre panier.');
            return $this->redirectToRoute('login');
        }

        // Utiliser le repository pour récupérer les articles de l'utilisateur connecté
        $paniers = $listarticleRepository->findByUser($user);

        // Calculer les totaux du panier
        $totalHT = 0;
        foreach ($paniers as $panier) {
            $totalHT += $panier->getQuantite() * $panier->getPrixUnitaire();
        }

        // Calculer la TVA
        $tva = $totalHT * 0.20;
        $totalTTC = $totalHT + $tva;

        return $this->render('commande/index.html.twig', [
            'paniers' => $paniers,
            'totalHT' => $totalHT,
            'tva' => $tva,
            'totalTTC' => $totalTTC,
        ]);
    }


    #[Route('/decrease-quantity/{id}', name: 'decrease_quantity', methods: ['POST'])]
    public function decreaseQuantity(int $id, EntityManagerInterface $em, Request $request): Response
    {
        // Récupérer l'article du panier
        $cartItem = $em->getRepository(ListArticle::class)->findOneBy(['article' => $id]);

        if ($cartItem && $cartItem->getQuantite() > 1) {
            // Diminuer la quantité
            $cartItem->setQuantite($cartItem->getQuantite() - 1);
            $this->addFlash('success', 'Quantité diminuée.');
        } else {
            // Supprimer l'article si la quantité est 1
            $em->remove($cartItem);
            $this->addFlash('success', 'Article supprimé du panier.');
        }

        // Enregistrer les modifications
        $em->flush();

        // Rediriger vers la même page pour actualiser le panier
        return $this->redirect($request->headers->get('referer'));
    }
/*
    #[Route('/remove-from-cart/{id}', name: 'remove_from_cart', methods: ['POST'])]
    public function removeFromCart(
        int $id,
        ListArticleRepository $panierRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): RedirectResponse // Utilisez RedirectResponse ici
    {
        // Récupérer l'article du panier par son ID
        $panier = $panierRepository->find($id);

        // Vérifier si l'article existe dans le panier
        if ($panier) {
            // Supprimer l'article du panier
            $entityManager->remove($panier);
            $entityManager->flush();

            // Afficher un message flash de succès
            $this->addFlash('success', 'L\'article a été supprimé du panier.');
        } else {
            // Si l'article n'existe pas, afficher un message d'erreur
            $this->addFlash('error', 'Cet article n\'existe pas dans le panier.');
        }

        // Rediriger l'utilisateur vers la page précédente
        return $this->redirect($request->headers->get('referer'));
    }
*/
#[Route('/remove-from-cart/{id}', name: 'remove_from_cart', methods: ['POST'])]
public function removeFromCart(
    int $id,
    ListArticleRepository $listArticleRepository,
    EntityManagerInterface $entityManager,
    Request $request
): RedirectResponse {
    // Récupérer l'article dans la table listearticle
    $article = $listArticleRepository->find($id);

    // Vérifier si l'article existe
    if ($article) {
        // Supprimer l'article uniquement de la table listearticle
        $entityManager->remove($article);
        $entityManager->flush();

        // Ajouter un message flash de succès
        $this->addFlash('success', 'L\'article a été supprimé de la liste.');
    } else {
        // Si l'article n'existe pas, afficher un message d'erreur
        $this->addFlash('error', 'Cet article n\'existe pas dans la liste.');
    }

    // Rediriger l'utilisateur vers la page précédente
    return $this->redirect($request->headers->get('referer'));
}

    #[Route('/admin/commandes', name: 'commandes_list')]
public function list(Request $request, CommandeRepository $commandeRepository)
{
    // Récupérer la page actuelle, sinon par défaut page 1
    $page = $request->query->getInt('page', 1);
    $limit = 4; // Nombre de commandes par page

    // Récupérer les commandes paginées avec les articles et leurs quantités
    $commandes = $commandeRepository->findPaginatedWithClientAndArticleNames($page, $limit);

    // Calculer le nombre total de commandes pour la pagination
    $totalCommandes = $commandeRepository->countAll();

    return $this->render('user/admin/order.html.twig', [
        'commandes' => $commandes,
        'currentPage' => $page,
        'totalPages' => ceil($totalCommandes / $limit),
    ]);
}

    /*
    #[Route('/admin/commandes', name: 'commandes_list')]
    public function list(Request $request, CommandeRepository $commandeRepository)
    {
        // Récupérer la page actuelle, sinon par défaut page 1
        $page = $request->query->getInt('page', 1);
        $limit = 6; // Nombre de commandes par page
    
        $commandes = $commandeRepository->findPaginatedWithClientAndArticleNames($page, $limit);
        
        // Calculer le nombre total de commandes pour la pagination
        $totalCommandes = $commandeRepository->countAll();
    
        return $this->render('user/admin/order.html.twig', [
            'commandes' => $commandes,
            'currentPage' => $page,
            'totalPages' => ceil($totalCommandes / $limit),
        ]);
    }
  */  

/*
    #[Route('/admin/commandes', name: 'commandes_list')]
    public function list(CommandeRepository $commandeRepository)
    {
        $commandes = $commandeRepository->findAllWithClientAndArticleNames();

        return $this->render('user/admin/order.html.twig', [
            'commandes' => $commandes,
        ]);
    }
*/
/*
    #[Route('/factures/data', name: 'factures_data')]
    public function getFactureData()
    {
        // Récupérer les articles de la base de données
        $articles = $this->getDoctrine()
                         ->getRepository(ListArticle::class)
                         ->findAll();

        // Calcul des totaux (vous pouvez ajuster cela en fonction de vos besoins)
        $totalHT = 0;
        foreach ($articles as $article) {
            $totalHT += $article->getPrixUnitaire() * $article->getQuantite();
        }

        // Simuler la TVA et le total TTC
        $tva = $totalHT * 0.2;  // TVA 20%
        $totalTTC = $totalHT + $tva;

        // Organiser les données
        $data = [
            'paniers' => array_map(function ($article) {
                return [
                    'nom' => $article->getNom(),
                    'prixUnitaire' => $article->getPrixUnitaire(),
                    'quantite' => $article->getQuantite(),
                ];
            }, $articles),
            'totalHT' => $totalHT,
            'tva' => $tva,
            'totalTTC' => $totalTTC,
        ];

        // Retourner les données sous forme de réponse JSON
        return new JsonResponse($data);
    }
  */  


  
    
/*
    #[Route('/get-logo-image', name: 'get_logo_image')]
    public function getLogoImage(): JsonResponse
    {
        // Obtient le chemin de l'image à partir du répertoire public
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/logo.png';

        // Vérifie si le fichier existe
        if (!file_exists($logoPath)) {
            return new JsonResponse(['error' => 'Image non trouvée'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Lire l'image et la convertir en base64
        try {
            $imageData = base64_encode(file_get_contents($logoPath));
        } catch (\Exception $e) {
            // Si une erreur survient lors de la lecture du fichier
            return new JsonResponse(['error' => 'Erreur lors de la lecture de l\'image'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Retourner la réponse en format JSON contenant l'image encodée en Base64
        return new JsonResponse(['imageData' => $imageData]);
    }

    #[Route('/generate-invoice', name: 'generate_invoice')]
    public function generateInvoice()
    {
        try {
            // Récupérer l'utilisateur connecté
            $user = $this->security->getUser();
    
            if (!$user) {
                return new JsonResponse(['error' => 'Utilisateur non connecté'], 401);
            }
    
            // Récupérer les articles de l'utilisateur connecté
            $articles = $this->listArticleRepository->findByUser($user);
    
            if (!$articles || count($articles) == 0) {
                return new JsonResponse(['error' => 'Aucun article trouvé pour cet utilisateur'], 404);
            }
    
            // Calculer les totaux (HT, TVA, TTC)
            $totalHT = 0;
            foreach ($articles as $article) {
                $totalHT += $article->getPrixUnitaire() * $article->getQuantite();
            }
    
            $tva = $totalHT * 0.2;  // 20% de TVA
            $totalTTC = $totalHT + $tva;
    
            // Créer un tableau des articles à envoyer en JSON
            $data = [
                'paniers' => array_map(function($article) {
                    return [
                        'nom' => $article->getNom(),
                        'prixUnitaire' => (float) $article->getPrixUnitaire(),
                        'quantite' => (int) $article->getQuantite(),
                        'total' => (float) ($article->getPrixUnitaire() * $article->getQuantite())
                    ];
                }, $articles),
                'totalHT' => (float) $totalHT,
                'tva' => (float) $tva,
                'totalTTC' => (float) $totalTTC
            ];
    
            return new JsonResponse($data);
    
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue : ' . $e->getMessage()], 500);
        }
    }
*/
}