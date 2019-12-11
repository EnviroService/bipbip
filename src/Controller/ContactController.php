<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Contact;

class ContactController extends AbstractController
{
    /**
     * @Route("contact/", name="add_message")
     * @param Request $request
     * @return Response A response instance
     */
    public function add(Request $request): Response
    {
        $contact = new Contact();

        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contact = $form->getData();

            //TODO config mailer to send the message

            return $this->redirectToRoute(
                'contact_success',
                [
                'contact' => $contact,
                                ]
            );
        }

        return $this->render(
            'contact/contact.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("contact/", name="contact_success")
     */
    public function success()
    {
        return $this->render(
            'contact/contact_success.html.twig'
        );
    }
}
