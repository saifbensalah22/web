<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class EventAPIController extends AbstractController
{
  


    
    private $entityManager;
    private $evenementRepository;

    public function __construct(EntityManagerInterface $entityManager, EvenementRepository $evenementRepository)
    {
        $this->entityManager = $entityManager;
        $this->evenementRepository = $evenementRepository;
    }




    
  





  #[Route('/getEvenementJson', name: 'getEvenementJson', methods: ['GET', 'POST'])]
  public function myEvenementApi(EntityManagerInterface $entityManager): Response
  {
      $repository = $entityManager->getRepository(Evenement::class);
      $data = $repository->createQueryBuilder('e')
          ->select('e.id,e.lieu_event, e.date_event, e.description_event ')
          ->getQuery()
          ->getArrayResult();

      return $this->json($data);
  }

  #[Route('/addEvenementJSON', name: 'addEvenementJSON', methods: ['GET', 'POST'])]
  public function addEvenementJSON( Request $request ,NormalizerInterface $normalizer ){
      $em=$this->getDoctrine()->getManager();
      $evenement=new Evenement();
      $evenement->setLieuEvent($request->get('lieu_event'));
      $evenement->setDateEvent(new \DateTime($request->get('date_event')));
      $evenement->setDescriptionEvent($request->get('description_event'));
     
   
    
      $em -> persist($evenement);
      $em->flush();
      $jsonContent=$normalizer->normalize($evenement, 'json', ['circular_reference_handler' => function ($object) {
          return $object->getId();
      }, 'max_depth' => 1]);
      return new Response("Evenement ajoutéé".json_encode($jsonContent));

  }




  #[Route('/editEvenementJSON', name: 'editEvenementJSON', methods: ['GET','POST'])]
  public function editEvenementJSON(Request $request, NormalizerInterface $normalizer)
  {
      $em = $this->getDoctrine()->getManager();
      $id = $request->get('id');
      $evenement = $em->getRepository(Evenement::class)->find($id);
  
      if (!$evenement) {
          return new Response("evenement with id $id not found");
      }
      $evenement->setLieuEvent($request->get('lieu_event'));
      $evenement->setDateEvent(new \DateTime($request->get('date_event')));
      $evenement->setDescriptionEvent($request->get('description_event'));
   
      $em -> persist($evenement);
      $em->flush();
  
      $jsonContent = $normalizer->normalize($evenement, 'json', [
          'circular_reference_handler' => function ($object) {
              return $object->getId();
          },
          'max_depth' => 1
      ]);
      return new Response("evenement updated" . json_encode($jsonContent));
  }
  





  #[Route('/deleteEvenementJson', name: 'deleteEvenementJson', methods: ['POST', 'DELETE'])]
  public function deleteEvenementJSON(Request $request, NormalizerInterface $normalizer)
  {
      $em = $this->getDoctrine()->getManager();
      $id = $request->get('id');
      $evenement = $em->getRepository(Evenement::class)->find($id);
  
      if ($request->isMethod('DELETE')) {
          $em->remove($evenement);
          $em->flush();
          return new Response("evenement deleted");
      } else {
          $em->persist($evenement);
          $em->flush();
          $jsonContent = $normalizer->normalize($evenement, 'json', [
              'circular_reference_handler' => function ($object) {
                  return $object->getId();
              },
              'max_depth' => 1
          ]);
          return new Response("evenement updated" . json_encode($jsonContent));
      }
  }



}
