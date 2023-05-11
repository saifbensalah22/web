<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Repository\DemandeRepository;
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

class DemandeAPIController extends AbstractController
{


    
    private $entityManager;
    private $demandeRepository;

    public function __construct(EntityManagerInterface $entityManager, DemandeRepository $demandeRepository)
    {
        $this->entityManager = $entityManager;
        $this->demandeRepository = $demandeRepository;
    }




    
  





  #[Route('/getDemandeJson', name: 'getDemandeJson', methods: ['GET', 'POST'])]
  public function myDemandeApi(EntityManagerInterface $entityManager): Response
  {
      $repository = $entityManager->getRepository(Demande::class);
      $data = $repository->createQueryBuilder('e')
          ->select('e.id ,e.titre, e.description, e.competence ,e.prix,e.etat ,e.dateLimite, e.dateCreation')
          ->getQuery()
          ->getArrayResult();

      return $this->json($data);
  }

  #[Route('/addDemandeJSON', name: 'addDemandeJSON', methods: ['GET', 'POST'])]
  public function addDemandeJSON( Request $request ,NormalizerInterface $normalizer ){
      $em=$this->getDoctrine()->getManager();
      $demande=new Demande();
      $demande->settitre($request->get('titre'));
      $demande->setDescription($request->get('description'));
      $demande->setCompetence($request->get('competence'));
      $demande->setPrix($request->get('prix'));
      $demande->setEtat($request->get('etat'));
      $demande->setDateLimite(new \DateTime($request->get('dateLimite')));
      $demande->setDateCreation(new \DateTime($request->get('dateCreation')));
   
    
      $em -> persist($demande);
      $em->flush();
      $jsonContent=$normalizer->normalize($demande, 'json', ['circular_reference_handler' => function ($object) {
          return $object->getId();
      }, 'max_depth' => 1]);
      return new Response("demande ajoutéé".json_encode($jsonContent));

  }




  #[Route('/editDemandeJSON', name: 'editDemandeJSON', methods: ['GET','POST'])]
  public function editDemandeJSON(Request $request, NormalizerInterface $normalizer)
  {
      $em = $this->getDoctrine()->getManager();
      $id = $request->get('id');
      $demande = $em->getRepository(Demande::class)->find($id);
  
      if (!$demande) {
          return new Response("demande with id $id not found");
      }
      $demande->settitre($request->get('titre'));
      $demande->setDescription($request->get('description'));
      $demande->setCompetence($request->get('competence'));
      $demande->setPrix($request->get('prix'));
      $demande->setEtat($request->get('etat'));
      $demande->setDateLimite(new \DateTime($request->get('dateLimite')));
      $demande->setDateCreation(new \DateTime($request->get('dateCreation')));
   
      $em -> persist($demande);
      $em->flush();
  
      $jsonContent = $normalizer->normalize($demande, 'json', [
          'circular_reference_handler' => function ($object) {
              return $object->getId();
          },
          'max_depth' => 1
      ]);
      return new Response("demande updated" . json_encode($jsonContent));
  }
  





  #[Route('/deleteDemandeJson', name: 'deleteDemandeJson', methods: ['POST', 'DELETE'])]
  public function deleteDemandeJSON(Request $request, NormalizerInterface $normalizer)
  {
      $em = $this->getDoctrine()->getManager();
      $id = $request->get('id');
      $demande = $em->getRepository(Demande::class)->find($id);
  
      if ($request->isMethod('DELETE')) {
          $em->remove($demande);
          $em->flush();
          return new Response("demande deleted");
      } else {
          $em->persist($demande);
          $em->flush();
          $jsonContent = $normalizer->normalize($demande, 'json', [
              'circular_reference_handler' => function ($object) {
                  return $object->getId();
              },
              'max_depth' => 1
          ]);
          return new Response("demande updated" . json_encode($jsonContent));
      }
  }



}
