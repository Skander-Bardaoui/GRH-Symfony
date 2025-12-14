<?php

namespace App\Controller;

use App\Entity\Reunion;
use App\Form\ReunionType;
use App\Repository\ReunionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReunionController extends AbstractController
{
    #[Route('/reunion', name: 'reunion_index')]
    public function index(ReunionRepository $repo): Response
    {
        $reunions = $repo->findBy(['employee' => $this->getUser()]);

        return $this->render('reunion/index.html.twig', [
            'reunions' => $reunions,
        ]);
    }

    #[Route('/reunion/new', name: 'reunion_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $reunion = new Reunion();
        $form = $this->createForm(ReunionType::class, $reunion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reunion->setEmployee($this->getUser());
            $reunion->setStatus('pending');

            $em->persist($reunion);
            $em->flush();

            return $this->redirectToRoute('reunion_index');
        }

        return $this->render('reunion/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/reunions', name: 'admin_reunion_index')]
    public function adminIndex(ReunionRepository $repo): Response
    {
        $reunions = $repo->findAll();

        return $this->render('admin/reunion_list.html.twig', [
            'reunions' => $reunions,
        ]);
    }

    #[Route('/admin/reunion/{id}/confirm', name: 'reunion_confirm')]
    public function confirm(Reunion $reunion, EntityManagerInterface $em): Response
    {
        $reunion->setStatus('confirmed');
        $em->flush();

        return $this->redirectToRoute('admin_reunion_index');
    }

    #[Route('/admin/reunion/{id}/cancel', name: 'reunion_cancel')]
    public function cancel(Reunion $reunion, EntityManagerInterface $em): Response
    {
        $reunion->setStatus('cancelled');
        $em->flush();

        return $this->redirectToRoute('admin_reunion_index');
    }
}
