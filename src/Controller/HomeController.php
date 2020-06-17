<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Form\RecrutementType;
use App\Repository\CollectsRepository;
use App\Repository\OrganismsRepository;
use JsonSchema\Constraints\NumberConstraint;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email as EmailAssert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function index()
    {
        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("vends/", name="vends")
     * @return Response
     */
    public function vends()
    {
        return $this->render('home/vends.html.twig');
    }

    /**
     * @Route("infos/team", name="team")
     */
    public function team()
    {
        return $this->render('infos/team.html.twig');
    }

    /**
     * @Route("infos/grade", name="grade")
     */
    public function grade()
    {
        return $this->render('infos/grade.html.twig');
    }

    /**
     * @Route("infos/bipbip", name="qui-sommes-nous")
     */
    public function who()
    {
        return $this->render('infos/bipbip.html.twig');
    }

    /**
     * @Route("admin/", name="adminIndex")
     */
    public function adminIndex()
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("infos/cgu", name="cgu")
     */
    public function cgu()
    {
        return $this->render('infos/cgu.html.twig');
    }

    /**
     * @Route("infos/cgv", name="cgv")
     */
    public function cgv()
    {
        return $this->render('infos/cgv.html.twig');
    }

    /**
     * @Route("infos/protection", name="protection")
     */
    public function protection()
    {
        return $this->render('infos/protection.html.twig');
    }

    /**
     * @Route("infos/cookies", name="cookies")
     */
    public function cookies()
    {
        return $this->render('infos/cookies.html.twig');
    }

    /**
     * @Route("infos/mentions", name="mentions")
     */
    public function mentions()
    {
        return $this->render('infos/mentions.html.twig');
    }

    /**
     * @Route("autres", name="autres")
     */
    public function autres()
    {
        return $this->render('estimation/autres.html.twig');
    }

    /**
     * @Route("histoire", name="histoire")
     */
    public function histoire()
    {
        return $this->render('infos/histoire.html.twig');
    }

    /**
     * @Route("recrute", name="recrute")
     * @param Request $request
     * @param MailerInterface $mailer
     * @param ValidatorInterface $validator
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function recrute(Request $request, MailerInterface $mailer, ValidatorInterface $validator)
    {
        $form = $this->createForm(RecrutementType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $email = $form->get('email')->getData();
            $emailConstraint = new EmailAssert();
            $emailConstraint->message = 'Cet email est invalide';

            $errorsMail = $validator->validate(
                $email,
                $emailConstraint
            );

            if (0 < count($errorsMail)) {
                $errors = $errorsMail[0]->getMessage();
                return $this->render('infos/recrute.html.twig', [
                    'form' => $form->createView(),
                    'errors' => $errors
                ]);
            }

            $data = $form->getData();
            $cvExp = $form->get('CV')->getData()->getPathname();
            $lettre = $form->get('lettre')->getData()->getPathname();

            $emailBip = (new Email())
                ->from(new Address(
                    $email,
                    $data['firstname'] . ' ' . $data['lastname']
                ))
                ->to(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
                ->replyTo($email)
                ->attachFromPath(
                    $cvExp,
                    "CV_" . $data['lastname'] . "_" . $data['firstname'],
                    "application/pdf"
                )
                ->attachFromPath(
                    $lettre,
                    "Lettre_" . $data['lastname'] . "_" . $data['firstname'],
                    "application/pdf"
                )
                ->subject('recrutement')
                ->html($this->renderView(
                    'contact/sentMailRecruit.html.twig',
                    [
                        'form' => $data
                    ]
                ));

            // send a copie to sender
            $emailExp = (new Email())
                ->from(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
                ->to(new Address(
                    $email,
                    $data['firstname'] . ' ' . $data['lastname']
                ))
                ->replyTo('github-test@bipbip-mobile.fr')
                ->subject('Votre message à bien était envoyé à BipBip Mobile')
                ->html($this->renderView(
                    'contact/sentMailExpRecruit.html.twig',
                    [
                        'form' => $data
                    ]
                ));

            $mailer->send($emailBip);
            $mailer->send($emailExp);
            $this->addFlash('success', 'Candidature envoyée avec succés');
        }


        return $this->render('infos/recrute.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("livraisons", name="livraisons")
     */
    public function livraisons()
    {
        return $this->render('infos/livraisons.html.twig');
    }

    /**
     * @Route("presse", name="presse")
     */
    public function presse()
    {
        return $this->render('infos/presse.html.twig');
    }

    /**
     * @Route("bonsplans", name="bonsplans")
     */
    public function bonsplans()
    {
        return $this->render('bonsplans/index.html.twig');
    }

    /**
     * @Route("bipers", name="bipers")
     * @return Response
     */
    public function collectorBipers(CollectsRepository $collectsRepo)
    {
        $form = $this->createForm(ContactType::class);
        $directory = "uploads/plaquette/";
        $filename = "Plaquette_de_presentation_generale_BipBip.pdf";
        $filenameSave = $directory . $filename;

        return $this->render('home/collectorBipers.html.twig', [
            'form' => $form->createView(),
            'plaquette' => $filenameSave,
            'collects' => $collectsRepo->findByDateValid()
        ]);
    }
}
