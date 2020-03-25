<?php

namespace App\Controller;

use App\Entity\FAQ;
use App\Entity\Organisms;
use App\Entity\Phones;
use App\Entity\User;
use App\Form\EstimationsType;
use App\Form\EstimationType;
use App\Form\OrganismsType;
use App\Repository\FAQRepository;
use App\Form\RegistrationFormType;
use App\Form\SearchType;
use App\Repository\UserRepository;
use App\Form\UserType;
use App\Repository\EstimationsRepository;
use App\Form\MatriceType;
use App\Repository\OrganismsRepository;
use App\Form\RegistrationCollectorFormType;
use App\Repository\PhonesRepository;
use App\Security\LoginFormAuthenticator;
use App\Service\UploaderHelper;
use DateInterval;
use \DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpParser\Builder\Class_;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/admin")
 */

class AdminController extends AbstractController
{
    /**
     * @Route("/home", name="home_admin", methods={"GET"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse|Response
     */
    public function searchBar(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ) {
        if ($request->isXmlHttpRequest()) {
            $data = $_GET['users'];
            $result = $userRepository->findSearch($data);
            $json = [];

            foreach ($result as $user) {
                $lastname = $user->getLastname();
                $firstname = $user->getFirstname();
                $id = $user->getId();
                $json[] = ['lastname' => $lastname, 'firstname' => $firstname, 'id' => $id];
            }
            $json = json_encode($json);
            return new JsonResponse($json, 200, [], true);
        }
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
                    $form->get('password')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('collectors_index');
        }

