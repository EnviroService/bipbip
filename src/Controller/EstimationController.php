<?php


namespace App\Controller;

use App\Entity\Estimations;
use App\Entity\Phones;
use App\Entity\User;
use App\Form\EstimationType;
use App\Repository\EstimationsRepository;
use App\Repository\PhonesRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
            $estimation->setEstimationDate(new DateTime('now'))
                       ->setIsCollected(false)
                       ->setBrand($brand)
                       ->setModel($model)
                       ->setCapacity($capacity)
                       ->setColor("all")
                       ->setMaxPrice($maxPrice)
                       ->setIsValidatedPayment(false)
                       ->setIsValidatedSignature(false);

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
}
