<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Repository\ReclamationRepository;
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

class ReclamationApiController extends AbstractController
{
    private $entityManager;
    private $reclamationRepository;

    public function __construct(EntityManagerInterface $entityManager, ReclamationRepository $reclamationRepository)
    {
        $this->entityManager = $entityManager;
        $this->reclamationRepository = $reclamationRepository;
    }




    
  





  #[Route('/getRecJSON', name: 'getRecJSON', methods: ['GET', 'POST'])]
  public function myApi(EntityManagerInterface $entityManager): Response
  {
      $repository = $entityManager->getRepository(Reclamation::class);
      $data = $repository->createQueryBuilder('e')
          ->select('e.id,e.email, e.date , e.telephone, e.description')
          ->getQuery()
          ->getArrayResult();

      return $this->json($data);
  }

  #[Route('/addRecJSON', name: 'addRecJSON', methods: ['GET', 'POST'])]
  public function addRecJSON( Request $request ,NormalizerInterface $normalizer ){
      $em=$this->getDoctrine()->getManager();
      $reclamation=new Reclamation();
      $reclamation->setEmail($request->get('email'));
      $reclamation->setDate(new \DateTime($request->get('date')));
      $reclamation->setTelephone($request->get('telephone'));
      $reclamation->setDescription($request->get('description'));
     
    
      $em -> persist($reclamation);
      $em->flush();
      $jsonContent=$normalizer->normalize($reclamation, 'json', ['circular_reference_handler' => function ($object) {
          return $object->getId();
      }, 'max_depth' => 1]);
      return new Response("reclamation ajoutéé".json_encode($jsonContent));

  }




  #[Route('/editRecJSON', name: 'editRecJSON', methods: ['GET','POST'])]
  public function editRecJSON(Request $request, NormalizerInterface $normalizer)
  {
      $em = $this->getDoctrine()->getManager();
      $id = $request->get('id');
      $reclamation = $em->getRepository(Reclamation::class)->find($id);
  
      if (!$reclamation) {
          return new Response("Reclamation with id $id not found");
      }
  
      $reclamation->setEmail($request->get('email'));
      $reclamation->setDate(new \DateTime($request->get('date')));
      $reclamation->setTelephone($request->get('telephone'));
      $reclamation->setDescription($request->get('description'));
   
      $em -> persist($reclamation);
      $em->flush();
  
      $jsonContent = $normalizer->normalize($reclamation, 'json', [
          'circular_reference_handler' => function ($object) {
              return $object->getId();
          },
          'max_depth' => 1
      ]);
      return new Response("Reclamation updated" . json_encode($jsonContent));
  }
  





  #[Route('/deleterec', name: 'deleterec', methods: ['POST', 'DELETE'])]
  public function deleteRecJSON(Request $request, NormalizerInterface $normalizer)
  {
      $em = $this->getDoctrine()->getManager();
      $id = $request->get('id');
      $reclamation = $em->getRepository(Reclamation::class)->find($id);
  
      if ($request->isMethod('DELETE')) {
          $em->remove($reclamation);
          $em->flush();
          return new Response("Reclamation deleted");
      } else {
          $em->persist($reclamation);
          $em->flush();
          $jsonContent = $normalizer->normalize($reclamation, 'json', [
              'circular_reference_handler' => function ($object) {
                  return $object->getId();
              },
              'max_depth' => 1
          ]);
          return new Response("Reclamation updated" . json_encode($jsonContent));
      }
  }

}
