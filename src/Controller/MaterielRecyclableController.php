<?php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\MaterielRecyclable;
use App\Form\MaterielRecyclableType;
use App\Form\MaterielRecyclableEditType;
use App\Repository\MaterielRecyclableRepository;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Enum\StatutEnum;
use Symfony\Component\Validator\Constraints as Assert;

class MaterielRecyclableController extends AbstractController
{
    private string $uploadsDirectory;

    public function __construct(string $uploadsDirectory)
    {
        $this->uploadsDirectory = $uploadsDirectory;
    }

    #[Route('/materiel/recyclable', name: 'app_materiel_recyclable')]
    public function index(): Response
    {
        return $this->render('materiel_recyclable/index.html.twig', [
            'controller_name' => 'MaterielRecyclableController',
        ]);
    }

    #[Route('/ajouter', name: 'ajouter_materiel_recyclable')]
    public function ajouter(Request $request, EntityManagerInterface $entityManager): Response
    {
        $materiel = new MaterielRecyclable();
        $materiel->setStatut(StatutEnum::EN_ATTENTE);
        $form = $this->createForm(MaterielRecyclableType::class, $materiel);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $materiel->setDatecreation(new \DateTimeImmutable());
            if ($user) {
                $materiel->setUser($user);
            }
            else{
                return $this->redirectToRoute('login');
            }
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $fileName = uniqid('img_', true) . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move($this->uploadsDirectory, $fileName);
                    $materiel->setImage($fileName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
                    return $this->redirectToRoute('ajouter_materiel_recyclable');
                }
            }
            $entityManager->persist($materiel);
            $entityManager->flush();
            $this->addFlash('success', 'Matériel ajouté avec succès !');
            return $this->redirectToRoute('app_materiaux_liste');
        }
        return $this->render('materiel_recyclable/ajouter.html.twig', [
            'form' => $form->createView(),
            'materielRecyclable' => $materiel
        ]);
    }

    #[Route('/materiaux', name: 'app_materiaux_liste')]
    public function liste(EntityManagerInterface $entityManager): Response
    {
        $materiaux = $entityManager->getRepository(MaterielRecyclable::class)->findAll();
        return $this->render('materiel_recyclable/list.html.twig', [
            'materiaux' => $materiaux,
        ]);
    }

    #[Route('/materiel/edit/{id}', name: 'materiel_edit')]
    public function edit(int $id, MaterielRecyclableRepository $materielRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Retrieve the article to be edited
        $materiel = $materielRepository->find($id);
        if (!$materiel) {
            throw $this->createNotFoundException('The material does not exist.');
        }

        // Create an edit form for the article
        $form = $this->createForm(MaterielRecyclableType::class, $materiel);

        // Handle form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $materiel = $form->getData();
            // Handle image upload
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                try {
                    $file->move($this->uploadsDirectory, $fileName);
                    $materiel->setImage($fileName);
                } catch (FileException $e) {
                    // Handle file upload error
                    $this->addFlash('error', 'There was an error uploading your Material image: ' . $e->getMessage());
                    return $this->redirectToRoute('app_materiaux_liste');
                }
            }
            // Debug: Check the article before persisting
            $this->addFlash('success', 'Updating Material: ' . $materiel->getName());
            $entityManager->persist($materiel);
            $entityManager->flush();
            // Redirect back to the list of articles
            return $this->redirectToRoute('app_materiaux_liste');
        }

        return $this->render('materiel_recyclable/edit.html.twig', [
            'materiel' => $materiel,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/materiel/delete/{id}', name: 'materiel_delete')]
    public function delete(int $id, EntityManagerInterface $entityManager): RedirectResponse
    {
        $materiel = $entityManager->getRepository(MaterielRecyclable::class)->find($id);
        if ($materiel) {
            if ($materiel->getImage()) {
                $imagePath = $this->uploadsDirectory . '/' . $materiel->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $entityManager->remove($materiel);
            $entityManager->flush();
            $this->addFlash('success', 'Matériel recyclable supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Matériel recyclable non trouvé.');
        }
        return $this->redirectToRoute('app_materiaux_liste');
    }

    #[Route('/admin/listMaterials', name: 'list_materials_admin')]
    public function listMaterial(MaterielRecyclableRepository $materielRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $materiels = $materielRepository->findAll();
        $materiel = new MaterielRecyclable();
        $form = $this->createForm(MaterielRecyclableType::class, $materiel);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $materiel = $form->getData();
            $materiel->setDatecreation(new \DateTimeImmutable());
            
            // Fetch the current user from the session
            $user = $this->getUser();
            if ($user) {
                $materiel->setUser($user);
            }

            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                try {
                    $file->move($this->uploadsDirectory, $fileName);
                    $materiel->setImage($fileName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'There was an error uploading your Material image: ' . $e->getMessage());
                    return $this->redirectToRoute('list_materials_admin');
                }
            } else {
                $this->addFlash('error', $materiel->getName() . ' was added without an Image!');
                $materiel->setImage('default-image.jpg');
            }
            $materiel->setStatut(StatutEnum::EN_ATTENTE);
            $this->addFlash('success', $materiel->getName() . ' was added successfully!');
            $entityManager->persist($materiel);
            $entityManager->flush();
            return $this->redirectToRoute('list_materials_admin');
        }
        return $this->render('materiel_recyclable/list_materiel_recyclable_dashboard.html.twig', [
            'materiels' => $materiels,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/editMaterial/{id}', name: 'edit_material_admin')]
    public function editMaterial(int $id, MaterielRecyclableRepository $materielRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $materiel = $materielRepository->find($id);
        if (!$materiel) {
            throw $this->createNotFoundException('The material does not exist.');
        }
        $form = $this->createForm(MaterielRecyclableEditType::class, $materiel);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $materiel = $form->getData();
            $this->addFlash('success', 'Updating Material: ' . $materiel->getName());
            $entityManager->persist($materiel);
            $entityManager->flush();
            return $this->redirectToRoute('list_materials_admin');
        }
        return $this->render('materiel_recyclable/edit_materiel_recyclable_dashboard.html.twig', [
            'materiel' => $materiel,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/deleteMaterial/{id}', name: 'delete_material_admin')]
    public function deleteMaterial(EntityManagerInterface $entityManager, int $id): Response
    {
        $materiel = $entityManager->getRepository(MaterielRecyclable::class)->find($id);
        if (!$materiel) {
            throw $this->createNotFoundException('Matériel recyclable non trouvé.');
        }
        if ($materiel->getImage()) {
            $imagePath = $this->uploadsDirectory . '/' . $materiel->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $entityManager->remove($materiel);
        $entityManager->flush();
        $this->addFlash('success', $materiel->getName() . ' a été supprimé avec succès !');
        return $this->redirectToRoute('list_materials_admin');
    }
}