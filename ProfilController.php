<?php

namespace App\Controller;

use App\Entity\Cv;
use App\Form\UserType;
use App\Entity\User;
use App\Form\CvType;
use App\Repository\CvRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;






class ProfilController extends AbstractController
{
    

    /*#[Route('/profil', name: 'app_profil')]
    public function index(): Response
    {
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }*/




 /**
     * @Route("/profil", name="app_profil")
     */
    public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder, ManagerRegistry $doctrine , CvRepository $cvRepository): Response
    {
        $myProfile = $this->getUser();
        $id = $myProfile->getId();
        

        $form = $this->createForm(UserType::class, $myProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mdp = $myProfile->getPassword();
            $password = $passwordEncoder->encodePassword($myProfile, $mdp);
            $myProfile->setPassword($password);

            // Traitement de l'image
            $imageFile = $form['image']->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('user_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'erreur si la sauvegarde de l'image échoue
                }

                $myProfile->setImage($newFilename);
            }

            $manager = $doctrine->getManager();
            $manager->flush();
            $this->addFlash('info', "Le profil est mis à jour");
            return $this->redirectToRoute('app_profil');
        }

        $cv = new Cv();
        $formm = $this->createForm(CvType::class, $cv);
        $formm->handleRequest($request);

        if ($formm->isSubmitted() && $formm->isValid()) {
            
            $cv->setIdUser($myProfile->getId());
            $myProfile->setRole('freelancer');
            $cvRepository->save($cv, true);


            $this->addFlash('info', "votre CV est ajouté ");
        }

        return $this->render('profil/index.html.twig', [
            'profile' => $form->createView(),
            'cv' => $formm->createView(),

        ]);
    }

    
}

