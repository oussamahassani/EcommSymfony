<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ArticleRepository;
use App\Repository\FactureRepository;

final class DetailsController extends AbstractController
{
    #[Route('/details/{id}', name: 'app_details')]
    public function index(int $id, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->find($id);
    
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé.');
        }
    
        return $this->render('details/index.html.twig', [
            'article' => $article,
        ]);
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
            'articles' => array_map(function ($article) {
                return [
                    'nom' => $article->getNom(),
                    'prix_unitaire' => $article->getPrix(),
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

        // Générer le HTML pour le PDF à partir des données JSON
        $html = $this->renderView('facture/pdf.html.twig', [
            'facture' => $factureData,
        ]);

        // Configurer Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Générer le fichier PDF
        $output = $dompdf->output();
        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="facture_' . $factureData['id'] . '.pdf"');

        return $response;
    }

}
