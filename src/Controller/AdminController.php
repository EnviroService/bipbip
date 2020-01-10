<?php


namespace App\Controller;

use App\Entity\Phones;
use App\Form\MatriceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/matrice", name="matrice_upload")
     * @param Request $request
     * @return Response
     * @SuppressWarnings(PHPMD, UnusedLocalVariable)
     */
    public function newMatrice(Request $request): Response
    {

        $form = $this->createForm(MatriceType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $matrice */
            $matrice = $form['matrice_field']->getData();
            $csv = $matrice->openFile();
            $em = $this->getDoctrine()->getManager();
            $classMeta = $em->getClassMetadata(Phones::class);
            $connection = $em->getConnection();
            $dbPlatform = $connection->getDatabasePlatform();
            $query = $dbPlatform->getTruncateTableSql($classMeta->getTableName());
            $connection->executeUpdate($query);

            foreach ($csv as $key) {
                $row = str_getcsv($key, ",");
                if ($row[0] != 'fin') {
                    $phone = new phones();
                    $phone->setBrand($row[2])
                        ->setModel($row[1])
                        ->setCapacity($row[4])
                        ->setColor($row[6])
                        ->setPriceLiquidDamage($row[7])
                        ->setPriceScreenCracks($row[8])
                        ->setPriceCasingCracks($row[9])
                        ->setPriceBattery($row[10])
                        ->setPriceButtons($row[11])
                        ->setPriceBlacklisted($row[12])
                        ->setPriceRooted($row[13])
                        ->setMaxPrice($row[14])
                        ->setValidityPeriod(15);
                    $em->persist($phone);
                    $em->flush();
                } else {
                    break;
                }
            }

            $this->addFlash('success', 'mise à jour effectuée');

            return $this->redirectToRoute('matrice_upload');
        }

        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
