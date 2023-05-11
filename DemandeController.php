<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Demande;
use App\Form\DemandeType;
use App\Repository\UserRepository;
use App\Repository\DemandeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/demande')]
class DemandeController extends AbstractController
{

    #[Route('/', name: 'app_demande_client_index', methods: ['GET'])]
    public function index(DemandeRepository $demandeClientRepository): Response
    {/* 
        return $this->render('demande_client/index.html.twig', [
            'demande_clients' => $demandeClientRepository->findAll(),
        ]); */

        return $this->render('demande/demande_dashboard.html.twig');
    }
    #[Route('/show_mes_demande', name: 'app_demande_show_mes_demandes', methods: ['GET'])]
    public function showmesdemandes(DemandeRepository $demandeClientRepository): Response
    {
        return $this->render('demande/mes_demandes.html.twig', [
            'demande_clients' => $demandeClientRepository->findmesdemandes($this->getUser()->getId()),
        ]);
    }

    #[Route('/show_all_demande', name: 'app_demande_show_all_demandes', methods: ['GET'])]
    public function showalldemandes(DemandeRepository $demandeClientRepository): Response
    {
        return $this->render('demande/index.html.twig', [
            'demande_clients' => $demandeClientRepository->findAll(),
        ]);
    }
    #[Route('/mesdemande', name: 'app_demande_client_showMyDemande', methods: ['GET'])]
    public function mesdemandes( DemandeRepository $demandeClientRepository): Response
    {
        $user=$this->getUser();
        $id=$user->getId();
        return $this->render('demande/mes_demandes.html.twig', [
            'demande_clients' => $demandeClientRepository->findmesdemandes($id),
        ]);
    }

    #[Route('/home', name: 'app_demande_client_home', methods: ['GET'])]
    public function home(DemandeRepository $demandeClientRepository, UserRepository $userRepository): Response
    {
        return $this->render('demande/liste_user.html.twig', [
            'users' => $userRepository->findAll(),
        ],);
    }

    #[Route('/{id}/show', name: 'app_demande_client_show', methods: ['GET'])]
    public function show(Demande $demandeClient): Response
    {
        return $this->render('demande/show.html.twig', [
            'demande_client' => $demandeClient,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_demande_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demande $demandeClient, DemandeRepository $demandeClientRepository): Response
    {
        $form = $this->createForm(DemandeType::class, $demandeClient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demandeClientRepository->save($demandeClient, true);

            return $this->redirectToRoute('app_demande_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('demande/edit.html.twig', [
            'demande_client' => $demandeClient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demande_client_delete', methods: ['POST'])]
    public function delete(Request $request, Demande $demandeClient, DemandeRepository $demandeClientRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $demandeClient->getId(), $request->request->get('_token'))) {
            $demandeClientRepository->remove($demandeClient, true);
        }

        return $this->redirectToRoute('app_demande_client_showMyDemande', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}/select', name: 'app_user_addDemande', methods: ['GET', 'POST'])]
    public function addDemande(User $user, Request $request, DemandeRepository $demandeClientRepository, UserRepository $userRepository): Response
    {

        $demandeClient = new Demande();
        $form = $this->createForm(DemandeType::class, $demandeClient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userconnected=$this->getUser();
            $idconnected=$userconnected->getId();
            $now = new DateTime();
            $demandeClient->setEtat("non étudié");
            $demandeClient->setDateCreation($now);
            $demandeClient->setIdFreelance($user->getId());
            $demandeClient->setIdClient($this->getUser());
            $demandeClientRepository->save($demandeClient, true);
            return $this->redirectToRoute('app_demande_show_mes_demandes', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('demande/new.html.twig', [
            'demande_client' => $demandeClient,
            'form' => $form,
            
        ]);
    }

   
}
