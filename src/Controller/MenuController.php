<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenuController extends AbstractController
{
    #[Route('/admin/menus', name: 'admin_menus')]
    public function index(MenuRepository $menuRepository): Response
    {
        $menus = $menuRepository->findAll();
        return $this->render('menu/index.html.twig', [
            'menus' => $menus,
        ]);
    }

    #[Route('/admin/menus/create', name: 'admin_menus_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $menu = new Menu();
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($menu);
            $entityManager->flush();
            $this->addFlash('success', 'Menu item created successfully!');
            return $this->redirectToRoute('admin_menus');
        }

        return $this->render('menu/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/menus/edit/{id}', name: 'admin_menus_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $menu = $entityManager->getRepository(Menu::class)->find($id);

        if (!$menu) {
            throw $this->createNotFoundException('The menu item does not exist.');
        }

        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Menu item updated successfully!');
            return $this->redirectToRoute('admin_menus');
        }

        return $this->render('menu/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/menus/delete/{id}', name: 'admin_menus_delete')]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $menu = $entityManager->getRepository(Menu::class)->find($id);

        if (!$menu) {
            throw $this->createNotFoundException('The menu item does not exist.');
        }

        $entityManager->remove($menu);
        $entityManager->flush();
        $this->addFlash('success', 'Menu item deleted successfully!');

        return $this->redirectToRoute('admin_menus');
    }
}