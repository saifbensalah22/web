<?php

namespace App\Controller;
use App\Entity\ReponseReclamation;
use App\Repository\ReclamationRepository;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Snappy\Pdf;
use App\Entity\PdfGeneratorService;
use Dompdf\Dompdf;
use Dompdf\Options;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use MercurySeries\FlashyBundle\FlashyNotifier;


#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $reclamations = $entityManager
            ->getRepository(Reclamation::class)
            ->findAll();

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,FlashyNotifier $flashy,ReclamationRepository $repository): Response
    {
        $reclamation = new Reclamation();

        // get current system date as string
        $dateString = date('Y-m-d');
    
        // create DateTime object from string
        $date = \DateTime::createFromFormat('Y-m-d', $dateString);
    
        $reclamation->setDate($date); // set the date property of the $reservation object
    




        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reclamation);
            $entityManager->flush();
            $flashy->success('Addedd succefully', 'http://your-awesome-link.com');
            $repository->sms();
            $this->addFlash('danger','reclmation envoyée avec succées');
            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
        }
           
        #[Route('/front', name: 'app_reclamation_new_front', methods: ['GET', 'POST'])]
        public function newFront(Request $request, EntityManagerInterface $entityManager,FlashyNotifier $flashy,ReclamationRepository $repository): Response
        {
            $reclamation = new Reclamation();
            // get current system date as string
            $dateString = date('Y-m-d');
            
            // create DateTime object from string
            $date = \DateTime::createFromFormat('Y-m-d', $dateString);
        
            $reclamation->setDate($date); // set the date property of the $reservation object


            $form = $this->createForm(ReclamationType::class, $reclamation);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($reclamation);
                
                $entityManager->flush();
                $flashy->success('Addedd succefully', 'http://your-awesome-link.com');
               // $repository->sms();
                $this->addFlash('danger','reclmation envoyée avec succées');
                return $this->redirectToRoute('app_reclamation_new_front', [], Response::HTTP_SEE_OTHER);
            }

            return $this->renderForm('reclamation/Front.html.twig', [
                'reclamation' => $reclamation,
                'form' => $form,
            ]);
        }


        



    #[Route('/statistiqueR', name: 'statsR')]
    public function stat()
        {
    
            $repository = $this->getDoctrine()->getRepository(Reclamation::class);
            $reclamation= $repository->findAll();
    
            $em = $this->getDoctrine()->getManager();
    
    
            $ev1 = 0;
            $ev2 = 0;
            $ev3=0;
            $ev4=0;
    
    
            foreach ($reclamation as $reclamation) 
            {
               if ($reclamation->getSujet() =='Services')  
                {
                    $ev1 += 1;
                }
                elseif ($reclamation->getSujet() =='Cours')
                 {
                    $ev2 += 1;
                 }
                  elseif ($reclamation->getSujet() =='Evenement')
                 {
                    $ev3 += 1;
                 }
                else 
                 {
                    $ev4 +=1;
                 }
               
    
            }
    
            $pieChart = new PieChart();
            $pieChart->getData()->setArrayToDataTable(
                [['Prix', 'Nom'],
                    ['Services', $ev1],
                    ['Cours', $ev2],
                    ['Evenement', $ev3],
                    ['Autres', $ev4]
                ]
            );
            $pieChart->getOptions()->setTitle('statistique a partir les Sujets Reclamations');
            $pieChart->getOptions()->setHeight(1000);
            $pieChart->getOptions()->setWidth(1400);
            $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
            $pieChart->getOptions()->getTitleTextStyle()->setColor('green');
            $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
            $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
            $pieChart->getOptions()->getTitleTextStyle()->setFontSize(30);
    
           
    
            return $this->render('reclamation/stat.html.twig', array('piechart' => $pieChart));
        }
    
    /////////////////////////////////////---PDF---///////////////////////////////////////////////
    #[Route('/pdf/reclamation', name: 'generator_serviceR')]
    public function pdfService(): Response
    { 
        $reclamation= $this->getDoctrine()
        ->getRepository(Reclamation::class)
        ->findAll();



        $html =$this->renderView('pdf/indexReclamation.html.twig', ['reclamations' => $reclamation]);
        $pdfGeneratorService=new PdfGeneratorService();
        $pdf = $pdfGeneratorService->generatePdf($html);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
        ]);
        
    }

    ///////////////////////////////////////////////////////////////////////////////////
    #[Route('/rechercheB', name:'reclamation_rechercheB')]
    public function rechercheB(Request $request, ReclamationRepository $repository)
    {
        $data= $request->get('search');
        $reclamations=$repository->findBy(['email'=>$data]);


        return $this->render('reclamation/searchB.html.twig', [
            'reclamations'=>$reclamations]);

    }

    #[Route('/order_By_Email', name:'order_By_email' ,methods:['GET'])]
    public function order_By_Email(Request $request,ReclamationRepository $ReclamationRepository): Response
    {

        $ReclamationByEmail = $ReclamationRepository->order_By_Email();

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $ReclamationByEmail,
        ]);

        //trie selon Date normal

    }
    
    #[Route('/order_By_Description', name:'order_By_Description' ,methods:['GET'])]
    
    public function order_By_Description(Request $request,ReclamationRepository $ReclamationRepository): Response
    {
        $ReclamationByDescription = $ReclamationRepository->order_By_Description();

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $ReclamationByDescription,
        ]);

       

    }




















    
    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager,FlashyNotifier $flashy): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $flashy->success('Update succefully', 'http://your-awesome-link.com');
            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager,FlashyNotifier $flashy): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
            $flashy->success('Drop succefully', 'http://your-awesome-link.com');
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }
    
}
