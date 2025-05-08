<?php
namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatistiqueController extends AbstractController
{
    #[Route('/admin/statistique', name: 'app_statistique' , defaults: ['page' => 1])]
    public function index(CommandeRepository $commandeRepository, ArticleRepository $articleRepository): Response
    {
        // Récupérer les statistiques des ventes par produit
        $stats = $commandeRepository->countSalesByProduct();

        // Extraire les noms des produits et le nombre de ventes pour Chart.js
        $articleNoms = array_map(fn($stat) => $stat['nom'], $stats);
        $sales = array_map(fn($stat) => $stat['sales'], $stats);

        // Récupérer les quantités totales des produits
        $quantites = $articleRepository->getTotalQuantitiesByProduct();

        // Extraire les noms des produits et les quantités totales pour le diagramme circulaire
        $articleNoms = array_map(fn($product) => $product['nom'], $quantites);
        $quantities = array_map(fn($product) => $product['quantitestock'], $quantites);

        return $this->render('statistique/index.html.twig', [
            'productNoms' => json_encode($articleNoms), 
            'sales' => json_encode($sales),
            'productNames' => json_encode($articleNoms), 
            'quantities' => json_encode($quantities),
        ]);
        
    }
}