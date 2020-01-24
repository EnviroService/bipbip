<?php

namespace App\Controller;

use App\Entity\Organisms;
use App\Entity\Phones;
use App\Entity\User;
use App\Form\EstimationType;
use App\Form\OrganismsType;
use App\Repository\EstimationsRepository;
use App\Form\MatriceType;
use App\Form\OrganismsType;
use App\Repository\OrganismsRepository;
use App\Form\RegistrationCollectorFormType;
use App\Repository\PhonesRepository;
use App\Security\LoginFormAuthenticator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

/**
 * @Route("/admin")
 */

class AdminController extends AbstractController
{
    /**
     * @Route("/home", name="home_admin")
     */
    public function show(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/collector/register", name="register_collector")
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $authenticator
     * @return Response|null
     * @throws Exception
     */
    public function registerCollector(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator
    ) {
        $user = new User();
        $form = $this->createForm(RegistrationCollectorFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_COLLECTOR']);
            $user->setSignupDate(new DateTime('now'));
            $user->setSigninDate(new DateTime('now'));
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('adminhome_admin');
        }

        return $this->render('admin/register_collector.html.twig', [
            'registrationCollectorForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/matrice", name="matrice_upload")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function newMatrice(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MatriceType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $matrice */
            $matrice = $form['matrice_field']->getData();
            $csv = $matrice->openFile("r");
            $classMeta = $em->getClassMetadata(Phones::class);
            $connection = $em->getConnection();
            $dbPlatform = $connection->getDatabasePlatform();
            $query = $dbPlatform->getTruncateTableSql($classMeta->getTableName());
            $connection->executeUpdate($query);

            foreach ($csv as $key => $value) {
                if ($key != 0) {
                    $value = strval($value);
                    $row = str_getcsv($value, ";");
                    if ($value == null) {
                        break;
                    }
                    $phone = new Phones();
                    $phone->setBrand($row[1])
                        ->setModel($row[2])
                        ->setCapacity($row[3])
                        ->setColor($row[4])
                        ->setPriceLiquidDamage($row[5])
                        ->setPriceScreenCracks($row[6])
                        ->setPriceCasingCracks($row[7])
                        ->setPriceBattery($row[8])
                        ->setPriceButtons($row[9])
                        ->setPriceBlacklisted($row[10])
                        ->setPriceRooted($row[11])
                        ->setMaxPrice($row[12])
                        ->setValidityPeriod(13);
                    $em->persist($phone);
                    $em->flush();
                }
            }

            $this->addFlash('success', 'mise à jour effectuée');

            return $this->redirectToRoute('home_admin');
        }
        return $this->render('admin/matrice.html.twig', [
            'form' => $form->createView()
        ]);
    }

     /** @Route("/organisms", name="admin_organisms_index", methods={"GET"})
      *  @param OrganismsRepository $organismsRepository
      *  @return Response
      */
    public function index(OrganismsRepository $organismsRepository): Response
    {
        $organisms = $organismsRepository->findAll();
        return $this->render('admin/index_organism.html.twig', [
            'organisms' => $organisms,
        ]);
    }

    /**
     * @Route("/organism/{id}", name="admin_organism_show", methods={"GET"})
     * @param Organisms $organism
     * @return Response
     */
    public function showOrganism(Organisms $organism): Response
    {
        return $this->render('admin/show_organism.html.twig', [
            'organism' => $organism,
        ]);
    }

    /**
     * @Route("/organism/edit/{id}", name="admin_organism_edit", methods={"GET","POST"})
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
            $file = $form['logo']->getData();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('upload_directory'), $fileName);
                $organism->setLogo(
                    new File($this->getParameter('upload_directory') . '/' . $organism->getLogo())
                );
                dd($file);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_organisms_index');
        }

        return $this->render('admin/edit.html.twig', [
            'organism' => $organism,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/remove/{id}", name="remove_estimationBrand")
     * @param EntityManagerInterface $em
     * @param int $id
     * @return Response
     */
    public function removeEstimationBrand(
        EntityManagerInterface $em,
        int $id
    ) {
        $queryBuilder = $em->getRepository(Phones::class)->findAll();
        $brands = [];
        foreach ($queryBuilder as $phone) {
            array_push($brands, $phone->getBrand());
        }
        $brands = array_unique($brands);

        return $this->render("admin/removeEstimation.html.twig", [
            'brands' => $brands,
            'id' => $id
        ]);
    }

    /**
     * @Route("/remove/{id}/{brand}", name="remove_estimationModel")
     * @param int $id
     * @param string $brand
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function removeEstimationModel(EntityManagerInterface $em, int $id, string $brand)
    {
        $queryBuilder = $em->getRepository(Phones::class)->findByBrand($brand);
        $models = [];
        foreach ($queryBuilder as $brand) {
            array_push($models, $brand->getModel());
        }
        $models = array_unique($models);


        return $this->render("admin/removeEstimationModel.html.twig", [
            "models" => $models,
            "brand" => $brand,
            "id" => $id
        ]);
    }

    /**
     * @Route("/remove/{id}/{brand}/{model}", name="remove_estimation_capacity")
     * @param string $brand
     * @param int $id
     * @param string $model
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function removeEstimationCapacity(
        string $brand,
        string $model,
        int $id,
        EntityManagerInterface $em
    ): Response {
        $queryBuilder = $em->getRepository(Phones::class)->findByModel($model);
        $capacities = [];
        foreach ($queryBuilder as $model) {
            array_push($capacities, $model->getCapacity());
        }

        return $this->render("admin/removeEstimationCapacity.html.twig", [
            "model" => $model,
            "brand" => $brand,
            "capacities" => $capacities,
            "id" => $id
        ]);
    }

    /**
     * @Route("/admin/remove/{id}/{brand}/{model}/{capacity}/quest", name="remove_estimation_quest")
     * @param Request $request
     * @param string $brand
     * @param string $model
     * @param int $capacity
     * @param int $id
     * @param PhonesRepository $phone
     * @param EntityManagerInterface $em
     * @param EstimationsRepository $estimationsRepo
     * @return Response
     * @throws Exception
     */
    public function removeEstimationQuest(
        Request $request,
        string $brand,
        string $model,
        int $capacity,
        int $id,
        PhonesRepository $phone,
        EntityManagerInterface $em,
        EstimationsRepository $estimationsRepo
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

        $estimation = $estimationsRepo->findOneBy(['id' => $id]);
        $form = $this->createForm(EstimationType::class, $estimation, ['method' => Request::METHOD_POST]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $estimation->setEstimationDate(new DateTime('now'))
                ->setIsCollected(false)
                ->setBrand($brand)
                ->setModel($model)
                ->setCapacity($capacity)
                ->setColor("all")
                ->setMaxPrice($maxPrice)
                ->setIsValidatedPayment(false)
                ->setIsValidatedCi(false)
                ->setImei('0');

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
                $message = "Votre téléphone a perdu trop de valeur, 
                mais nous pouvons vous le reprendre $estimated euros symbolique";
            }
            $estimation->setEstimatedPrice($estimated);
            $em->persist($estimation);
            $em->flush();

            return $this->render('admin/final_remove_price.html.twig', [
                'estimation' => $estimation,
                'phone' => $phone,
                'message' => $message,
                'id' => $id
            ]);
        }

        return $this->render("admin/removeEstimationQuest.html.twig", [
            "model" => $model,
            "brand" => $brand,
            "capacity" => $capacity,
            "phone" => $phone,
            "form" => $form->createView()
        ]);
    }
}
