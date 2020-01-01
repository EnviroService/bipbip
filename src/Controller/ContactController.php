<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
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

            $email = (new Email())
                ->from(new Address($contactFormData->getEmail(), $contactFormData
                    ->getFirstname() . ' ' . $contactFormData->getLastname()))
                ->to(new Address('jyaire@gmail.com', 'BipBip Mobile'))
                ->replyTo($contactFormData->getEmail())
                ->subject('Nouveau message reçu depuis l\'application BipBip Mobile')
                ->html($this->renderView(
                    'contact/sentmail.html.twig',
                    array('form' => $contactFormData)
                ));

            $mailer->send($email);

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
}
