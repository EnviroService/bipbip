<?php

namespace App\Controller;

use App\Entity\FAQ;
use App\Form\FAQType;
use App\Repository\CategoryRepository;
use App\Repository\FAQRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/faq")
 */
class FAQController extends AbstractController
{

    /**
     * @param CategoryRepository $categoryRepo
     * @return Response
     * @Route("/", name="faq_index")
     */
    public function index(categoryRepository $categoryRepo) : Response
    {
        $categories = $categoryRepo->findAll();

        return $this->render('faq/index.html.twig', [
            'categories' => $categories]);
    }

    /**
     * @param FAQRepository $faqRepo
     * @param CategoryRepository $categoryRepo
     * @return Response
     * @Route("/category", name="faq_category")
     */
    public function categoryFaq(FAQRepository $faqRepo, categoryRepository $categoryRepo) : Response
    {
        $faqContent = $faqRepo->findAll();
        return $this->render('faq/category.html.twig', [
            'faqContent' => $faqContent]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/new", name="faq_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fAQ = new FAQ();
        $form = $this->createForm(FAQType::class, $fAQ);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($fAQ);
            $entityManager->flush();

            return $this->redirectToRoute('faq_index');
        }

        return $this->render('faq/new.html.twig', [
            'f_a_q' => $fAQ,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="faq_show", methods={"GET"})
     * @param FAQ $fAQ
     * @return Response
     */
    public function show(FAQ $fAQ): Response
    {
        return $this->render('faq/show.html.twig', [
            'f_a_q' => $fAQ,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="faq_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param FAQ $fAQ
     * @return Response
     */
    public function edit(Request $request, FAQ $fAQ): Response
    {
        $form = $this->createForm(FAQType::class, $fAQ);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Modification effectuée avec succès');
            return $this->redirectToRoute('admin_faq_index');
        }

        return $this->render('faq/edit.html.twig', [
            'faq' => $fAQ,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="faq_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param FAQ $fAQ
     * @return Response
     */
    public function delete(Request $request, FAQ $fAQ): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fAQ->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($fAQ);
            $entityManager->flush();
        }

        return $this->redirectToRoute('faq_index');
    }
}
