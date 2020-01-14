<?php

namespace App\Controller;

use App\Entity\Estimations;
use App\Form\EstimationsType;
use App\Repository\EstimationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/estimations")
 */
class EstimationsController extends AbstractController
{
    /**
     * @Route("/", name="estimations_index", methods={"GET"})
     * @param EstimationsRepository $eRepo
     * @return Response
     */
    public function index(EstimationsRepository $eRepo): Response
    {
        return $this->render('estimations/index.html.twig', [
            'estimations' => $eRepo->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="estimations_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $estimation = new Estimations();
        $form = $this->createForm(EstimationsType::class, $estimation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($estimation);
            $entityManager->flush();

            return $this->redirectToRoute('estimations_index');
        }

        return $this->render('estimations/new.html.twig', [
            'estimation' => $estimation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="estimations_show", methods={"GET"})
     * @param Estimations $estimation
     * @return Response
     */
    public function show(Estimations $estimation): Response
    {
        return $this->render('estimations/show.html.twig', [
            'estimation' => $estimation,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="estimations_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Estimations $estimation
     * @return Response
     */
    public function edit(Request $request, Estimations $estimation): Response
    {
        $form = $this->createForm(EstimationsType::class, $estimation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('estimations_index');
        }

        return $this->render('estimations/edit.html.twig', [
            'estimation' => $estimation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="estimations_delete", methods={"DELETE"})
     * @param Request $request
     * @param Estimations $estimation
     * @return Response
     */
    public function delete(Request $request, Estimations $estimation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$estimation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($estimation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('estimations_index');
    }
}
