<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Entity\DemandeOffre;
use App\Form\DemandeOffreType;
use App\Repository\DemandeRepository;
use App\Repository\DemandeOffreRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;


#[Route('/demande/offre')]
class DemandeOffreController extends AbstractController
{
    #[Route('/', name: 'app_demande_offre_index', methods: ['GET'])]
    public function index(DemandeOffreRepository $offreDemandeRepository, DemandeRepository $demandeClientRepository): Response
    {
        return $this->render('demande_offre/index.html.twig', [
            'offre_demandes' => $offreDemandeRepository->findmesoffrede($this->getUser()->getId()),
        ]);
    }
    ///////////////////////////////////////////////////////////////////////
    #[Route('/{id}/reponse', name: 'app_offre_demande_repondre', methods: ['GET', 'POST'])]
    public function showmesdemandes(Demande $demandeClient, DemandeOffreRepository $offreDemandeRepository, Request $request, DemandeRepository $demandeClientRepository): Response
    {

        $reponseOffre = new DemandeOffre();
        $form = $this->createForm(DemandeOffreType::class, $reponseOffre);
        $form->handleRequest($request);
        //  $demande_id = $demandeClient->getId();
        $freelance_id = $demandeClient->getIdFreelance();
        if ($form->isSubmitted() && $form->isValid()) {
            $reponseOffre->setIdDemande($demandeClient);
            $reponseOffre->setIdFreelance($freelance_id);
            $demandeClient->setEtat("demande bien étudier ");
            $offreDemandeRepository->save($reponseOffre, true);
            $etat = $reponseOffre->getReponseDemande();
            $demandeClient->setEtat($etat);
            $demandeClientRepository->save($demandeClient, true);

            return $this->redirectToRoute('app_demande_offre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('demande_offre/new.html.twig', [
            'offre_demandes' => $reponseOffre,
            'form' => $form,
        ]);
    }

    #[Route('/affaire', name: 'app_demande_offre_affaire', methods: ['GET'])]
    public function mesaffaires(DemandeRepository $demandeClientRepository): Response
    {

        return $this->render('demande_offre/testListDesAffaires.html.twig', [
            'demande_clients' => $demandeClientRepository->findmesoffredetravail($this->getUser()->getId()),
        ]);
    }
    ///////////////////////////////////////////////////////////////////////
    
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }
    ///////////////////////////////////////////////////////////////////////

    #[Route('/new', name: 'app_demande_offre_new', methods: ['GET', 'POST'])]
    public function new(Request $request,Demande $demande,UserRepository $userRepository, MailerInterface $mailer ,DemandeOffreRepository $demandeOffreRepository): Response
    {
       /* $idFreelance = $demande->getIdFreelance();
        $from = $userRepository->chercherUserParId($idFreelance);
        $idClient = $demande->getIdClient();
        $to = $userRepository->chercherUserParId($idClient);
        $mailfrom = $from->getMail();
        $mailto = $to->getMail();
        //$idClient = $demandeRepository->findOneBy(['idClient' => $demandeClient->getIdClient()]);
        // $test = $idClient->getIdClient();
        //$idFreelance = $demandeRepository->findOneBy(['idFreelance' => $demandeClient->getIdFreelance()]);

       */
        $demandeOffre = new DemandeOffre();
        $form = $this->createForm(DemandeOffreType::class, $demandeOffre);
        $form->handleRequest($request);
        $email = new Email();

        if ($form->isSubmitted() && $form->isValid()) {
            $demandeOffreRepository->save($demandeOffre, true);
           // dd($mailto);

            $email->from('salim.bouazizi@esprit.tn')
            ->To('bouazizi.selim@gmail.com')
            ->subject('Reponse sur demande')
            ->text(
                "<p> Bonjour</p> unde demande de réinitialisation de mot de passe a été effectuée. Veuillez cliquer sur le lien suivant :"
            );
        // ->text('<p> Bonjour</p> unde demande de réinitialisation de mot de passe a été effectuée. Veuillez cliquer sur le lien suivant :".$url,
        // "text/html');

        try {
            $mailer->send($email);
            $this->addFlash('message', 'Reponse sur offre a été  envoyé :');
          } catch (TransportExceptionInterface $e) {
            // Gérer les erreurs d'envoi de courriel
            return $this->render("demande/demande_dashboard.html.twig");
        }
        return $this->redirectToRoute('app_demande_offre_affaire', [], Response::HTTP_SEE_OTHER);
     }

        return $this->renderForm('demande_offre/new.html.twig', [
            'demande_offre' => $demandeOffre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demande_offre_show', methods: ['GET'])]
    public function show(DemandeOffre $demandeOffre): Response
    {
        return $this->render('demande_offre/show.html.twig', [
            'demande_offre' => $demandeOffre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_demande_offre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DemandeOffre $demandeOffre, DemandeOffreRepository $demandeOffreRepository): Response
    {
        $form = $this->createForm(DemandeOffreType::class, $demandeOffre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demandeOffreRepository->save($demandeOffre, true);

            return $this->redirectToRoute('app_demande_offre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('demande_offre/edit.html.twig', [
            'demande_offre' => $demandeOffre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demande_offre_delete', methods: ['POST'])]
    public function delete(Request $request, DemandeOffre $demandeOffre, DemandeOffreRepository $demandeOffreRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $demandeOffre->getId(), $request->request->get('_token'))) {
            $demandeOffreRepository->remove($demandeOffre, true);
        }

        return $this->redirectToRoute('app_demande_offre_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/affaireAccepté', name: 'app_affaire_accepté')]
    public function accepted(DemandeOffreRepository $offreDemandeRepository): Response
    {
        $affaires=$offreDemandeRepository->findAccepted();
        return $this->render('demande_offre/affaireAccepté.html.twig', [
            'aff'=>$affaires,
        ]);
    }
}
