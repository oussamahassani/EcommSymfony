<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Repository\FactureRepository;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class FactureController extends AbstractController
{
    private FactureRepository $factureRepository;

    public function __construct(FactureRepository $factureRepository)
    {
        $this->factureRepository = $factureRepository;
    }

    #[Route('/factures', name: 'factures_index', methods: ['GET'])]
    public function index(Request $request, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('login');
        }

        $factures = [];
        $searchDate = $request->query->get('date_facture');

        $qb = $this->factureRepository->createQueryBuilder('f')
            ->where('f.client = :client')
            ->setParameter('client', $user);

        if ($searchDate) {
            $date = new \DateTime($searchDate);
            $startOfDay = clone $date;
            $startOfDay->setTime(0, 0, 0);
            $endOfDay = clone $date;
            $endOfDay->setTime(23, 59, 59);

            $qb->andWhere('f.datetime >= :startOfDay AND f.datetime <= :endOfDay')
                ->setParameter('startOfDay', $startOfDay)
                ->setParameter('endOfDay', $endOfDay);
        }

        $factures = $qb->getQuery()->getResult();

        return $this->render('facture/index.html.twig', [
            'factures' => $factures,
        ]);
    }

    #[Route('/factures/details/{id}', name: 'facture_details', methods: ['GET'])]
    public function show(int $id, FactureRepository $factureRepository, ArticleRepository $articleRepository): Response
    {
        $facture = $factureRepository->find($id);
        if (!$facture) {
            throw $this->createNotFoundException('Facture introuvable.');
        }

        $commande = $facture->getCommande();
        $articles = [];
        if ($commande && $commande->getArticleIds()) {
            $articleIds = $commande->getArticleIds();
            $articles = $articleRepository->findBy(['id' => $articleIds]);

            // Ajouter la quantité pour chaque article
            foreach ($articles as $article) {
                $article->quantite = $commande->getQuantiteForArticle($article->getId());
            }
        }

        return $this->render('details/details.html.twig', [
            'facture' => $facture,
            'commande' => $commande,
            'articles' => $articles,
        ]);
    }

    #[Route('/factures/{id}', name: 'factures_delete', methods: ['POST'])]
    public function delete(Request $request, Facture $facture, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $facture->getId(), $request->request->get('_token'))) {
            $entityManager->remove($facture);
            $entityManager->flush();
        }

        return $this->redirectToRoute('factures_index');
    }
   

    #[Route('/factures/json/{id}', name: 'facture_json', methods: ['GET'])]
    public function getFactureJson(int $id, FactureRepository $factureRepository, ArticleRepository $articleRepository): JsonResponse
    {
        // Récupérer la facture par son ID
        $facture = $factureRepository->find($id);
        if (!$facture) {
            throw $this->createNotFoundException('Facture introuvable.');
        }

        // Récupérer le nom du client
        $clientName = $facture->getClient()->getName();

        // Récupérer les articles associés à la commande
        $commande = $facture->getCommande();
        $articles = [];
        if ($commande && $commande->getArticleIds()) {
            $articles = $articleRepository->findBy(['id' => $commande->getArticleIds()]);
        }

        // Structurer les données en JSON
        $factureData = [
            'id' => $facture->getId(),
            'montant' => $facture->getMontant(),
            'date' => $facture->getDatetime()->format('Y-m-d H:i'),
            'client' => [
                'id' => $facture->getClient()->getId(),
                'nom' => $clientName,
            ],
            'articles' => array_map(function ($article) use ($commande) {
               // Récupérer la quantité de l'article dans la commande
                $quantite = $commande->getQuantiteForArticle($article->getId());
                return [
                    'nom' => $article->getNom(),
                    'prix_unitaire' => $article->getPrix(),
                    'quantite' => $quantite,
                ];
            }, $articles),
        ];

        // Retourner une réponse JSON
        return new JsonResponse($factureData);
    }

    #[Route('/factures/telecharger-pdf-from-json/{id}', name: 'facture_telecharger_pdf_from_json', methods: ['GET'])]
    public function telechargerFacturePdfFromJson(int $id, FactureRepository $factureRepository, ArticleRepository $articleRepository): Response
    {
        // Appeler la méthode JSON pour récupérer les données
        $jsonResponse = $this->getFactureJson($id, $factureRepository, $articleRepository);
        $factureData = json_decode($jsonResponse->getContent(), true);
    
        // Encoder l'image en base64
        $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/logo.png';
        $imageBase64 = base64_encode(file_get_contents($imagePath));
    
        // Générer le HTML pour le PDF à partir des données JSON
        $html = $this->renderView('facture/pdf.html.twig', [
            'facture' => $factureData,
            'logo_base64' => $imageBase64, // Passer l'image encodée en base64 au template
        ]);
    
        // Configurer Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
    
        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    
        // Générer le fichier PDF
        $output = $dompdf->output();
        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="facture_' . $factureData['id'] . '.pdf"'); // Forcer le téléchargement
    
        return $response;
    }
    

  

}