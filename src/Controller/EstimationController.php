<?php


namespace App\Controller;

use App\Entity\Estimations;
use App\Entity\Phones;
use App\Entity\User;
use App\Form\EstimationType;
use App\Form\RegistrationFormType;
use App\Repository\PhonesRepository;
use App\Security\LoginFormAuthenticator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

/**
 * Class EstimationController
 * @package App\Controller
 * @Route("/estimation")
 */
class EstimationController extends AbstractController
{
    /**
     * @Route("/", name="new_estimation")
     * @param PhonesRepository $phones
     * @param string|null $brand
     * @param array|null $models
     * @return Response
     */
    public function index(PhonesRepository $phones, ?string $brand, ?array $models): Response
    {
        // find Brands distinct
        $brands = $phones->findBrandDistinct();

        return $this->render("estimation/index.html.twig", [
            "brands" => $brands,
            "models" => $models,
        ]);
    }

    /**
     * @Route("/modelId", name="model_id")
     * @param PhonesRepository $phones
     * @param Request $request
     * @return JsonResponse
     */
    public function modelId(PhonesRepository $phones, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $brand = $request->request->get('brand');
            $models = $phones->findModelDistinct($brand);

            return new JsonResponse([
                'models' => json_encode($models),
                ]);
        }
    }

    /**
     * @Route("/capacityId", name="capacity_id")
     * @param PhonesRepository $phones
     * @param Request $request
     * @return JsonResponse
     */
    public function capacityId(PhonesRepository $phones, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $brand = $request->request->get('brand');
            $model = $request->request->get('model');
            $capacities = $phones->findCapacityDistinct($brand, $model);

            return new JsonResponse([
                'capacities' => json_encode($capacities),
            ]);
        }
    }

    /**
     * @Route("/{brand}", name="estimation_brand")
     * @param string $brand
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function model(string $brand, EntityManagerInterface $em): Response
    {
        $queryBuilder = $em->getRepository(Phones::class)->findByBrand($brand);
        $models = [];
        foreach ($queryBuilder as $brand) {
            array_push($models, $brand->getModel());
        }
        $models = array_unique($models);


        return $this->render("estimation/model.html.twig", [
            "models" => $models,
            "brand" => $brand
        ]);
    }

    /**
     * @Route("/{brand}/{model}", name="estimation_capacity")
     * @param string $brand
     * @param string $model
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function capacity(string $brand, string $model, EntityManagerInterface $em): Response
    {
        $queryBuilder = $em->getRepository(Phones::class)->findByModel($model);
        $capacities = [];
        foreach ($queryBuilder as $model) {
            array_push($capacities, $model->getCapacity());
        }

        return $this->render("estimation/capacity.html.twig", [
            "model" => $model,
            "brand" => $brand,
            "capacities" => $capacities
        ]);
    }

    /**
     * @Route("/{brand}/{model}/{capacity}/quest", name="estimation_quest")
     * @param Request $request
     * @param string $brand
     * @param string $model
     * @param int $capacity
     * @param PhonesRepository $phone
     * @param EntityManagerInterface $em
     * @return Response
     * @throws \Exception
     */
    public function quest(
        Request $request,
        string $brand,
        string $model,
        int $capacity,
        PhonesRepository $phone,
        EntityManagerInterface $em
    ): Response {
        $phone = $phone->findOneBy(['model' => $model,
            'capacity' => $capacity
            ]);
        $maxPrice = $phone->getMaxPrice();
        $liquidDamage = $phone->getPriceLiquidDamage();
        $screenCracks = $phone->getPriceScreenCracks();
        $casingCracks = $phone->getPriceCasingCracks();
        $bateryPrice = $phone->getPriceBattery();
        $buttonPrice = $phone->getPriceButtons();

        $estimation = new Estimations();
        $form = $this->createForm(EstimationType::class, $estimation, ['method' => Request::METHOD_POST]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $estimation
                ->setEstimationDate(new DateTime('now'))
                ->setIsCollected(false)
                ->setBrand($brand)
                ->setModel($model)
                ->setCapacity($capacity)
                ->setColor("all")
                ->setMaxPrice($maxPrice)
                ->setIsValidatedPayment(false)
                ->setIsValidatedCi(false)
                ->setImei('0')
                ->setStatus(0);


            $estimated = $maxPrice;

            if ($form['liquid_damage']->getData() === "1") {
                $estimation->setLiquidDamage($liquidDamage);
                $estimated -= $liquidDamage;
            } else {
                $estimation->setLiquidDamage(0);
            }

            if ($form['screenCracks']->getData() === "1") {
                $estimation->setScreenCracks($screenCracks);
                $estimated -= $screenCracks;
            } else {
                $estimation->setScreenCracks(0);
            }

            if ($form['casingCracks']->getData() === "1") {
                $estimation->setCasingCracks($casingCracks);
                $estimated -= $casingCracks;
            } else {
                $estimation->setCasingCracks(0);
            }

            if ($form['batteryCracks']->getData() === "1") {
                $estimation->setBatteryCracks($bateryPrice);
                $estimated -= $bateryPrice;
            } else {
                $estimation->setBatteryCracks(0);
            }

            if ($form['buttonCracks']->getData() === "1") {
                $estimation->setButtonCracks($buttonPrice);
                $estimated -= $buttonPrice;
            } else {
                $estimation->setButtonCracks(0);
            }
            $message = "";
            if ($estimated < 1) {
                $estimated = 1;
                $message = "Ton téléphone a perdu trop de valeur, 
                nous te proposons : $estimated € symbolique et le traitement des déchets";
            }

            if (!empty($this->getUser())) {
                $estimation->setUser($this->getUser());
            }

            $estimation->setEstimatedPrice($estimated);
            $em->persist($estimation);
            $em->flush();

            return $this->render('estimation/final_price.html.twig', [
                'estimation' => $estimation,
                'phone' => $phone,
                'message' => $message
            ]);
        }

        return $this->render("estimation/quest.html.twig", [
            "model" => $model,
            "brand" => $brand,
            "capacity" => $capacity,
            "phone" => $phone,
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/user/register/{estimation}", name="estimation_register_user")
     * @param Estimations $estimation
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $authenticator
     * @return Response|null
     * @throws \Exception
     */
    public function registerUser(
        Estimations $estimation,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator
    ) {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_USER']);
            $user->setSignupDate(new DateTime('now'));
            $user->setSigninDate(new DateTime('now'));
            $user->addEstimation($estimation);
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $firstname = $user->getFirstname();
            $this->addFlash('success', "Compte créé, bienvenue $firstname.");


            $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );

            return $this->render('user/choiceEnvoi.html.twig', [
                'estimation' => $estimation,
                'user' => $user
            ]);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/{user}/estimation/{estimation}", name="show_etiquette")
     * @param User $user
     * @param Estimations $estimation
     * @return BinaryFileResponse
     */
    public function showEtiquette(
        User $user,
        Estimations $estimation
    ) {
        $filename = 'uploads/etiquettes/id'. $user->getId().'_E'. $estimation->getId() .'.pdf';
        return $this->file($filename);
    }
}
