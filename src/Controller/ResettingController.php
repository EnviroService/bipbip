<?php


namespace App\Controller;

use App\Entity\User;
use App\Form\ResettingType;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class ResettingController extends AbstractController
{
    // Mot de passe perdu -> envoi de mail -> gestion du temps.
    /**
     * @Route("/request", name="request_resetting")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param TokenGeneratorInterface $tokenGenerator
     * @param MailerInterface $mailer
     * @return RedirectResponse|Response
     * @throws TransportExceptionInterface
     */
    public function request(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenGeneratorInterface $tokenGenerator,
        MailerInterface $mailer
    ) {
        //création formulaire

        $user = $this->getUser();

        $form = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();

            $user = $entityManager->getRepository(User::class)
                ->findOneBy([
                    'email' => $email
                ]);


            if (!$user) {
                $this->addFlash('danger', "Cet email n'existe pas");
                return $this->redirectToRoute("request_resetting", [
                    'form' => $form->createView()
                ]);
            } else {
                $user->setToken($tokenGenerator->generateToken());
                $user->setPasswordRequestedAt(new DateTime());

                $entityManager->persist($user);

                $entityManager->flush();


                $mailUser = (new Email())
                    ->from(new Address('github-test@bipbip-mobile.fr', 'BipBip Mobile'))
                    ->to(new Address($user->getEmail()))
                    ->subject('Réinitialisation du mot de passe')
                    ->html($this->renderView('security/passLink.html.twig', array(
                        'user' => $user
                    )));

                $request->getSession()->getFlashBag()->add('success', "Un mail va vous être envoyé afin que
                vous puissiez renouveller votre mot de passe.
                Le lien sera valide 24h");

                $mailer->send($mailUser);
            }
            return $this->redirectToRoute("app_login");
        }

        return $this->render('user/forgottenPass.html.twig', [
            'form' => $form->createView()
        ]);
    }


    // si supérieur à 10min, retourne false
    //sinon retourne false

    private function isRequestInTime(DateTime $passewordRequestedAt = null)
    {
        if ($passewordRequestedAt === null) {
            return false;
        }

        $now = new DateTime();
        $interval = $now->getTimestamp() - $passewordRequestedAt->getTimestamp();

        $daySeconds = 60 * 30;
        $response = $interval > $daySeconds ? false : $response = true;

        return $response;
    }

    /**
     * @Route("reset/{id}/{token}", name="resetting")
     * @param User $user
     * @param $token
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */

    public function resetting(
        User $user,
        $token,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        // interdit l'accès à la page si:
        // le token associé au membre est null
        // le token enregistré en base et le token présent dans l'url ne sont pas égaux
        // le token date de plus de 10 minutes

        if ($user->getToken() === null || $token !== $user->getToken() ||
            !$this->isRequestInTime($user->getPasswordRequestedAt())) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(ResettingType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            //réinitialisation du token et de la date de sa création à NULL
            $user->setToken('null');
            $user->setPasswordRequestedAt(null);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "Ton mot de passe a été renouvelé.");

            return $this->redirectToRoute('app_login');
        }

        return $this->render('resetting/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
