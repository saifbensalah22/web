<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Entity\User;
use App\Entity\Projet;
use App\Form\OffreType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/offreuser')]
class OffreuserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

public function __construct(EntityManagerInterface $entityManager)
{
    $this->entityManager = $entityManager;
}
    
#[Route('/', name: 'app_offreuser_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {   
        $offres = $entityManager
            ->getRepository(Offre::class)
            ->findAll();

         
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('offreuser/index.html.twig', [
            'offres' => $offres,
            'users' => $users
        ]);
    }



/*
    #[Route('/', name: 'app_offreuser_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {   
        $offres = $entityManager
            ->getRepository(Offre::class)
            ->findAll();

         
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('offreuser/index.html.twig', [
            'offres' => $offres,
            'users' => $users
        ]);
    }*/
    #[Route('/select-user', name: 'app_offreuser_selectuser')]
public function selectUser(Request $request): Response
{
    $userId = $request->request->get('userId');

    

    // render the new content as a Twig template and return it as a response
    return $this->render('offreuser/newcontent.html.twig', [
       'userid' => $userId
    ]);
}

    #[Route('/new', name: 'app_offreuser_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {   $userConnected=$this->getUser();
        $idC=$userConnected->getPassword();
        
        $offre = new Offre();

        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $offre -> setIdUser($userConnected);
            $entityManager->persist($offre);
            $entityManager->flush();

            return $this->redirectToRoute('app_offreuser_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('offreuser/new.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{idOffre}', name: 'app_offreuser_show', methods: ['GET'])]
    public function show(Offre $offre): Response
    {   $x = $offre->getIdUser();
        $y = $offre->getIdProjet();

        $queryBuilder = $this->entityManager->createQueryBuilder();
    $prenomoffre = $queryBuilder
    ->select('u2.prenom')
    ->from(User::class, 'u2')
    ->where('u2.id = :userId')
    ->setParameter('userId', $x)
    ->getQuery()
    ->getSingleScalarResult();
    $queryBuilder = $this->entityManager->createQueryBuilder();
    $title = $queryBuilder
    ->select('p.titre')
    ->from(Projet::class, 'p')
    ->where('p.id_projet = :idProjet')
    ->setParameter('idProjet', $y)
    ->getQuery()
    ->getSingleScalarResult();

        return $this->render('offreuser/show.html.twig', [
            'offre' => $offre,
            'offreur' => $prenomoffre,
            'title' => $title,
        ]);
    }

    #[Route('/{idOffre}/edit', name: 'app_offreuser_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Offre $offre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_offreuser_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('offreuser/edit.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{idOffre}', name: 'app_offreuser_delete', methods: ['POST'])]
    public function delete(Request $request, Offre $offre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offre->getIdOffre(), $request->request->get('_token'))) {
            $entityManager->remove($offre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_offreuser_index', [], Response::HTTP_SEE_OTHER);
    }
}
