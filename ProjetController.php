<?php

namespace App\Controller;
use App\Repository\ProjetRepository;
use App\Entity\User;
use App\Entity\Projet;
use App\Form\ProjetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use App\Entity\Offre;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/projet')]
class ProjetController extends AbstractController
{

    private EntityManagerInterface $entityManager;

public function __construct(EntityManagerInterface $entityManager)
{
    $this->entityManager = $entityManager;
}

    #[Route('/', name: 'app_projet_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $projets = $entityManager
            ->getRepository(Projet::class)
            ->findAll();

        return $this->render('projet/index.html.twig', [
            'projets' => $projets,
        ]);
    }
    /**
 * @Route("/stat", name="projet_statistics")
 */
public function stats(ProjetRepository $repository): Response
{
    $topProjects = $repository->findTopProjects(5);

    return $this->render('projet/stat.html.twig', [
        'topProjects' => $topProjects,
    ]);
}
    #[Route('/new', name: 'app_projet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet); //generer une formulaire de lentity projet
        $form->handleRequest($request);
        $userConnected=$this->getUser();
        




        if ($form->isSubmitted() && $form->isValid()) {
            $projet->setIdUser($userConnected);
            $entityManager->persist($projet);
            $entityManager->flush();
            $x = $projet->getIduser(); // prendre l'id de l'utilisateur qui a fait le projet
            $y = $projet->getTitre(); //prendre le titre de projet
        $queryBuilder = $this->entityManager->createQueryBuilder(); // requete sql pour selectionner le createur de projet
        $email = $queryBuilder // on vas prendre le email de l'utilisateur
            ->select('u.mail')
            ->from(User::class, 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $x)
            ->getQuery()
            ->getSingleScalarResult();
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
            $mail = new PHPMailer(true);
        try {
            // set up the SMTP connection
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = 'smtp.office365.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'freelanci@outlook.com';
            $mail->Password = '21933005@r';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            
            // set up the email message
            $mail->setFrom('freelanci@outlook.com', 'Dorra ferah');
            $mail->addAddress($email);
            $mail->Subject = 'Projet';
            $mail->Body = $nom .' ' . $prenom . ' votre projet sous le nom ' . $y .  ' a etait cree.';
            
            // send the email
            $mail->send();
            echo 'Email sent successfully.';
        } catch (Exception $e) {
            echo 'Email could not be sent. Error message: ', $mail->ErrorInfo;
        }
            return $this->redirectToRoute('app_projet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('projet/new.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id_projet}', name: 'app_projet_show', methods: ['GET'])]
    public function show(Projet $projet): Response
    {
        $queryBuilder = $this->entityManager->createQueryBuilder(); // requete sql pour selectionner le createur de projet
        $x =10;
        $x = $queryBuilder // on vas prendre le email de l'utilisateur
        ->select('COUNT(o)')
        ->from(Offre::class, 'o')
        ->andWhere('o.id_projet = :id_projet')
        ->setParameter('id_projet', $projet->getIdProjet())
        ->getQuery()
        ->getSingleScalarResult();
        
        $queryBuilder = $this->entityManager->createQueryBuilder();

// Query to get Offre entities related to the project
$offres = $queryBuilder
    ->select('o')
    ->from(Offre::class, 'o')
    ->where('o.id_projet = :id_projet')
    ->setParameter('id_projet', $projet->getIdProjet())
    ->getQuery()
    ->getResult();

// Extract idUser values from the Offre entities
$idUsers = array_map(function ($offre) {
    return $offre->getIdUser();
}, $offres);

$queryBuilder = $this->entityManager->createQueryBuilder();

// Query to get User entities based on the extracted idUser values
$users = $queryBuilder
    ->select('u')
    ->from(User::class, 'u')
    ->where('u.id IN (:id_users)')
    ->setParameter('id_users', $idUsers)
    ->getQuery()
    ->getResult();



        return $this->render('projet/show.html.twig', [
            'projet' => $projet,
            'x' =>$x,
            'users' => $users
        
        ]);
    }

    #[Route('/{id_projet}/edit', name: 'app_projet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id_projet, EntityManagerInterface $entityManager): Response
    {
        $projet = $entityManager
        ->getRepository(Projet::class)
        ->find($id_projet);

        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_projet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('projet/edit.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }
    
    #[Route('/{id_projet}', name: 'app_projet_delete', methods: ['POST'])]
    public function delete(Request $request, $id_projet, EntityManagerInterface $entityManager): Response
    {
        $projet = $entityManager
        ->getRepository(Projet::class)
        ->find($id_projet);

        if ($this->isCsrfTokenValid('delete'.$projet->getIdProjet(), $request->request->get('_token'))) {
            $entityManager->remove($projet);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_projet_index', [], Response::HTTP_SEE_OTHER);
    }
    

    

}
