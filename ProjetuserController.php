<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\User;
use App\Form\ProjetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/projetuser')]
class ProjetuserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

public function __construct(EntityManagerInterface $entityManager)
{
    $this->entityManager = $entityManager;
}
    #[Route('/', name: 'app_projetuser_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $projets = $entityManager
            ->getRepository(Projet::class)
            ->findAll();

            
            
        return $this->render('projetuser/index.html.twig', [
            'projets' => $projets,
        ]);
    }

    #[Route('/new', name: 'app_projetuser_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projet = new Projet();
        $userConnected=$this->getUser();
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projet->setIdUser($userConnected);
            $entityManager->persist($projet);
            $entityManager->flush();

            return $this->redirectToRoute('app_projetuser_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('projetuser/new.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }

    #[Route('/{id_projet}', name: 'app_projetuser_show', methods: ['GET'])]
    public function show($id_projet, EntityManagerInterface $entityManager): Response
    {
        $projet = $entityManager
        ->getRepository(Projet::class)
        ->find($id_projet);
        $x = $projet->getIduser();
        $queryBuilder = $this->entityManager->createQueryBuilder();

            $nom = $queryBuilder
    ->select('u1.nom')
    ->from(User::class, 'u1')
    ->where('u1.id = :userId')
    ->setParameter('userId', $x)
    ->getQuery()
    ->getSingleScalarResult();

    $queryBuilder = $this->entityManager->createQueryBuilder();
    $prenom = $queryBuilder
    ->select('u2.prenom')
    ->from(User::class, 'u2')
    ->where('u2.id = :userId')
    ->setParameter('userId', $x)
    ->getQuery()
    ->getSingleScalarResult();

        return $this->render('projetuser/show.html.twig', [
            'projet' => $projet,
            'nom' => $nom,
            'prenom' => $prenom,
        ]);
    }

    #[Route('/{idProjet}/edit', name: 'app_projetuser_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_projetuser_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('projetuser/edit.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }

    #[Route('/{idProjet}', name: 'app_projetuser_delete', methods: ['POST'])]
    public function delete(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$projet->getIdProjet(), $request->request->get('_token'))) {
            $entityManager->remove($projet);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_projetuser_index', [], Response::HTTP_SEE_OTHER);
    }
}
