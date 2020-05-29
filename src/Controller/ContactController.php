<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    // Permet de contacter bipbip

    /**
     * @Route("contact/", name="add_message")
     * @param Request $request
     * @param MailerInterface $mailer
     * @return Response A response instance
     * @throws TransportExceptionInterface
     */
    public function add(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contactFormData = $form->getData();

            // mail for bipbip
            $emailBip = (new Email())
                ->from(new Address($contactFormData->getEmail(), $contactFormData
                    ->getFirstname() . ' ' . $contactFormData->getLastname()))
                ->to(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
                ->replyTo($contactFormData->getEmail())
                ->subject($contactFormData->getSubject())
                ->html($this->renderView(
                    'contact/sentmail.html.twig',
                    array('form' => $contactFormData)
                ));

            // send a copie to sender
            $emailExp = (new Email())
                ->from(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
                ->to(new Address($contactFormData->getEmail(), $contactFormData
                        ->getFirstname() . ' ' . $contactFormData->getLastname()))
                ->replyTo('github-test@bipbip-mobile.fr')
                ->subject('Votre message envoyé à BipBip Mobile')
                ->html($this->renderView(
                    'contact/sentmailexp.html.twig',
                    array('form' => $contactFormData)
                ));

            $mailer->send($emailBip);
            $mailer->send($emailExp);

            $this->addFlash('success', 'Ton message a été envoyé, nous te répondrons rapidement !');

            return $this->redirectToRoute('home');
        }

        return $this->render(
            'contact/contact.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("asso", name="asso")
     * @param Request $request
     * @param MailerInterface $mailer
     * @return RedirectResponse|Response
     * @throws TransportExceptionInterface
     */
    public function asso(Request $request, MailerInterface $mailer)
    {
        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contactFormData = $form->getData();

            // mail for bipbip
            $emailBip = (new Email())
                ->from(new Address($contactFormData->getEmail(), $contactFormData
                        ->getFirstname() . ' ' . $contactFormData->getLastname()))
                ->to(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
                ->replyTo($contactFormData->getEmail())
                ->subject($contactFormData->getSubject())
                ->html($this->renderView(
                    'contact/sentmail.html.twig',
                    array('form' => $contactFormData)
                ));

            // send a copie to sender
            $emailExp = (new Email())
                ->from(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
                ->to(new Address($contactFormData->getEmail(), $contactFormData
                        ->getFirstname() . ' ' . $contactFormData->getLastname()))
                ->replyTo('github-test@bipbip-mobile.fr')
                ->subject('Votre message envoyé à BipBip Mobile')
                ->html($this->renderView(
                    'contact/sentmailexp.html.twig',
                    array('form' => $contactFormData)
                ));

            $mailer->send($emailBip);
            $mailer->send($emailExp);

            $this->addFlash('success', 'Ton message a été envoyé, nous te répondrons rapidement !');

            return $this->redirectToRoute('home');
        }
        return $this->render('home/asso.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
