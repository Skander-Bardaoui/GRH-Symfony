<?php

namespace App\Controller;

use App\Entity\Conge;
use App\Form\CongeType;
use App\Repository\CongeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CongeController extends AbstractController
{
    #[Route('/conge', name: 'conge_index')]
    public function index(CongeRepository $repo): Response
    {
        $conges = $repo->findBy(['employee' => $this->getUser()]);

        return $this->render('conge/index.html.twig', [
            'conges' => $conges,
        ]);
    }

    #[Route('/conge/new', name: 'conge_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $conge = new Conge();
        $form = $this->createForm(CongeType::class, $conge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conge->setEmployee($this->getUser());
            $conge->setStatus('pending');
            $em->persist($conge);
            $em->flush();

            return $this->redirectToRoute('conge_index');
        }

        return $this->render('conge/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/conge/{id}/edit', name: 'conge_edit', methods: ['POST'])]
    public function edit(Request $request, Conge $conge, EntityManagerInterface $em): Response
    {
        // Check if the current user owns this conge
        if ($conge->getEmployee() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot edit this leave request.');
        }

        // Check if conge can be edited (only pending requests can be edited)
        if ($conge->getStatus() !== 'pending') {
            $this->addFlash('error', 'Only pending leave requests can be edited.');
            return $this->redirectToRoute('conge_index');
        }

        // Update the conge with new data
        $startDate = new \DateTime($request->request->get('startDate'));
        $endDate = new \DateTime($request->request->get('endDate'));

        $conge->setStartDate($startDate);
        $conge->setEndDate($endDate);

        $em->flush();
        $this->addFlash('success', 'Leave request updated successfully.');

        return $this->redirectToRoute('conge_index');
    }

    #[Route('/conge/{id}/delete', name: 'conge_delete', methods: ['POST'])]
    public function delete(Request $request, Conge $conge, EntityManagerInterface $em): Response
    {
        // Check if the current user owns this conge
        if ($conge->getEmployee() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot delete this leave request.');
        }

        // Check if conge can be deleted (only pending requests can be deleted)
        if ($conge->getStatus() !== 'pending') {
            $this->addFlash('error', 'Only pending leave requests can be deleted.');
            return $this->redirectToRoute('conge_index');
        }

        if ($this->isCsrfTokenValid('delete'.$conge->getId(), $request->request->get('_token'))) {
            $em->remove($conge);
            $em->flush();
            $this->addFlash('success', 'Leave request deleted successfully.');
        }

        return $this->redirectToRoute('conge_index');
    }

    #[Route('/admin/conges', name: 'admin_conges')]
    public function adminIndex(CongeRepository $repo): Response
    {
        return $this->render('admin/conges.html.twig', [
            'conges' => $repo->findAll(),
        ]);
    }

    #[Route('/admin/conge/{id}/approve', name: 'conge_approve')]
    public function approve(Conge $conge, EntityManagerInterface $em): Response
    {
        $conge->setStatus('approved');
        $em->flush();
        return $this->redirectToRoute('admin_conges');
    }

    #[Route('/admin/conge/{id}/reject', name: 'conge_reject')]
    public function reject(Conge $conge, EntityManagerInterface $em): Response
    {
        $conge->setStatus('rejected');
        $em->flush();
        return $this->redirectToRoute('admin_conges');
    }
}