        return $this->render('admin/collectors/register_collector.html.twig', [
            'registrationCollectorForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/matrice", name="matrice_upload")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function newMatrice(
        Request $request,
        EntityManagerInterface $em,
        PhonesRepository $phonesRepository,
        NormalizerInterface $normalizer
    ): Response {
        // Création du formulaire et récupération des données.
        $form = $this->createForm(MatriceType::class);
        $form->handleRequest($request);

        // Vérification des données après soumission
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $matrice */
            $matrice = $form['matrice_field']->getData();

            // Vérification du format requis.
            $ext = $matrice->getClientOriginalExtension();
            if ($ext != "csv") {
                $this->addFlash('danger', "Le fichier doit etre de type .csv. 
                Format actuel envoyé: .$ext");

                return $this->redirectToRoute("matrice_upload");
            }

            // Récupération des informations de la class utilisée.
            //$classMeta = $em->getClassMetadata(Phones::class);

            // Récupération des informations de connexion
            $connection = $em->getConnection();

            // Récupération de la plateforme SQL
            $dbPlatform = $connection->getDatabasePlatform();
            $dbPlatform->getListDatabasesSQL();

            // Récupération du nom de la table et préparation pour la truncate
            //$query = $dbPlatform->getTruncateTableSql($classMeta->getTableName());

            // Execution de la requete truncate
            //$connection->executeUpdate($query);

            // Récupération d'un tableau d'objet
            $repo = $phonesRepository->findAll();

            // transformation du tableau d'objet en tableau associatif
            $phoneTableauRepo = $normalizer->normalize($repo);

            // Compte le nombre de telephone en Bdd
            $nbrDePhonesBdd = count($phoneTableauRepo);

            // Ouverture du fichier à télécharger
            $csv = $matrice->openFile("r");

            // Données utiles pour le compte rendu
            $telTableauCsv = 0;
            $phoneIdentiques = [];
            $donneesIdentiques = 0;
            $telNonTrouve = [];


            /////// ETAPE 1
            /// TRAITEMENT DES DONNEES

            // Si il n'y a aucun telephone en BDD on applique ACTION 1

                     // ACTION 1
            if ($nbrDePhonesBdd == 0) {
                foreach ($csv as $rowCsv => $telNouveau) {
                    // On lit la premiere ligne
                    $ligneCsv = str_getcsv($telNouveau, ";");

                    // Si le csv est vide, on a fini l processus
                    if (empty($ligneCsv[0])) {
                        break;
                    }

                    // si la ligne est differente de la premiere ligne contenant les entete
                    // alors cette ligne correspond a un telephone csv
                    if ($rowCsv != 0) {
                        //connaitre le nombre de telephone lu en Bdd
                        $nbrTelTesteBdd = 0;

                        // On commence a compter le nombre de telephone que nous allons traiter
                        $telTableauCsv++;

                        $phone = new Phones();
                        $phone
                            ->setBrand($ligneCsv[1])
                            ->setModel($ligneCsv[2])
                            ->setCapacity($ligneCsv[3])
                            ->setColor($ligneCsv[4])
                            ->setPriceLiquidDamage($ligneCsv[5])
                            ->setPriceScreenCracks($ligneCsv[6])
                            ->setPriceCasingCracks($ligneCsv[7])
                            ->setPriceBattery($ligneCsv[8])
                            ->setPriceButtons($ligneCsv[9])
                            ->setPriceBlacklisted($ligneCsv[10])
                            ->setPriceRooted($ligneCsv[11])
                            ->setMaxPrice($ligneCsv[12])
                            ->setValidityPeriod(15);

                        $em->persist($phone);

                        // transformation de l'objet csv $phone en tableau
                        $phoneNormaliz = $normalizer->normalize($phone);
                        $phoneNormaliz['id'] = $telTableauCsv;

                        // Si le nombre de telephone testé de la bdd est atteint,
                        // alors on ajoute le phone au tableau des ajouts
                        if ($nbrTelTesteBdd == $nbrDePhonesBdd) {
                            array_push($telNonTrouve, $phoneNormaliz);
                            //echo "TELEPHONE NON TROUVE <br>";
                        }
                    }
                }
            }

            // si la base de donnée n'est pas vide suivre ACTION 2

                    // ACTION 2
            $phoneSup = [];
            if ($nbrDePhonesBdd > 0) {
                foreach ($csv as $rowCsv => $telNouveau) {
                    // On lit la premiere ligne du csv
                    $ligneCsv = str_getcsv($telNouveau, ";");

                    // si la ligne de csv est vide, alors on a fini le traitement
                    if (empty($ligneCsv[0])) {
                        break;
                    }

                    // si la ligne est differente de la premiere ligne contenant les entete
                    // alors cette ligne correspond a un telephone csv
                    if ($rowCsv != 0) {
                        $nbrTelTesteBdd = 0;
                        $telTableauCsv++;
                        foreach ($phoneTableauRepo as $valuePhoneRepo) {
                            $nbrTelTesteBdd++;
                            //       echo "1ere valeur de repo: <br>";
                            //       var_dump($valuePhoneRepo);

                            $phone = new Phones();
                            $phone
                                ->setBrand($ligneCsv[1])
                                ->setModel($ligneCsv[2])
                                ->setCapacity($ligneCsv[3])
                                ->setColor($ligneCsv[4])
                                ->setPriceLiquidDamage($ligneCsv[5])
                                ->setPriceScreenCracks($ligneCsv[6])
                                ->setPriceCasingCracks($ligneCsv[7])
                                ->setPriceBattery($ligneCsv[8])
                                ->setPriceButtons($ligneCsv[9])
                                ->setPriceBlacklisted($ligneCsv[10])
                                ->setPriceRooted($ligneCsv[11])
                                ->setMaxPrice($ligneCsv[12])
                                ->setValidityPeriod(15);

                            // transformation de l'objet csv $phone en tableau
                            $phoneNormaliz = $normalizer->normalize($phone);
                            $phoneNormaliz['id'] = $telTableauCsv;

                                //echo "on recherche : <br>";
                                //var_dump($phoneNormaliz);
                            $compPhone = array_diff($phoneNormaliz, $valuePhoneRepo);
                            $nbrOcurence = count($compPhone);

                            // Si les données sont les memes, on envoie dans le tableau des donnees identiques
                            if ($nbrOcurence == 1 && isset($compPhone['id']) || $compPhone == null) {
                                array_push($phoneIdentiques, $valuePhoneRepo['id']);
                                $donneesIdentiques++;
                                    //echo "TELEPHONE TROUVE<br>";
                                break;
                            }
                            // Si le nombre de telephone testé de la bdd est atteint,
                            // alors on ajoute le phone au tableau des ajouts
                            if ($nbrTelTesteBdd == $nbrDePhonesBdd) {
                                array_push($telNonTrouve, $phoneNormaliz);
                                //echo "TELEPHONE NON TROUVE <br>";
                            }
                        }
                    }
                }

                /////// ETAPE 2
                /// MODIFICATION DES DONNEES

                // Si des telephones ne font pas partis de la BDD, on les enregistre
                if (!empty($telNonTrouve)) {
                    foreach ($telNonTrouve as $tel) {
                        $phone = new Phones();
                        $phone
                            ->setBrand($tel["brand"])
                            ->setModel($tel["model"])
                            ->setCapacity($tel["capacity"])
                            ->setColor($tel["color"])
                            ->setPriceLiquidDamage($tel["priceLiquidDamage"])
                            ->setPriceScreenCracks($tel["priceScreenCracks"])
                            ->setPriceCasingCracks($tel["priceCasingCracks"])
                            ->setPriceBattery($tel["priceBattery"])
                            ->setPriceButtons($tel["priceButtons"])
                            ->setPriceBlacklisted($tel["priceBlacklisted"])
                            ->setPriceRooted($tel["priceRooted"])
                            ->setMaxPrice($tel["maxPrice"])
                            ->setValidityPeriod($tel["validityPeriod"]);

                        $em->persist($phone);
                    }
                }

                // Recherche des telephones qui n'ont plus rien a faire en Bdd et suppression
                if (!empty($phoneIdentiques)) {
                    $idTelBdd = [];
                    for ($i=0; $i < $nbrDePhonesBdd; $i++) {
                        $idTelBdd[] = $phoneTableauRepo[$i]['id'];
                    }

                    $idDifferent = array_diff($idTelBdd, $phoneIdentiques);

                    foreach ($idDifferent as $id) {
                        $resetPhone = $phonesRepository->findOneBy(['id' => $id]);
                        array_push($phoneSup, $resetPhone);
                        $em->remove($resetPhone);
                    }
                }
            }
                        /* DEBUG
                        /*echo "le foreach est terminé <br>";
                        echo "il y a $donneesIdentiques donnees identiques <br>";
                        echo "nombre nouveau telephone en bdd: " . count($telNonTrouve) . "<br>";
                        echo "les telephone suivant ne sont pas connu en bdd: <br>";
                        var_dump($telNonTrouve);
                        echo"telephone identiques: <br>";
                        var_dump($phoneIdentiques);
                        */
            $em->flush();

            $this->addFlash('success', 'Mise à jour effectuée');

            return $this->render('admin/matrice.html.twig', [
                'nbrPhoneBdd' => $nbrDePhonesBdd,
                'donneeIdentique' => $donneesIdentiques,
                'telNonTrouve' => $telNonTrouve,
                'nbrTelNonTrouve' => count($telNonTrouve),
                'analiseCsv' => $telTableauCsv,
                'phoneSup' => $phoneSup,
                'nbrPhoneSup' => count($phoneSup),
                'form' => $form->createView()
            ]);
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
        $organisms = $organismsRepository->findBy([], ["organismName" => "ASC"]);

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
     * @param UploaderHelper $uploaderHelper
     * @return Response
     */
    public function edit(Request $request, Organisms $organism, UploaderHelper $uploaderHelper): Response
    {
        $form = $this->createForm(OrganismsType::class, $organism);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['logo']->getData();

            if ($uploadedFile) {
                $newFileName = $uploaderHelper->uploadArticleImage($uploadedFile);
                $organism->setLogo($newFileName);
            }
            /*
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('upload_directory'), $fileName);
                $organism->setLogo($fileName);
            }
            */
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_organisms_index');
        }

        $organismPhone = "0".$organism->getOrganismPhone();

        return $this->render('admin/edit.html.twig', [
            'organism' => $organism,
            'organismPhone' => $organismPhone,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param FAQRepository $faqRepo
     * @return Response
     * @Route("/faq", name="admin_faq_index")
     */

    public function indexFaq(FAQRepository $faqRepo): Response
    {
        $faqContent = $faqRepo->findAll();
        return $this->render('admin/admin_faqIndex.html.twig', [
            'faqContent' => $faqContent]);
    }

    /**
     * @Route("/faq/{id}", name="admin_faq_show", methods={"GET"})
     * @param FAQ $fAQ
     * @return Response
     */
    public function showFaq(FAQ $fAQ): Response
    {
        return $this->render('admin/admin_faqShow.html.twig', [
            'f_a_q' => $fAQ ]);
    }

    /**
     * @Route("/user/{id}/documents", name="user_documents")
     * @param User $user
     * @return Response
     */
    public function userDocuments(User $user)
    {
        return $this->render('admin/user_documents.html.twig', [
            'user' => $user,
        ]);
    }

     /**
     * @Route("/modify/{id}", name="modify_estimationBrand")
     * @param EntityManagerInterface $em
     * @param int $id
     * @return Response
     */
    public function modifyEstimationBrand(
        EntityManagerInterface $em,
        int $id
    ) {
        $queryBuilder = $em->getRepository(Phones::class)->findAll();
        $brands = [];
        foreach ($queryBuilder as $phone) {
            array_push($brands, $phone->getBrand());
        }
        $brands = array_unique($brands);

        return $this->render("admin/modifyEstimation.html.twig", [
            'brands' => $brands,
            'id' => $id
        ]);
    }

    /**
     * @Route("/modify/{id}/{brand}", name="modify_estimationModel")
     * @param int $id
     * @param string $brand
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function modifyEstimationModel(EntityManagerInterface $em, int $id, string $brand)
    {
        $queryBuilder = $em->getRepository(Phones::class)->findByBrand($brand);
        $models = [];
        foreach ($queryBuilder as $brand) {
            array_push($models, $brand->getModel());
        }
        $models = array_unique($models);


        return $this->render("admin/modifyEstimationModel.html.twig", [
            "models" => $models,
            "brand" => $brand,
            "id" => $id
        ]);
    }

    /**
     * @Route("/modify/{id}/{brand}/{model}", name="modify_estimation_capacity")
     * @param string $brand
     * @param int $id
     * @param string $model
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function modifyEstimationCapacity(
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

        return $this->render("admin/modifyEstimationCapacity.html.twig", [
            "model" => $model,
            "brand" => $brand,
            "capacities" => $capacities,
            "id" => $id
        ]);
    }

    /**
     * @Route("/admin/modify/{id}/{brand}/{model}/{capacity}/quest", name="modify_estimation_quest")
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
    public function modifyEstimationQuest(
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
        $form = $this->createForm(EstimationsType::class, $estimation, ['method' => Request::METHOD_POST]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $imei = $data->getImei();
            $estimation->setEstimationDate(new DateTime('now'))
                ->setIsCollected(false)
                ->setBrand($brand)
                ->setModel($model)
                ->setCapacity($capacity)
                ->setColor("all")
                ->setMaxPrice($maxPrice)
                ->setIsValidatedPayment(false)
                ->setIsValidatedCi(false)
                ->setImei($imei);

            $estimated = $maxPrice;

            if ($form['liquidDamage']->getData() === "1") {
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
                mais nous pouvons te le reprendre $estimated euros symbolique";
            }
            $estimation->setEstimatedPrice($estimated);
            $em->persist($estimation);
            $em->flush();

            return $this->render('admin/final_modify_price.html.twig', [
                'estimation' => $estimation,
                'phone' => $phone,
                'message' => $message,
                'id' => $id
            ]);
        }
        return $this->render("admin/modifyEstimationQuest.html.twig", [
            "model" => $model,
            "brand" => $brand,
            "capacity" => $capacity,
            "phone" => $phone,
            "form" => $form->createView()

        ]);
    }

    /**
     * @Route("/anon", name="users_anon")
     * @param UserRepository $users
     * @param EntityManagerInterface $em
     * @return Response
     * @throws Exception
     */
    // route to anonymise users after 3 years
    public function anonUsers(UserRepository $users, EntityManagerInterface $em)
    {
        $date = new DateTime('now');
        $date->sub(new DateInterval('P3Y'));
        $users = $users->findOldUsers($date);

        $empty = 0; // find users
        if (empty($users)) {
            $empty = 1; // find no users
        } else {
            // if button is activated, then anonymised users
            // and give last signin in 1970 to forget them
            if (isset($_GET["anon"])) {
                foreach ($users as $user) {
                    $newEmail = $user->getId()."@olduser.bipbip";
                    $user->setEmail($newEmail)
                        ->setLastname("xxx")
                        ->setPhonenumber("0000000000")
                        ->setAddress("xxx")
                        ->setSigninDate(\DateTime::createFromFormat('Y-m-d', "1970-01-01"))
                        ->setOrganism(null)
                        ->setCollect(null);
                    $em->persist($user);
                    $empty = 2; // users anonymised
                }
                $em->flush();
                $this->addFlash('success', 'Utilisateur(s) anonymisé(s)');
            }
        }

        return $this->render('admin/anon.html.twig', [
            'users' => $users,
            'empty' => $empty,
        ]);
    }
}
