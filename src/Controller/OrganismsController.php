<?php

namespace App\Controller;

use App\Entity\Collects;
use App\Entity\Organisms;
use App\Form\OrganismsType;
use App\Repository\OrganismsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

/**
 * @Route("/organisms")
 */
class OrganismsController extends AbstractController
{
    /**
     * @Route("/", name="organisms_index", methods={"GET"})
     * @param OrganismsRepository $organismsRepository
     * @return Response
     */
    public function index(OrganismsRepository $organismsRepository): Response
    {
        return $this->render('organisms/index.html.twig', [
            'organisms' => $organismsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="organisms_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $organism = new Organisms();
        $form = $this->createForm(OrganismsType::class, $organism);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($organism);
            $entityManager->flush();

            return $this->redirectToRoute('adminadmin_organisms_index');
        }

        return $this->render('organisms/new.html.twig', [
            'organism' => $organism,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="organisms_show", methods={"GET"})
     * @param Organisms $organism
     * @return Response
     */
    public function show(Organisms $organism): Response
    {
        return $this->render('organisms/show.html.twig', [
            'organism' => $organism,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="organisms_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
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
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Organisms $organism
     * @return Response
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

    /**
     * @Route("/{id}/addCollect", name="addCollect")
     * @param Organisms $organisms
     * @param EntityManagerInterface $em
     * @return Response
     * @throws \Exception
     */
    public function createCollect(Organisms $organisms, EntityManagerInterface $em)
    {
        if (isset($_POST['date'])) {
            $date = $_POST['date'];
            $collect = new Collects();
            $dateCollect = new DateTime($date);
            $hour = substr($_POST['heure'], 0, 2);
            $minute = substr($_POST['heure'], -2, 2);
            $dateCollect->setTime(intval($hour), intval($minute));
            $collect->setDateCollect($dateCollect);
            $collect->setCollector($organisms);
            $organisms->addCollect($collect);
            $em->persist($collect);
            $em->flush();

            $this->addFlash("success", "Votre collecte a bien été enregistrée.");
        }
        return $this->render('organisms/createCollect.html.twig');
    }
}
