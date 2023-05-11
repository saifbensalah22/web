<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Repository\CoursRepository;
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

class CoursAPIController extends AbstractController
{
 

    private $entityManager;
    private $coursRepository;

    public function __construct(EntityManagerInterface $entityManager, CoursRepository $coursRepository)
    {
        $this->entityManager = $entityManager;
        $this->coursRepository = $coursRepository;
    }




    
  





  #[Route('/getCourJson', name: 'getCourJson', methods: ['GET', 'POST'])]
  public function myCoursApi(EntityManagerInterface $entityManager): Response
  {
      $repository = $entityManager->getRepository(Cours::class);
      $data = $repository->createQueryBuilder('e')
          ->select('e.id,e.cours_name, e.nom_tuteur, e.description ,e.prix')
          ->getQuery()
          ->getArrayResult();

      return $this->json($data);
  }

  #[Route('/addCoursJSON', name: 'addCoursJSON', methods: ['GET', 'POST'])]
  public function addCoursJSON( Request $request ,NormalizerInterface $normalizer ){
      $em=$this->getDoctrine()->getManager();
      $cours=new Cours();
      $cours->setCoursName($request->get('cours_name'));
      $cours->setNomTuteur($request->get('nom_tuteur'));
      $cours->setDescription($request->get('description'));
      $cours->setPrix($request->get('prix'));
   
    
      $em -> persist($cours);
      $em->flush();
      $jsonContent=$normalizer->normalize($cours, 'json', ['circular_reference_handler' => function ($object) {
          return $object->getId();
      }, 'max_depth' => 1]);
      return new Response("cours ajoutéé".json_encode($jsonContent));

  }




  #[Route('/editCoursJSON', name: 'editCoursJSON', methods: ['GET','POST'])]
  public function editCoursJSON(Request $request, NormalizerInterface $normalizer)
  {
      $em = $this->getDoctrine()->getManager();
      $id = $request->get('id');
      $cours = $em->getRepository(Cours::class)->find($id);
  
      if (!$cours) {
          return new Response("cours with id $id not found");
      }
      $cours->setCoursName($request->get('cours_name'));
      $cours->setNomTuteur($request->get('nom_tuteur'));
      $cours->setDescription($request->get('description'));
      $cours->setPrix($request->get('prix'));
   
      $em -> persist($cours);
      $em->flush();
  
      $jsonContent = $normalizer->normalize($cours, 'json', [
          'circular_reference_handler' => function ($object) {
              return $object->getId();
          },
          'max_depth' => 1
      ]);
      return new Response("cours updated" . json_encode($jsonContent));
  }
  





  #[Route('/deleteCourJson', name: 'deleteCourJson', methods: ['POST', 'DELETE'])]
  public function deleteCoursJSON(Request $request, NormalizerInterface $normalizer)
  {
      $em = $this->getDoctrine()->getManager();
      $id = $request->get('id');
      $cours = $em->getRepository(Cours::class)->find($id);
  
      if ($request->isMethod('DELETE')) {
          $em->remove($cours);
          $em->flush();
          return new Response("cours deleted");
      } else {
          $em->persist($cours);
          $em->flush();
          $jsonContent = $normalizer->normalize($cours, 'json', [
              'circular_reference_handler' => function ($object) {
                  return $object->getId();
              },
              'max_depth' => 1
          ]);
          return new Response("cours updated" . json_encode($jsonContent));
      }
  }







}
