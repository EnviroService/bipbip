<?php

namespace App\Controller;

use App\Entity\Estimations;
use App\Form\EstimationsType;
use App\Repository\EstimationsRepository;
use App\Repository\OrganismsRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
        if ($this->getUser()->getRoles()[0] == 'ROLE_COLLECTOR') {
            $organismUsers = $this->getUser()->getOrganism()->getUsers()->getValues();
            $estimationIds = [];
            foreach ($organismUsers as $user) {
                $estimationUser = $user->getEstimations()->getValues();
                foreach ($estimationUser as $estimation) {
                    $estimationId = $estimation->getId();
                    $collected = $estimation->getIsCollected();
                    array_push($estimationIds, $estimationId, $collected);
                }
            }
            return $this->render('estimations/index.html.twig', [
                'estimations' => $eRepo->findBy(
                    ['id' => $estimationIds,
                     'isCollected' => 0]
                )]);
        } else {
            return $this->render('estimations/index.html.twig', [
            'estimations' => $eRepo->findAll()
            ]);
        }
    }

    /**
     * @Route("/uncollected", name="estimations_uncollected_index", methods={"GET"})
     * @param EstimationsRepository $eRepo
     * @return Response
     */
    public function indexUncollected(EstimationsRepository $eRepo): Response
    {
        $estimations = $eRepo->findByUncollected();
        return $this->render('estimations/index.html.twig', [
            'estimations' => $estimations,
        ]);
    }

    /**
     * @Route("/collected", name="estimations_collected_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @param EstimationsRepository $eRepo
     * @return Response
     */
    public function indexCollected(EstimationsRepository $eRepo): Response
    {
        $estimations = $eRepo->findByCollected();
        return $this->render('estimations/index.html.twig', [
            'estimations' => $estimations,
        ]);
    }

    /**
     * @Route("/unfinished", name="estimations_unfinished_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @param EstimationsRepository $eRepo
     * @return Response
     */
    public function indexUnfinished(EstimationsRepository $eRepo): Response
    {
        $estimations = $eRepo->findByUnfinished();
        return $this->render('estimations/index.html.twig', [
            'estimations' => $estimations,
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
        if ($estimation->getUser() !== null && $estimation->getUser()->getOrganism() !== null) {
            if (($this->getUser()->getRoles()[0] == "ROLE_ADMIN")
                || ($estimation->getUser()->getOrganism() === $this->getUser()->getOrganism())) {
                return $this->render('estimations/show.html.twig', [
                    'estimation' => $estimation,
                ]);
            }
        }

        $message = "Cette estimation ne vous est pas accessible";
        $this->addFlash('danger', $message);
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/{id}/edit", name="estimations_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Estimations $estimation
     * @return Response
     */
    public function edit(Request $request, Estimations $estimation): Response
    {
        if ($estimation->getUser() !== null && $estimation->getUser()->getOrganism() !== null) {
            if (($this->getUser()->getRoles()[0] == "ROLE_ADMIN")
                || ($estimation->getUser()->getOrganism() === $this->getUser()->getOrganism())) {
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
        }

        $message = "Cette estimation ne vous est pas accessible";
        $this->addFlash('danger', $message);
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/{id}", name="estimations_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
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
