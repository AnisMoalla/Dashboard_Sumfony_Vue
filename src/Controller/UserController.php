<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType ;
use Symfony\Component\Validator\Constraints\Image;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/user")
 * @IsGranted("ROLE_ADMIN")
 */
class UserController extends AbstractController
{

    //functions stat
    public function getStatAges($users)
    {
        $ages = array(0,0,0,0,0);

        foreach ($users as $user)
        {
            if ($user->getAge() >= 18 && $user->getAge() < 25)
            {
                $ages[0]++;
            }
            elseif ($user->getAge() >= 25 && $user->getAge() < 35 )
            {
                $ages[1]++;
            }
            elseif ($user->getAge() >= 35 && $user->getAge() < 45 )
            {
                $ages[2]++;
            }
            elseif ($user->getAge() >= 45 && $user->getAge() < 55 )
            {
                $ages[3]++;
            }
            elseif ($user->getAge() >= 55)
            {
                $ages[4]++ ;
            }
        }
        return $ages ;
    }


    public function getStatVerif($users)
    {
        $verif = [0,0] ;
        foreach ($users as $user)
        {
            if ($user->isVerified() == "true")
            {
             $verif[0]++ ;
            } else
            {
                $verif[1]++ ;
            }
        }
        return $verif ;
    }

    /**
     * @Route("/statsadmin", name="statsadmin", methods={"GET"})
     */
    public function statsadmin(): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $users= $userRepository->findAll();

        $nbrEmployes = 0 ;
        $nbrAdmins = 0 ;

        foreach ($users as $user)
        {
            if ($user->getRoles()[0]=="ROLE_EMPLOYE")
            {
                $nbrEmployes++;
            }
            elseif ($user->getRoles()[0]=="ROLE_ADMIN")
            {
                $nbrAdmins++;
            }

        }

        $statVerif = $this->getStatVerif($users) ;
        $statages = $this->getStatAges($users) ;
        return $this->render('user/stats.html.twig' ,
            [
                "nbrEmployes" => $nbrEmployes ,
                "nbrAdmins" => $nbrAdmins ,
                "statages" => $statages ,
                "statVerif" => $statVerif ,
            ]

        ) ;

    }

    /**
     * @Route("/adminsListe", name="adminsListe", methods={"GET"})
     */
    public function printA(UserRepository $userRepository)
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('user/adminListe.html.twig', [
            'title' => "Welcome to our PDF Test",
            'users' => $userRepository->findByRole("ROLE_ADMIN"),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("Adminspdf.pdf", [
            "Attachment" => true

        ]);
    }

    /**
     * @Route("/employesListe", name="employesListe", methods={"GET"})
     */
    public function printE(UserRepository $userRepository)
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('user/employeListe.html.twig', [
            'title' => "Welcome to our PDF Test",
            'users' => $userRepository->findByRole("ROLE_EMPLOYE"),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("Employespdf.pdf", [
            "Attachment" => true

        ]);
    }

    /**
     * @Route("/admins", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findByRole("ROLE_ADMIN"),
        ]);
    }

    /**
     * @Route("/newAdmin", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request , UserPasswordEncoderInterface $userPasswordEncoder): Response
    {
        $user = new User();
        $user->setRoles(["ROLE_ADMIN"]);
        $form = $this->createForm(UserType::class, $user);
        $form->add('poste' , null , array(
            'constraints' => array(
                new NotBlank(),)))
            ->add('image', FileType::class, [
                'label' => 'Profile picture',

                'mapped' => false,

                'required' => false,

                'constraints' => [
                    new Image(),
                ]]);
        $form->add('ajouter',SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            if ($image)
            {
                $newFilename = uniqid().'.'.$image->guessExtension();
                try {
                    $image->move(
                        $this->getParameter('images_directory'),
                        $newFilename );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $user->setImage($newFilename);
            }
            $hash = $userPasswordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash) ;
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/Admin", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/editAdmin", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->add('poste' , null , array(
            'constraints' => array(
                new NotBlank(),)));
        $form->add('modifier',SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/A", name="user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/employes", name="employe_index", methods={"GET"})
     */
    public function indexE(UserRepository $userRepository): Response
    {
        return $this->render('user/indexE.html.twig', [
            'users' => $userRepository->findByRole("ROLE_EMPLOYE"),
        ]);
    }

    /**
     * @Route("/newEmploye", name="employe_new", methods={"GET","POST"})
     */
    public function newE(Request $request , UserPasswordEncoderInterface $userPasswordEncoder): Response
    {
        $user = new User();
        $user->setRoles(["ROLE_EMPLOYE"]);
        $form = $this->createForm(UserType::class, $user);
        $form->add('but' , null , array(
            'constraints' => array(
                new NotBlank(),)))
            ->add('image', FileType::class, [
                'label' => 'Profile picture',

                'mapped' => false,

                'required' => false,

                'constraints' => [
                    new Image(),
                ]]);
        $form->add('salaire' , null , array(
            'constraints' => array(
                new NotBlank(),)));
        $form->add('ajouter',SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            if ($image)
            {
                $newFilename = uniqid().'.'.$image->guessExtension();
                try {
                    $image->move(
                        $this->getParameter('images_directory'),
                        $newFilename );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $user->setImage($newFilename);
            }
            $hash = $userPasswordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash) ;
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('employe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/newE.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/Employe", name="employe_show", methods={"GET"})
     */
    public function showE(User $user): Response
    {
        return $this->render('user/showE.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/editEmploye", name="employe_edit", methods={"GET","POST"})
     */
    public function editE(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->add('but' , null , array(
            'constraints' => array(
                new NotBlank(),)));
        $form->add('salaire' , null , array(
            'constraints' => array(
                new NotBlank(),)));
        $form->add('modifier',SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('employe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/editE.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/E", name="employe_delete", methods={"POST"})
     */
    public function deleteE(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('employe_index', [], Response::HTTP_SEE_OTHER);
    }
}
