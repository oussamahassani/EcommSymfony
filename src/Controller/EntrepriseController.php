<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\Entreprise;
use App\Form\CreateAccountEntrepriseType;
use App\Form\EntrepriseLoginType;
use App\Form\AdminLoginType;
use App\Form\UserType;
use App\Form\SearchType;
use App\Form\ProfileEntrepriseType;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Psr\Log\LoggerInterface;

final class EntrepriseController extends AbstractController
{
    #[Route('/entreprise', name: 'app_entreprise')]
    public function index(): Response
    {
        return $this->render('entreprise/index.html.twig', [
            'controller_name' => 'EntrepriseController',
        ]);
    }

    #[Route('/entreprise-login', name: 'entreprise_login')]
    public function loginEntreprise(Request $request, AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, UserPasswordHasherInterface $passwordHasher): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash('error', 'Email ou mot de passe invalide. Veuillez réessayer.');
        }
        $lastUsername = $authenticationUtils->getLastUsername();
        $form = $this->createForm(EntrepriseLoginType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->get('_username')->getData();
            $password = $form->get('_password')->getData();
            $entreprise = $entityManager->getRepository(Entreprise::class)->findUserWithRoleAndMenusByEmail($username);

            if (!$entreprise) {
                $this->addFlash('error', 'Email ou mot de passe invalide. Veuillez réessayer.');
                return $this->redirectToRoute('entreprise_login');
            }

            if (!in_array('ROLE_ENTREPRISE', $entreprise->getRoles())) {
                $this->addFlash('error', 'Entreprise non existant');
                return $this->redirectToRoute('entreprise_login');
            }

            if (!$passwordHasher->isPasswordValid($entreprise, $password)) {
                $this->addFlash('error', 'Email ou mot de passe invalide. Veuillez réessayer.');
                return $this->redirectToRoute('entreprise_login');
            }

            $tokenStorage->setToken(null);
            $token = new UsernamePasswordToken($entreprise, 'entreprise', $entreprise->getRoles());
            $tokenStorage->setToken($token);
            $this->denyAccessUnlessGranted('ROLE_ENTREPRISE');
            return $this->redirectToRoute('entreprise_dashboard');
        }

        return $this->render('user/entreprise/login.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/entreprise/logoutEntreprise', name: 'entreprise_logout')]
    public function logoutEntreprise(): void
    {
        // This method can remain empty - it will be intercepted by the logout handler
    }

    #[Route('/create-account-entreprise', name: 'create_account_entreprise')]
    public function createAccount(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder): Response
    {
        $entreprise = new Entreprise();
        $form = $this->createForm(CreateAccountEntrepriseType::class, $entreprise);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the plain password
            $entreprise->setPassword($passwordEncoder->hashPassword($entreprise, $entreprise->getPassword()));
            // Set the role to 'client'
            $role = $entityManager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_ENTREPRISE']);
            if (!$role) {
                $role = new Role();
                $role->setName('ROLE_ENTREPRISE');
                $entityManager->persist($role);
            }
            $entreprise->setRole($role);
            $entreprise->setSupplier(False);
            $entreprise->setCreatedAt(new \DateTimeImmutable());
            try {
                $entityManager->persist($entreprise);
                $entityManager->flush();
                $this->addFlash('success', 'Account created successfully!');
                return $this->redirectToRoute('entreprise_login');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'A user with this email address already exists.');
                return $this->redirectToRoute('create_account_entreprise');
            }
        }
        return $this->render('user/entreprise/create_account.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/entreprise/dashboard', name: 'entreprise_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ENTREPRISE');
        $entreprises = $entityManager->getRepository(Entreprise::class)->findAll();

        return $this->render('user/entreprise/dashboard.html.twig', [
            'entreprises' => $entreprises,
        ]);
    }

    #[Route('/entreprise/listPartners', name: 'list_partners')]
    public function listPartners(EntityManagerInterface $entityManager , LoggerInterface $logger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ENTREPRISE');
        $user = $this->getUser();

        $logger->info('Accessing listPartners route.', [
            'user' => $user ? $user->getUserIdentifier() : 'anonymous',

        ]);
    
        // Fetch the list of partners
        $partners = $entityManager->getRepository(Entreprise::class)->findAll();
        $logger->info('Number of partners retrieved.', [
            'count' => count($partners),
        ]);
        // Render the template and pass the partners
        return $this->render('user/entreprise/list_partners.html.twig', [
            'partners' => $partners,
        ]);
    }

    private $uploadsDirectory;

    public function __construct(string $uploadsDirectory)
    {
        $this->uploadsDirectory = $uploadsDirectory;
    }

    #[Route('/entreprise/profile', name: 'entreprise_profile')]
    public function profile(TokenStorageInterface $tokenStorage, Request $request, EntityManagerInterface $entityManager): Response
    {
        $token = $tokenStorage->getToken();
        if (!$token) {
            throw $this->createAccessDeniedException('Token not found or not authenticated.');
        }
        $user = $token->getUser();
        if (!$user instanceof Entreprise) {
            throw $this->createAccessDeniedException('Entreprise not found or not authenticated.');
        }
        $this->denyAccessUnlessGranted('ROLE_ENTREPRISE');

        $form = $this->createForm(ProfileEntrepriseType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('imageFile')->getData()) {
                $file = $form->get('imageFile')->getData();
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                try {
                    $file->move($this->uploadsDirectory, $fileName);
                    $user->setImagePath($fileName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'There was an error uploading your profile image.');
                    return $this->redirectToRoute('entreprise_profile');
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('entreprise_profile');
        }

        return $this->render('user/entreprise/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/entreprise/profile/change-password', name: 'entreprise_change_password')]
    public function changePassword(TokenStorageInterface $tokenStorage, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $token = $tokenStorage->getToken();
        if (!$token) {
            throw $this->createAccessDeniedException('Token not found or not authenticated.');
        }

        $user = $token->getUser();
        if (!$user instanceof Entreprise) {
            throw $this->createAccessDeniedException('Entreprise not found or not authenticated.');
        }
        $this->denyAccessUnlessGranted('ROLE_ENTREPRISE');

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $currentPassword = $formData['currentPassword'];
            $newPassword = $formData['password'];

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Current password is incorrect.');
                return $this->redirectToRoute('entreprise_change_password');
            }

            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $entityManager->flush();

            $this->addFlash('success', 'Password changed successfully!');
            return $this->redirectToRoute('entreprise_change_password');
        }

        return $this->render('user/entreprise/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
