<?php


namespace App\Controller;

use App\Entity\Estimations;
use App\Entity\Phones;
use App\Form\EstimationType;
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
     * @param string $brand
     * @param string $model
     * @param int $capacity
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function quest(string $brand, string $model, int $capacity, EntityManagerInterface $em): Response
    {
        $em->getRepository(Phones::class)->findBy([
            "model" => $model,
            "capacity" => $capacity
        ]);

        return $this->render("estimation/quest.html.twig", [
            "model" => $model,
            "brand" => $brand,
            "capacity" => $capacity
        ]);
    }
}
