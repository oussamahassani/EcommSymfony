<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Commande;
use App\Form\CreateAccountType;
use App\Form\LoginType;
use App\Form\AdminLoginType;
use App\Form\UserType;
use App\Form\EntrepriseType;
use App\Form\SearchType;
use App\Form\ProfileType;
use App\Form\ChangePasswordType;
use App\Repository\CommandeRepository;
use App\Repository\FavorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ArticleRepository; 
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




final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash('error', 'Email ou mot de passe invalide. Veuillez réessayer.');
        }
        $lastUsername = $authenticationUtils->getLastUsername();
        $form = $this->createForm(LoginType::class);
        return $this->render('user/login.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    #[Route('/admin-login', name: 'admin_login')]
    public function loginAdmin(Request $request, AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, UserPasswordHasherInterface $passwordHasher): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash('error', 'Email ou mot de passe invalide. Veuillez réessayer.');
        }
        $lastUsername = $authenticationUtils->getLastUsername();
        $form = $this->createForm(AdminLoginType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->get('_username')->getData();
            $password = $form->get('_password')->getData();
            $user = $entityManager->getRepository(User::class)->findUserWithRoleAndMenusByEmail($username);

            if (!$user) {
                $this->addFlash('error', 'Email ou mot de passe invalide. Veuillez réessayer.');
                return $this->redirectToRoute('admin_login');
            }

            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                $this->addFlash('error', 'Admin non existant');
                return $this->redirectToRoute('admin_login');
            }

            if (!$passwordHasher->isPasswordValid($user, $password)) {
                $this->addFlash('error', 'Email ou mot de passe invalide. Veuillez réessayer.');
                return $this->redirectToRoute('admin_login');
            }

            $tokenStorage->setToken(null);
            $token = new UsernamePasswordToken($user, 'admin', $user->getRoles());
            $tokenStorage->setToken($token);
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('user/admin/login.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/admin/logoutAdmin', name: 'admin_logout')]
    public function logoutAdmin(): void
    {
        // This method can remain empty - it will be intercepted by the logout handler
    }

    #[Route('/create-account', name: 'create_account')]
    public function createAccount(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(CreateAccountType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the plain password
            $user->setPassword($passwordEncoder->hashPassword($user, $user->getPassword()));
            // Set the role to 'client'
            $role = $entityManager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_CLIENT']);
            if (!$role) {
                $role = new Role();
                $role->setName('ROLE_CLIENT');
                $entityManager->persist($role);
            }
            $user->setRole($role);
            $user->setCreatedAt(new \DateTimeImmutable());
            try {
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Account created successfully!');
                return $this->redirectToRoute('login');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'A user with this email address already exists.');
                return $this->redirectToRoute('create_account');
            }
        }
        return $this->render('user/client/create_account.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('user/admin/dashboard.html.twig', [
            // Pass any data you need to the template
        ]);
    }

    #[Route('/admin/listAdmins', name: 'list_admin')]
    public function listAdmins(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $roleAdmin = $entityManager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_ADMIN']);
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $query = $form->get('query')->getData();
            if ($query) {
                $admins = $this->filterAdmins($query, $entityManager, $roleAdmin);
            } else {
                $admins = $roleAdmin ? $entityManager->getRepository(User::class)->findBy(['role' => $roleAdmin]) : [];
            }
        } else {
            $admins = $roleAdmin ? $entityManager->getRepository(User::class)->findBy(['role' => $roleAdmin]) : [];
        }

        $admin = new User();
        // Create the form
        $formx = $this->createForm(UserType::class, $admin);
        $formx->handleRequest($request);

        if ($formx->isSubmitted() && $formx->isValid()) {
            // Check if a user with the same email already exists
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $admin->getEmail()]);
            if ($existingUser) {
                $this->addFlash('error', 'A user with this email address already exists.');
                return $this->redirectToRoute('list_admin');
            }

            // Generate password based on phone number
            $generatedPassword = $admin->getPhone() . '.admin';
            if (empty($generatedPassword)) {
                $this->addFlash('error', 'Phone number is required to generate the password.');
                return $this->redirectToRoute('list_admin');
            }

            // Hash the generated password
            $hashedPassword = $passwordHasher->hashPassword($admin, $generatedPassword);
            $admin->setPassword($hashedPassword);

            // Set the role and creation date
            $role = $entityManager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_ADMIN']);
            $admin->setRole($role);
            $admin->setCreatedAt(new \DateTimeImmutable());

            // Persist the admin user
            $entityManager->persist($admin);
            $entityManager->flush();

            // Add a success flash message
            $this->addFlash('success', 'Admin created successfully with the password: ' . $generatedPassword);
            return $this->redirectToRoute('list_admin');
        }

        return $this->render('user/admin/list_Admins.html.twig', [
            'formx' => $formx->createView(),
            'form' => $form->createView(),
            'admins' => $admins,
        ]);
    }

    private function filterAdmins(string $query, EntityManagerInterface $entityManager, Role $roleAdmin): array
    {
        return $entityManager->getRepository(User::class)->createQueryBuilder('u')
            ->where('u.role = :role')
            ->andWhere('u.name LIKE :query OR u.last_name LIKE :query OR u.email LIKE :query OR u.phone LIKE :query')
            ->setParameter('role', $roleAdmin)
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }

    #[Route('/admin/deleteAdmin/{id}', name: 'delete_admin')]
    public function deleteAdmin(EntityManagerInterface $entityManager, int $id): Response
    {
        $admin = $entityManager->getRepository(User::class)->find($id);
        if (!$admin) {
            throw $this->createNotFoundException('User non existant');
        }
        $entityManager->remove($admin);
        $entityManager->flush();
        return $this->redirectToRoute('list_admin');
    }

    #[Route('/admin/editAdmin/{id}', name: 'edit_admin')]
    public function editAdmin(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $admin = $entityManager->getRepository(User::class)->find($id);
        if (!$admin) {
            throw $this->createNotFoundException('Admin not found');
        }

        $form = $this->createForm(UserType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Admin updated successfully!');
            return $this->redirectToRoute('list_admin');
        }

        return $this->render('user/admin/edit_admin.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/listClients', name: 'list_client')]
    public function listClients(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $roleClient = $entityManager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_CLIENT']);
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $query = $form->get('query')->getData();
            if ($query) {
                $clients = $this->filterClients($query, $entityManager, $roleClient);
            } else {
                $clients = $roleClient ? $entityManager->getRepository(User::class)->findBy(['role' => $roleClient]) : [];
            }
        } else {
            $clients = $roleClient ? $entityManager->getRepository(User::class)->findBy(['role' => $roleClient]) : [];
        }

        $client = new User();
        // Create the form
        $formx = $this->createForm(UserType::class, $client);
        $formx->handleRequest($request);

        if ($formx->isSubmitted() && $formx->isValid()) {
            // Check if a user with the same email already exists
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $client->getEmail()]);
            if ($existingUser) {
                $this->addFlash('error', 'A user with this email address already exists.');
                return $this->redirectToRoute('list_client');
            }

            // Generate password based on phone number
            $generatedPassword = $client->getPhone() . '.client';
            if (empty($generatedPassword)) {
                $this->addFlash('error', 'Phone number is required to generate the password.');
                return $this->redirectToRoute('list_client');
            }

            // Hash the generated password
            $hashedPassword = $passwordHasher->hashPassword($client, $generatedPassword);
            $client->setPassword($hashedPassword);

            // Set the role and creation date
            $role = $entityManager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_CLIENT']);
            $client->setRole($role);
            $client->setCreatedAt(new \DateTimeImmutable());

            // Persist the admin user
            $entityManager->persist($client);
            $entityManager->flush();

            // Add a success flash message
            $this->addFlash('success', 'Client created successfully with the password: ' . $generatedPassword);
            return $this->redirectToRoute('list_client');
        }

        return $this->render('user/admin/list_Clients.html.twig', [
            'formx' => $formx->createView(),
            'form' => $form->createView(),
            'clients' => $clients,
        ]);
    }

    private function filterClients(string $query, EntityManagerInterface $entityManager, Role $roleClient): array
    {
        return $entityManager->getRepository(User::class)->createQueryBuilder('u')
            ->where('u.role = :role')
            ->andWhere('u.name LIKE :query OR u.last_name LIKE :query OR u.email LIKE :query OR u.phone LIKE :query')
            ->setParameter('role', $roleClient)
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }

    #[Route('/admin/deleteClient/{id}', name: 'delete_client')]
    public function deleteClient(EntityManagerInterface $entityManager, int $id): Response
    {
        $client = $entityManager->getRepository(User::class)->find($id);
        if (!$client) {
            throw $this->createNotFoundException('User non existant');
        }
        $entityManager->remove($client);
        $entityManager->flush();
        return $this->redirectToRoute('list_client');
    }

    #[Route('/admin/editClient/{id}', name: 'edit_client')]
    public function editClient(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $client = $entityManager->getRepository(User::class)->find($id);
        if (!$client) {
            throw $this->createNotFoundException('Client not found');
        }
        $form = $this->createForm(UserType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Client updated successfully!');
            return $this->redirectToRoute('list_client');
        }

        return $this->render('user/admin/edit_client.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/listEntreprises', name: 'list_entreprise')]
    public function listEntreprises(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $roleEntreprise = $entityManager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_ENTREPRISE']);
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $query = $form->get('query')->getData();
            if ($query) {
                $entreprises = $this->filterEntreprises($query, $entityManager, $roleEntreprise);
            } else {
                $entreprises = $roleEntreprise ? $entityManager->getRepository(Entreprise::class)->findBy(['role' => $roleEntreprise]) : [];
            }
        } else {
            $entreprises = $roleEntreprise ? $entityManager->getRepository(Entreprise::class)->findBy(['role' => $roleEntreprise]) : [];
        }

        $entreprise = new Entreprise();
        // Create the form
        $formx = $this->createForm(EntrepriseType::class, $entreprise);
        $formx->handleRequest($request);

        if ($formx->isSubmitted() && $formx->isValid()) {
            // Check if a user with the same email already exists
            $existingUser = $entityManager->getRepository(Entreprise::class)->findOneBy(['email' => $entreprise->getEmail()]);
            if ($existingUser) {
                $this->addFlash('error', 'A user with this email address already exists.');
                return $this->redirectToRoute('list_entreprise');
            }

            // Generate password based on phone number
            $generatedPassword = $entreprise->getPhone() . '.entreprise';
            if (empty($generatedPassword)) {
                $this->addFlash('error', 'Phone number is required to generate the password.');
                return $this->redirectToRoute('list_entreprise');
            }

            // Hash the generated password
            $hashedPassword = $passwordHasher->hashPassword($entreprise, $generatedPassword);
            $entreprise->setPassword($hashedPassword);

            // Set the role and creation date
            $role = $entityManager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_ENTREPRISE']);
            $entreprise->setRole($role);
            $entreprise->setCreatedAt(new \DateTimeImmutable());

            // Persist the admin user
            $entityManager->persist($entreprise);
            $entityManager->flush();

            // Add a success flash message
            $this->addFlash('success', 'Entreprise created successfully with the password: ' . $generatedPassword);
            return $this->redirectToRoute('list_entreprise');
        }

        return $this->render('user/admin/list_Entreprises.html.twig', [
            'formx' => $formx->createView(),
            'form' => $form->createView(),
            'entreprises' => $entreprises,
        ]);
    }

    private function filterEntreprises(string $query, EntityManagerInterface $entityManager, Role $roleEntreprise): array
    {
        return $entityManager->getRepository(Entreprise::class)->createQueryBuilder('u')
            ->where('u.role = :role')
            ->andWhere('u.company_name LIKE :query OR u.email LIKE :query OR u.phone LIKE :query')
            ->setParameter('role', $roleEntreprise)
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }

    #[Route('/admin/deleteEntreprise/{id}', name: 'delete_entreprise')]
    public function deleteEntreprise(EntityManagerInterface $entityManager, int $id): Response
    {
        $entreprise = $entityManager->getRepository(Entreprise::class)->find($id);
        if (!$entreprise) {
            throw $this->createNotFoundException('User non existant');
        }
        $entityManager->remove($entreprise);
        $entityManager->flush();
        return $this->redirectToRoute('list_entreprise');
    }

    #[Route('/admin/editEntreprise/{id}', name: 'edit_entreprise')]
    public function editEntreprise(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $entreprise = $entityManager->getRepository(Entreprise::class)->find($id);
        if (!$entreprise) {
            throw $this->createNotFoundException('Entreprise not found');
        }
        $form = $this->createForm(EntrepriseType::class, $entreprise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Entreprise updated successfully!');
            return $this->redirectToRoute('list_entreprise');
        }

        return $this->render('user/admin/edit_entreprise.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private $uploadsDirectory;

    public function __construct(string $uploadsDirectory)
    {
        $this->uploadsDirectory = $uploadsDirectory;
    }

    #[Route('/admin/profile', name: 'admin_profile')]
    public function profileAdmin(TokenStorageInterface $tokenStorage, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $token = $tokenStorage->getToken();
        if (!$token) {
            throw $this->createAccessDeniedException('Token not found or not authenticated.');
        }
        $user = $token->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('User not found or not authenticated.');
        }
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('imageFile')->getData()) {
                $file = $form->get('imageFile')->getData();
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                try {
                    $file->move($this->uploadsDirectory, $fileName);
                    $user->setImagePath($fileName);
                } catch (FileException $e) {
                    // Handle file upload error
                    $this->addFlash('error', 'There was an error uploading your profile image.');
                    return $this->redirectToRoute('admin_profile');
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('admin_profile');
        }

        return $this->render('user/admin/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/profile/change-password', name: 'admin_change_password')]
    public function changePasswordAdmin(TokenStorageInterface $tokenStorage, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $token = $tokenStorage->getToken();
        if (!$token) {
            throw $this->createAccessDeniedException('Token not found or not authenticated.');
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('User not found or not authenticated.');
        }
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $currentPassword = $formData['currentPassword'];
            $newPassword = $formData['password'];

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Current password is incorrect.');
                return $this->redirectToRoute('admin_change_password');
            }

            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $entityManager->flush();

            $this->addFlash('success', 'Password changed successfully!');
            return $this->redirectToRoute('admin_change_password');
        }

        return $this->render('user/admin/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile', name: 'profile')]
    public function profile(TokenStorageInterface $tokenStorage, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $token = $tokenStorage->getToken();
        if (!$token) {
            throw $this->createAccessDeniedException('Token not found or not authenticated.');
        }
        $user = $token->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('User not found or not authenticated.');
        }
        // Create the form
        $form = $this->createForm(ProfileType::class, $user);
        // Handle form submission
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('imageFile')->getData()) {
                $file = $form->get('imageFile')->getData();
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                try {
                    $file->move($this->uploadsDirectory, $fileName);
                    $user->setImagePath($fileName);
                } catch (FileException $e) {
                    // Handle file upload error
                    $this->addFlash('error', 'There was an error uploading your profile image.');
                    return $this->redirectToRoute('profile');
                }
            }
            $entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('profile');
        }
        // Pass the form to the template
        return $this->render('user/client/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/change-password', name: 'change_password')]
    public function changePassword(TokenStorageInterface $tokenStorage, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $token = $tokenStorage->getToken();
        if (!$token) {
            throw $this->createAccessDeniedException('Token not found or not authenticated.');
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('User not found or not authenticated.');
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $currentPassword = $formData['currentPassword'];
            $newPassword = $formData['password'];

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Current password is incorrect.');
                return $this->redirectToRoute('change_password');
            }

            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $entityManager->flush();

            $this->addFlash('success', 'Password changed successfully!');
            return $this->redirectToRoute('change_password');
        }

        return $this->render('user/client/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/admin/statistique/{page}', name: 'app_statistique', defaults: ['page' => 1] ,  requirements: ['page' => '\d+'])]
    public function stat(
        CommandeRepository $commandeRepository,
        ArticleRepository $articleRepository,
        FavorieRepository $favorieRepository,
        int $page = 1
    ): Response {
        // Récupérer les statistiques des ventes par produit
        $stats = $commandeRepository->countSalesByProduct();

        // Extraire les noms des produits et le nombre de ventes pour Chart.js
        $productNoms = array_map(fn($stat) => $stat['nom'], $stats);
        $sales = array_map(fn($stat) => $stat['sales'], $stats);

        // Récupérer les quantités totales des produits
        $quantites = $articleRepository->getTotalQuantitiesByProduct();

        // Extraire les noms des produits et les quantités totales pour le diagramme circulaire
        $productNames = array_map(fn($product) => $product['nom'], $quantites);
        $quantities = array_map(fn($product) => $product['quantitestock'], $quantites);

        // Récupérer les statistiques des paiements par mode (carte et espèces)
        $paymentStats = $commandeRepository->countPaymentsByMode();
        $cardPayments = $paymentStats['card'] ?? 0;
        $cashPayments = $paymentStats['especes'] ?? 0;

        // Récupérer les produits en rupture de stock (stock = 0)
        $outOfStockProducts = $articleRepository->findBy(['quantitestock' => 0]);
        $outOfStockProductNames = array_map(fn($product) => $product->getNom(), $outOfStockProducts);

        // Récupérer les données des favoris (clients et articles)
        $favoriteData = $favorieRepository->findFavoriteClientsAndArticles();

        // Grouper les articles favoris par client
        $groupedFavoriteData = [];
        foreach ($favoriteData as $favorite) {
            $clientId = $favorite['clientId'];
            if (!isset($groupedFavoriteData[$clientId])) {
                $groupedFavoriteData[$clientId] = [
                    'clientName' => $favorite['clientName'] . ' ' . $favorite['clientLastName'],
                    'articles' => []
                ];
            }
            $groupedFavoriteData[$clientId]['articles'][] = $favorite['articleName'];
        }

        // Calculer le nombre d'articles favoris par client
        $clientFavoriteCounts = [];
        foreach ($groupedFavoriteData as $clientId => $clientData) {
            $clientFavoriteCounts[$clientData['clientName']] = count($clientData['articles']);
        }

        // Trier les clients par nombre d'articles favoris (ordre décroissant)
        arsort($clientFavoriteCounts);

        // Convertir les données en tableaux pour Chart.js
        $clientNames = array_keys($clientFavoriteCounts);
        $clientFavoriteCountsData = array_values($clientFavoriteCounts);

        // Calculer le nombre d'occurrences de chaque produit dans les favoris
        $productOccurrences = [];
        foreach ($favoriteData as $favorite) {
            $productName = $favorite['articleName'];
            if (!isset($productOccurrences[$productName])) {
                $productOccurrences[$productName] = 0;
            }
            $productOccurrences[$productName]++;
        }

        // Trier les occurrences par ordre décroissant
        arsort($productOccurrences);

        // Convertir les occurrences en tableaux pour Chart.js
        $favoriteProductNames = array_keys($productOccurrences);
        $favoriteProductCounts = array_values($productOccurrences);

        return $this->render('user/admin/stat.html.twig', [
            'productNoms' => json_encode($productNoms),
            'sales' => json_encode($sales),
            'productNames' => json_encode($productNames),
            'quantities' => json_encode($quantities),
            'card' => $cardPayments,
            'especes' => $cashPayments,
            'outOfStockProductNames' => json_encode($outOfStockProductNames),
            'groupedFavoriteData' => $groupedFavoriteData, // Données groupées des favoris
            'favoriteProductNames' => json_encode($favoriteProductNames), // Noms des produits favoris
            'favoriteProductCounts' => json_encode($favoriteProductCounts), // Nombre d'occurrences des produits favoris
            'clientNames' => json_encode($clientNames), // Noms des clients
            'clientFavoriteCountsData' => json_encode($clientFavoriteCountsData), // Nombre d'articles favoris par client
            'productOccurrences' => $productOccurrences, // Occurrences des produits dans les favoris
            'currentPage' => $page, // Page actuelle
        ]);
    }


        
}
