<?php

namespace App\Controller;

use App\Entity\Collects;
use App\Form\CollectsType;
use App\Repository\CollectsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/collect")
 */
class CollectsController extends AbstractController
{

    // Permet de lister les collectes

    /**
     * @Route("/", name="collects_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     * @param CollectsRepository $collectsRepository
     * @return Response
     */
    public function index(CollectsRepository $collectsRepository): Response
    {
        $collectsValid = $collectsRepository->findByDateValid();
        return $this->render('collects/index.html.twig', [
            'collects' => $collectsValid
        ]);
    }
// Permet a l'admin d'ajouter une collecte

    /**
     * @Route("/new", name="collects_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $collect = new Collects();
        $form = $this->createForm(CollectsType::class, $collect);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $dateDebut = $data->getDateCollect();
            $dateDeFin = $data->getDateEndCollect();

            if ($dateDebut != null && $dateDeFin != null) {
                $timestampDebut =
                    date_create_from_format(
                        'd/m/Y H:i',
                        date_format($dateDebut, 'd/m/Y H:i')
                    )->getTimestamp();
                $timestampFin =
                    date_create_from_format(
                        'd/m/Y H:i',
                        date_format($dateDeFin, 'd/m/Y H:i')
                    )->getTimestamp();
                $diff = $timestampFin - $timestampDebut;

                if (date_diff($dateDebut, $dateDeFin)->invert === 1) {
                    $error = "L'heure de fin ne doit pas être inférieur à l'heure de début";

                    return $this->render('collects/new.html.twig', [
                        'error' => $error,
                        'collect' => $collect,
                        'form' => $form->createView(),
                    ]);
                } elseif ($diff > 72000) {
                    $error = "Le temp maximum entre début et fin est de 20h";

                    return $this->render('collects/new.html.twig', [
                        'error' => $error,
                        'collect' => $collect,
                        'form' => $form->createView(),
                    ]);
                }

                $entityManager->persist($collect);
                $entityManager->flush();

                return $this->redirectToRoute('collects_index');
            }
        }

        return $this->render('collects/new.html.twig', [
            'collect' => $collect,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="collects_show", methods={"GET"})
     * @param Collects $collect
     * @return Response
     */
    public function show(Collects $collect): Response
    {
        return $this->render('collects/show.html.twig', [
            'collect' => $collect,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="collects_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Collects $collect): Response
    {
        $form = $this->createForm(CollectsType::class, $collect);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('collects_index');
        }

        return $this->render('collects/edit.html.twig', [
            'collect' => $collect,
            'form' => $form->createView(),
        ]);
    }
// Permet a l'admin de supprimer des collectes
    /**
     * @Route("/{id}", name="collects_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Collects $collect): Response
    {
        if ($this->isCsrfTokenValid('delete'.$collect->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($collect);
            $entityManager->flush();
        }

        return $this->redirectToRoute('collects_index');
    }
}
