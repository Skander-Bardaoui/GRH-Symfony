<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Form\RegistrationType;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\EmployeeAuthenticator;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        EmployeeRepository $employeeRepository,
        UserAuthenticatorInterface $userAuthenticator,
        EmployeeAuthenticator $authenticator
    ): Response {
        // Redirect if already logged in
        if ($this->getUser()) {
            return $this->redirectToRoute('app_employee');
        }

        $employee = new Employee();
        $form = $this->createForm(RegistrationType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if email already exists
            $existingEmployee = $employeeRepository->findOneBy(['email' => $employee->getEmail()]);
            
            if ($existingEmployee) {
                $this->addFlash('error', 'This email is already registered. Please login or use a different email.');
                return $this->redirectToRoute('app_register');
            }

            // Hash the password
            $employee->setPassword(
                $passwordHasher->hashPassword(
                    $employee,
                    $form->get('password')->getData()
                )
            );

            // Set default role
            $employee->setRoles(['ROLE_USER']);

            try {
                // Save to database
                $entityManager->persist($employee);
                $entityManager->flush();

                $this->addFlash('success', 'Registration successful! You are now logged in.');

                // Automatically authenticate the user after registration
                return $userAuthenticator->authenticateUser(
                    $employee,
                    $authenticator,
                    $request
                );
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred during registration. Please try again.');
                return $this->redirectToRoute('app_register');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}