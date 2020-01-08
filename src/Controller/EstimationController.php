<?php


namespace App\Controller;

use App\Entity\Estimations;
use App\Entity\Phones;
use App\Form\EstimationType;
use App\Repository\PhonesRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class EstimationController
 * @package App\Controller
 * @Route("/estimation")
 */
class EstimationController extends AbstractController
{
    /**
     * @Route("/", name="new_estimation")
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function index(EntityManagerInterface $em): Response
    {
        $queryBuilder = $em->getRepository(Phones::class)->findAll();
        $brands = [];
        foreach ($queryBuilder as $phone) {
            array_push($brands, $phone->getBrand());
        }
        $brands = array_unique($brands);

        return $this->render("estimation/index.html.twig", [
            "brands" => $brands
        ]);
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
     * @param EntityManagerInterface $em
     * @param PhonesRepository $phone
     * @return Response
     * @throws \Exception
     */
    public function quest(
        Request $request,
        string $brand,
        string $model,
        int $capacity,
        EntityManagerInterface $em,
        PhonesRepository $phone
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
            $estimation->setEstimationDate(new DateTime('now'));
            $estimation->setIsCollected(false);
            $estimation->setBrand($brand);
            $estimation->setModel($model);
            $estimation->setCapacity($capacity);
            $estimation->setColor("all");
            $estimation->setMaxPrice($maxPrice);
            $estimation->setEstimatedPrice($maxPrice);
            $estimation->setIsValidatedPayment(false);
            $estimation->setIsValidatedSignature(false);

            if ($form['liquid_damage']->getData() === "1") {
                $estimation->setLiquidDamage($liquidDamage);
            } else {
                $estimation->setLiquidDamage(0);
            }

            if ($form['screenCracks']->getData() === "1") {
                $estimation->setScreenCracks($screenCracks);
            } else {
                $estimation->setScreenCracks(0);
            }

            if ($form['casingCracks']->getData() === "1") {
                $estimation->setCasingCracks($casingCracks);
            } else {
                $estimation->setCasingCracks(0);
            }

            if ($form['batteryCracks']->getData() === "1") {
                $estimation->setBatteryCracks($bateryPrice);
            } else {
                $estimation->setBatteryCracks(0);
            }

            if ($form['buttonCracks']->getData() === "1") {
                $estimation->setButtonCracks($buttonPrice);
            } else {
                $estimation->setButtonCracks(0);
            }

            $em->persist($estimation);
            $em->flush();

            return $this->redirectToRoute('home');
        }


        return $this->render("estimation/quest.html.twig", [
            "model" => $model,
            "brand" => $brand,
            "capacity" => $capacity,
            "phone" => $phone,
            "form" => $form->createView()
        ]);
    }
}
