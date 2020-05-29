<?php

namespace App\Controller;

use App\Entity\Boutique;
use App\Form\BoutiqueType;
use App\Repository\BoutiqueRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/boutique")
 */
class BoutiqueController extends AbstractController
{
    /**
     * @Route("/admin", name="boutique_index", methods={"GET"})
     * @param BoutiqueRepository $boutiqueRepository
     * @return Response
     */
    public function index(BoutiqueRepository $boutiqueRepository): Response
    {
        return $this->render('boutique/index.html.twig', [
            'boutiques' => $boutiqueRepository->findAll(),
        ]);
    }

    /**
     * @Route("/", name="boutique", methods={"GET"})
     * @param BoutiqueRepository $boutiqueRepository
     * @return Response
     */
    public function boutique(BoutiqueRepository $boutiqueRepository): Response
    {

        return $this->render('boutique/boutique.html.twig', [
            'phones' => $boutiqueRepository->findAll(),
            'promos' => $boutiqueRepository->findBy(['isPromo' => true])
        ]);
    }

    /**
     * @Route("/new", name="boutique_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $boutique = new Boutique();
        $form = $this->createForm(BoutiqueType::class, $boutique);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $boutique->setDateAjout(new DateTime('now'));
            $boutique->setIsSold(false);

            $entityManager->persist($boutique);
            $entityManager->flush();

            $image = $form->get('image')->getData();

            $extension = pathinfo($_FILES['boutique']['name']['image'], PATHINFO_EXTENSION);
            $name = $boutique->getBrand() . '_'
                . str_replace(' ', '_', $boutique->getModel()) . '_'
                . $boutique->getId();

            $directory = 'uploads/boutique/';
            $filename = $directory . $name . ".$extension";

            move_uploaded_file($image, $filename);

            $boutique->setImage($filename);
            $entityManager->persist($boutique);
            $entityManager->flush();

            return $this->redirectToRoute('boutique_index');
        }


        return $this->render('boutique/new.html.twig', [
            'boutique' => $boutique,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="boutique_show", methods={"GET"})
     * @param Boutique $boutique
     * @return Response
     */
    public function show(Boutique $boutique): Response
    {
        return $this->render('boutique/show.html.twig', [
            'boutique' => $boutique,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="boutique_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Boutique $boutique
     * @return Response
     */
    public function edit(Request $request, Boutique $boutique): Response
    {
        $form = $this->createForm(BoutiqueType::class, $boutique);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $image = $form->get('image')->getData();

            $extension = pathinfo($_FILES['boutique']['name']['image'], PATHINFO_EXTENSION);
            $name = $boutique->getBrand() . '_'
                . str_replace(' ', '_', $boutique->getModel()) . '_'
                . $boutique->getId();

            $directory = 'uploads/boutique/';
            $filename = $directory . $name . ".$extension";

            move_uploaded_file($image, $filename);

            $boutique->setImage($filename);
            $entityManager->persist($boutique);
            $entityManager->flush();

            return $this->redirectToRoute('boutique_index');
        }

        return $this->render('boutique/edit.html.twig', [
            'boutique' => $boutique,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="boutique_delete", methods={"DELETE"})
     * @param Request $request
     * @param Boutique $boutique
     * @return Response
     */
    public function delete(Request $request, Boutique $boutique): Response
    {
        if ($this->isCsrfTokenValid('delete'.$boutique->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($boutique);
            $entityManager->flush();
        }

        return $this->redirectToRoute('boutique_index');
    }
}
