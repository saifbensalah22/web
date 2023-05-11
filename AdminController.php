<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\DemandeRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }





    #[Route('/back', name: 'back_admin')]
    public function back(): Response
    {
        return $this->render('admin/back.html.twig', [
            
        ]);
    }

    #[Route('/listUsers', name: 'app_admin_listUsers', methods: ['GET'])]
    public function listUsers(UserRepository $userRepository): Response
    {
        return $this->render('admin/listUsers.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

   

    #[Route('/ajouterUser', name: 'admin_ajouterUser')]
    public function ajouterUser(ManagerRegistry $doctrine , Request $request): Response
    {  
        $user = new User ();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {






            $m=$doctrine->getManager();

            $m->persist($user);
            $m->flush();
            return $this->redirectToRoute('app_admin_listUsers');
        }

        return $this->renderForm('admin/new.html.twig', [
            'controller_name' => 'AdminController',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/detail-client/{id}", name="detail_client" )
     *
     * @return void
     */
    public function detail_client(User $user): Response
    {
        return $this->render('admin/detail-client.html.twig', [
            'user' => $user,
        ]);
    }

     

    #[Route('/supprimer-client/{id}', name: 'supprimer_client')]
    public function supprimer_client($id ,Request $request, UserRepository $userRepository, ManagerRegistry $doctrine): Response
    {   
        $user=$userRepository->find($id);
        $m=$doctrine->getManager();
        $m->remove($user);
        $m->flush();

            $this->addFlash('info', "la suppression se faite avec succès");
            
        

        return $this->redirectToRoute('app_admin_listUsers');
    }

 /**
 * @Route("/bloquer/{id}", name="bloquer_utilisateur")
 */
public function bloquerUtilisateur(User $user)
{
    $user->setIsBlocked(true);
    $this->getDoctrine()->getManager()->flush();

    $this->addFlash('info', 'L\'utilisateur a été bloqué.');
    

    return $this->redirectToRoute('app_admin_listUsers');
}

/**
 * @Route("/debloquer/{id}", name="debloquer_utilisateur")
 */
public function debloquerUtilisateur(User $user)
{
    $user->setIsBlocked(false);
    $this->getDoctrine()->getManager()->flush();

    $this->addFlash('info', 'L\'utilisateur a été débloqué.');

    return $this->redirectToRoute('app_admin_listUsers');
}

/**
 * @Route("/trierParPrenom", name="sort_users_by_first_name")
 */
public function triParPrenom(UserRepository $userRepository)
{
    $users = $userRepository->findAllSortedByFirstName();

    return $this->render('admin/listUsers.html.twig', [
        'users' => $users,
    ]);
}

/**
 * @Route("/trierParPrenom", name="sort_users_by_last_name")
 */
public function triParNom(UserRepository $userRepository)
{
    $users = $userRepository->findAllSortedByLastName();

    return $this->render('admin/listUsers.html.twig', [
        'users' => $users,
    ]);
}

#[Route('/search', name: 'searchUser')]
public function search(Request $request , NormalizerInterface $Normalizer , UserRepository $ur): Response
{
    $requestString=$request->get('searchValue');
    $users=$ur->chercheParNom($requestString);
    $jsonContent=$Normalizer->normalize($users,'json',['groups'=>'users']);
    $retour=json_encode(($jsonContent));

    return new Response($retour);
}

#[Route('/showAdminDemande', name: 'admin_show_all_demandes', methods: ['GET'])]
public function showalldemandes(DemandeRepository $demandeClientRepository): Response
{
    return $this->render('admin/listDemandes.html.twig', [
        'demande_clients' => $demandeClientRepository->findAll(),
    ]);
}

#[Route('/affaire', name: 'admin_demande_offre_affaire', methods: ['GET'])]
public function mesaffaires(DemandeRepository $demandeClientRepository): Response
{

    return $this->render('admin/ListDesAffaires.html.twig', [
        'demande_clients' => $demandeClientRepository->findmesoffredetravail($this->getUser()->getId()),
    ]);
}

/**
 * @Route("/trierParDate", name="sort_demande_by_date")
 */
public function triParDate(DemandeRepository $dr)
{
    $demandes = $dr->findAllSortedByDate();

    return $this->render('admin/listDemandes.html.twig', [
        'demande_clients' => $demandes,
    ]);
}
 

  #[Route('/ajouterAdmin', name: 'admin_ajouteradmin')]
    public function ajouteradmin(ManagerRegistry $doctrine , Request $request): Response
    {  
        $user = new User ();
        $user->setRole("ROLE_ADMIN");
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {        
            $m=$doctrine->getManager();
            $m->persist($user);
            $m->flush();
            return $this->redirectToRoute('app_admin_listUsers');
        }

        return $this->renderForm('admin/newAdmin.html.twig', [
            'controller_name' => 'AdminController',
            'form' => $form,
        ]);
    }










    


    
}
