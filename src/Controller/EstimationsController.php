<?php

namespace App\Controller;

use App\Entity\Estimations;
use App\Form\EstimationsType;
use App\Repository\CollectsRepository;
use App\Repository\EstimationsRepository;
use App\Repository\OrganismsRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Toutes les estimations en fonction du collecteur
/**
 * @Route("admin/estimations")
 */
class EstimationsController extends AbstractController
{
    /**
     * @Route("/", name="estimations_index", methods={"GET"})
     * @param EstimationsRepository $eRepo
     * @param CollectsRepository $collectsRepository
     * @return Response
     */
    public function index(EstimationsRepository $eRepo, CollectsRepository $collectsRepository): Response
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
                    'isCollected' => 0 ],
                    ['estimationDate' => "DESC"]
                ),
                'pageTitle' => "Etape 1 : choix de l'estimation"
                ]);
        } else {
            return $this->render('estimations/index.html.twig', [
            'estimations' => $eRepo->findBy([], ['id' => "DESC"]),
            'pageTitle' => 'Toutes les estimations'
            ]);
        }
    }
// Pour l'admin  : estimations a collecter
    /**
     * @Route("/uncollected", name="estimations_uncollected_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @param EstimationsRepository $eRepo
     * @return Response
     */
    public function indexUncollected(EstimationsRepository $eRepo, CollectsRepository $collectsRepo): Response
    {
        $estimations = $eRepo->findByUncollected();

        return $this->render('estimations/index.html.twig', [
            'estimations' => $estimations,
            'pageTitle' => 'Estimations à collecter'
        ]);
    }
// Estimations collectées
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
            'pageTitle' => 'Estimations collectées'
        ]);
    }

// Estimations non présentée à la collecte ou non recu
    /**
     * @Route("/notCollected", name="estimations_notcollected_index")
     * @param EstimationsRepository $eRepo
     * @return Response
     */
    public function indexNotcollected(EstimationsRepository $eRepo)
    {
        $estimations = $eRepo->findByNotcollected();

        return $this->render('estimations/index.html.twig', [
            'estimations' => $estimations,
            'pageTitle' => 'non réceptionné'
        ]);
    }

// Estimations inachevées
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
            'pageTitle' => 'Estimations non validées'
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

        if ($this->getUser()->getRoles()[0] == "ROLE_ADMIN") {
            return $this->render('estimations/show.html.twig', [
                'estimation' => $estimation,
            ]);
        } elseif ($estimation->getUser() !== null && $estimation->getUser()->getOrganism() !== null) {
            if ($estimation->getUser()->getOrganism() === $this->getUser()->getOrganism()) {
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
// Permet d'effacer une estimation

    /**
     * @Route("/{id}/delete", name="estimations_delete")
     * @IsGranted("ROLE_ADMIN")
     * @param EntityManagerInterface $em
     * @param Estimations $estimation
     * @return Response
     */
    public function delete(EntityManagerInterface $em, Estimations $estimation): Response
    {
        $em->remove($estimation);
        $em->flush();

        return $this->redirectToRoute('estimations_index');
    }
}
