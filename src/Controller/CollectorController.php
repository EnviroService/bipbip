<?php

namespace App\Controller;

use App\Entity\Estimations;
use App\Form\CollectUserType;
use App\Form\ImeiType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("collector")
 */

class CollectorController extends AbstractController
{

    /**
     * @Route("/verify/estim/{id}", name="verifyEstim", methods={"GET","POST"})
     * @IsGranted("ROLE_COLLECTOR")
     * @param Request $request
     * @param Estimations $estimation
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function verifyEstim(Request $request, Estimations $estimation, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ImeiType::class, $estimation);
        $form->handleRequest($request);
        $id = $estimation->getId();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $imei = $data->getImei();
            $estimation->setImei($imei);

            $em->persist($estimation);
            $em->flush();

            return $this->redirectToRoute('verifyEstim', [
                'id' => $id,
            ]);
        }

        return $this->render('estimations/editEstim.html.twig', [
            'estimation' => $estimation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/user/{id}", name="verifyUser", methods={"GET","POST"})
     * @IsGranted("ROLE_COLLECTOR")
     * @param Request $request
     * @param Estimations $estimation
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function verifyUser(
        Request $request,
        Estimations $estimation,
        EntityManagerInterface $em
    ): Response {
        $user = $estimation->getUser();
        $form = $this->createForm(CollectUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $user) {
            $data = $form-> getData();
            $user->setLastname($data['lastname'])
                ->setFirstname($data['firstname'])
                ->setEmail($data['email'])
                ->setPhoneNumber($data['phoneNumber'])
                ->setAddress($data['address'])
                ->setPostCode($data['postCode'])
                ->setCity($data['city']);

            $em->persist($user);
            $em->flush();
        }

        return $this->render('bdc/editUser.html.twig', [
            'estimation' => $estimation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/capture/{id}", name="takePhoto")
     * @IsGranted("ROLE_COLLECTOR")
     * @param Estimations $estimation
     * @param EntityManagerInterface $em
     * @return Response
     */
    // route to take a photo of the Identity Card
    public function takePhoto(Estimations $estimation, EntityManagerInterface $em)
    {
        if (isset($_POST['submit'])) {
            if (!$estimation->getUser()) {
                $message = "Cette estimation n'est pas liée à un utilisateur";
                $this->addFlash('danger', $message);
                return $this->redirectToRoute('home');
            }
            // is the file exist ?
            $tmpFilePath = $_FILES['upload']['tmp_name'];
            if (!is_uploaded_file($tmpFilePath)) {
                $error = 'Le fichier est introuvable';
                $this->addFlash('danger', $error);
                return $this->render('bdc/takePhoto.html.twig', [
                    'estimation' => $estimation,
                    ]);
            }
            //save the url and the file
            if (!empty($estimation->getUser())) {
                $lastname = $estimation->getUser()->getLastname();
                $firstname = $estimation->getUser()->getFirstname();
            } else {
                $lastname = "anonyme";
                $firstname = "anonyme";
            }
            $extension = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);
            $filename = 'E' . $estimation->getId() . '-' . $lastname . '-' . $firstname . '.' . $extension;
            $filePath = "uploads/CI/$filename";

            if (move_uploaded_file($tmpFilePath, $filePath)) {
                $message = 'Merci, la photo a été enregistrée';
                $this->addFlash('success', $message);
            } else {
                $error = 'Merci de créer un dossier uploads/CI/';
                $this->addFlash('danger', $error);
            }
            // Validation of isValidatedCi in DB
            $estimation->setIsValidatedCi(true);
            $em->persist($estimation);
            $em->flush();
            return $this->redirectToRoute('bdc_show', [
                'estimation' => $estimation,
                'id' => $estimation->getId(),
                ]);
        }
        return $this->render('bdc/takePhoto.html.twig', [
            'estimation' => $estimation,
        ]);
    }

    /**
     * @Route("/show/{id}", name="bdc_show")
     * @IsGranted("ROLE_COLLECTOR")
     * @param Estimations $estimation
     * @return Response
     */
    // route to show an estimation
    public function show(Estimations $estimation)
    {
        if ($this->getUser()->getRoles()[0] == "ROLE_ADMIN") {
            return $this->render('bdc/show.html.twig', [
                'estimation' => $estimation,
            ]);
        } elseif ($estimation->getUser() !== null && $estimation->getUser()->getOrganism() !== null) {
            if (($estimation->getUser()->getOrganism() === $this->getUser()->getOrganism())) {
                return $this->render('bdc/show.html.twig', [
                    'estimation' => $estimation,
                ]);
            }
        }
        $message = "Cette estimation n'est pas liée à un utilisateur";
        $this->addFlash('danger', $message);
        return $this->redirectToRoute('adminIndex');
    }

    /**
     * @Route("/pay/{id}", name="bdc_pay")
     * @IsGranted("ROLE_COLLECTOR")
     * @param Estimations $estimation
     * @return Response
     */
    // route to go to payment
    public function pay(Estimations $estimation)
    {
        return $this->render('bdc/pay.html.twig', [
            'estimation' => $estimation,
        ]);
    }

    /**
     * @Route("/end/{id}", name="bdc_end")
     * @IsGranted("ROLE_COLLECTOR")
     * @param Estimations $estimation
     * @param EntityManagerInterface $em
     * @return Response
     */
    // route to confirm picture of identity Card
    public function end(Estimations $estimation, EntityManagerInterface $em)
    {
        // Validation of estimation and payment
        $estimation->setIsValidatedPayment(true)->setIsCollected(true);
        $em->persist($estimation);
        $em->flush();
        return $this->render('bdc/end.html.twig', [
            'estimation' => $estimation,
        ]);
    }
}
