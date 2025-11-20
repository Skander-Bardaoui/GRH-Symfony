<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Form\EmployeeType;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/employee')]
class AdminEmployeeController extends AbstractController
{
    #[Route('/', name: 'admin_employee_index')]
    public function index(EmployeeRepository $repo): Response
    {
        return $this->render('admin/employee/index.html.twig', [
            'employees' => $repo->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_employee_new')]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $employee = new Employee();
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $employee->setPassword($passwordHasher->hashPassword($employee, $employee->getPassword()));
            $em->persist($employee);
            $em->flush();
            $this->addFlash('success', 'Employee created successfully.');
            return $this->redirectToRoute('admin_employee_index');
        }

        return $this->render('admin/employee/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_employee_edit')]
    public function edit(Employee $employee, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($employee->getPassword()) {
                $employee->setPassword($passwordHasher->hashPassword($employee, $employee->getPassword()));
            }
            $em->flush();
            $this->addFlash('success', 'Employee updated successfully.');
            return $this->redirectToRoute('admin_employee_index');
        }

        return $this->render('admin/employee/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_employee_delete')]
    public function delete(Employee $employee, EntityManagerInterface $em): Response
    {
        $em->remove($employee);
        $em->flush();
        $this->addFlash('success', 'Employee deleted successfully.');
        return $this->redirectToRoute('admin_employee_index');
    }
}
