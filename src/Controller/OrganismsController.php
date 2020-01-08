<?php

namespace App\Controller;

use App\Entity\Organisms;
use App\Form\OrganismsType;
use App\Repository\OrganismsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/organisms")
 */
class OrganismsController extends AbstractController
{
    /**
     * @Route("/", name="organisms_index", methods={"GET"})
     */
    public function index(OrganismsRepository $organismsRepository): Response
    {
        return $this->render('organisms/index.html.twig', [
            'organisms' => $organismsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="organisms_new", methods={"GET","POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $organism = new Organisms();
        $form = $this->createForm(OrganismsType::class, $organism);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($organism);
            $entityManager->flush();

            return $this->redirectToRoute('organisms_index');
        }

        return $this->render('organisms/new.html.twig', [
            'organism' => $organism,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="organisms_show", methods={"GET"})
     */
    public function show(Organisms $organism): Response
    {
        return $this->render('organisms/show.html.twig', [
            'organism' => $organism,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="organisms_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Organisms $organism
     * @return Response
     */
    public function edit(Request $request, Organisms $organism): Response
    {
        $form = $this->createForm(OrganismsType::class, $organism);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('organisms_index');
        }

        return $this->render('organisms/edit.html.twig', [
            'organism' => $organism,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="organisms_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Organisms $organism): Response
    {
        if ($this->isCsrfTokenValid('delete'.$organism->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($organism);
            $entityManager->flush();
        }

        return $this->redirectToRoute('organisms_index');
    }
}
