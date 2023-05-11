<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Knp\Snappy\Pdf;
use App\Entity\PdfGeneratorService;
use Dompdf\Dompdf;
use Dompdf\Options;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/evenement')]
class EvenementController extends AbstractController
{
    #[Route('/', name: 'app_evenement_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $evenements = $entityManager
            ->getRepository(Evenement::class)
            ->findAll();

        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
        ]);
    }
   

    #[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $evenement->getImage();
            $filename = md5(uniqid()).'.'.$file->guessExtension();
            $file->move($this->getParameter('uploads'),$filename);
            $evenement->setImage($filename);
            
        
            $entityManager->persist($evenement);
            $entityManager->flush();

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }
    #[Route('/eventFront', name: 'app_evenement_index_front', methods: ['GET'])]
    public function indexFront(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        $evenements = $entityManager
            ->getRepository(Evenement::class)
            ->findAll();
    
        $pagination = $paginator->paginate(
            $evenements, /* les données à paginer */
            $request->query->getInt('page', 1), /* le numéro de page par défaut */
            3 /* le nombre d'éléments par page */
        );
    
        return $this->render('evenement/front_event.html.twig', [
            'pagination' => $pagination,
        ]);
    }
    
    ///////////////////---PDF---////////////////////
    #[Route('/pdf/evenement', name: 'generator_service')]
    public function pdfService(): Response
    { 
        $evenement= $this->getDoctrine()
        ->getRepository(Evenement::class)
        ->findAll();



        $html =$this->renderView('pdf/indexEvenement.html.twig', ['evenements' => $evenement]);
        $pdfGeneratorService=new PdfGeneratorService();
        $pdf = $pdfGeneratorService->generatePdf($html);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
        ]);
        
    }
    #[Route('/statistiqueE', name: 'statsE')]
    public function stat()
        {
    
            $repository = $this->getDoctrine()->getRepository(Evenement::class);
            $evenement= $repository->findAll();
    
            $em = $this->getDoctrine()->getManager();
    
    
            $ev1 = 0;
            $ev2 = 0;
            $ev3=0;
    
    
            foreach ($evenement as $evenement) 
            {
               if ($evenement->getLieuEvent() =='Tunis')  
                {
                    $ev1 += 1;
                }
                elseif ($evenement->getLieuEvent() =='Sousse')
                 {
                    $ev2 += 1;
                 }
                else 
                 {
                    $ev3 +=1;
                 }
               
    
            }
    
            $pieChart = new PieChart();
            $pieChart->getData()->setArrayToDataTable(
                [['Prix', 'Nom'],
                    ['Tunis ', $ev1],
                    ['Sousse', $ev2],
                    ['autres', $ev3]
                ]
            );
            $pieChart->getOptions()->setTitle('statistique a partir des lieus evenements');
            $pieChart->getOptions()->setHeight(1000);
            $pieChart->getOptions()->setWidth(1400);
            $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
            $pieChart->getOptions()->getTitleTextStyle()->setColor('green');
            $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
            $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
            $pieChart->getOptions()->getTitleTextStyle()->setFontSize(30);
    
           
    
            return $this->render('evenement/stat.html.twig', array('piechart' => $pieChart));

        }

        #[Route('/recherche', name:'evenement_recherche')]
    public function recherche(Request $request, EvenementRepository $repository)
    {
        $data= $request->get('search');
        $evenements=$repository->findBy(['descriptionEvent'=>$data]);


        return $this->render('evenement/search.html.twig', [
            'evenements'=>$evenements]);

    } 
    #[Route('/rechercheB', name:'evenement_rechercheB')]
    public function rechercheB(Request $request, EvenementRepository $repository)
    {
        $data= $request->get('search');
        $evenements=$repository->findBy(['descriptionEvent'=>$data]);


        return $this->render('evenement/searchB.html.twig', [
            'evenements'=>$evenements]);

    }

    #[Route('/order_By_Date', name:'order_By_Date' ,methods:['GET'])]
    public function order_By_Date(Request $request,EvenementRepository $EvenementRepository): Response
    {

        $EvenementByDate = $EvenementRepository->order_By_Date();

        return $this->render('evenement/index.html.twig', [
            'evenements' => $EvenementByDate,
        ]);

        //trie selon Date normal

    }
    
    #[Route('/order_By_Lieu', name:'order_By_Lieu' ,methods:['GET'])]
    
    public function order_By_Lieu(Request $request,EvenementRepository $EvenementRepository): Response
    {
        $EvenementByLieu = $EvenementRepository->order_By_Lieu();

        return $this->render('evenement/index.html.twig', [
            'evenements' => $EvenementByLieu,
        ]);

       

    }









        ///////////////////////////////////--MAP--////////////////////////////////////////////////
    #[Route('/show_in_map/{idevenement}', name: 'app_evenement_map', methods: ['GET'])]
    public function Map( Evenement $idevenement,EntityManagerInterface $entityManager ): Response
    {

        $evenement = $entityManager
            ->getRepository(Evenement::class)->findBy( 
                ['idevenement'=>$idevenement ]
            );
        return $this->render('evenement/api_arcgis.html.twig', [
            'evenements' => $evenement,
        ]);

    }

    /////////////////////////////////////////////////////////////////////////
    

  
    
    #[Route('/{id}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $evenement->getImage();
            if ($file) {
                $filename = md5(uniqid()).'.'.$file->guessExtension();
                $file->move($this->getParameter('uploads'),$filename);
                $evenement->setImage($filename);
            }
            $entityManager->flush();
        
            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }
}
