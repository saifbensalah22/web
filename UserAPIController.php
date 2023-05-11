<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;



class UserAPIController extends AbstractController
{

    private $entityManager;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }




    #[Route('/registerUserJSON', name: 'registerUserJSON', methods: ['GET', 'POST'])]
public function addUserJSON(Request $request, NormalizerInterface $normalizer, UserPasswordEncoderInterface $passwordEncoder)
{
    $em = $this->getDoctrine()->getManager();
    $user = new User();
    $user->setMail($request->get('mail'));
    $user->setNom($request->get('nom'));
    $password = $passwordEncoder->encodePassword($user, $request->get('password'));
    $user->setPassword($password);
    $user->setRole('USER ROLE');
   
    $em->persist($user);
    $em->flush();
    
    $jsonContent = $normalizer->normalize($user, 'json', ['circular_reference_handler' => function ($object) {
        return $object->getId();
    }, 'max_depth' => 1]);
    return new Response("user ajoutéé" . json_encode($jsonContent));
}

#[Route('/loginJSON', name: 'loginJSON', methods: ['GET','POST'])]
public function signinAction1(Request $request, UserPasswordEncoderInterface $passwordEncoder, NormalizerInterface $normalizer)
{
    $em = $this->getDoctrine()->getManager();

    $email = $request->get('mail');
    $password = $request->get('password');

    $user = $em->getRepository(User::class)->findOneBy(['mail' => $email]);

    if ($user) {
        if ($passwordEncoder->isPasswordValid($user, $password)) {
            $jsonContent = $normalizer->normalize($user, 'json', ['circular_reference_handler' => function ($object) {
                return $object->getId();
            }, 'max_depth' => 1]);
            return new Response(json_encode($jsonContent));
        } else {
            return new Response("Invalid password");
        }
    } else {
        return new Response("User not found");
    }
}


#[Route('/resetPasswordJSON', name: 'resetPasswordJSON', methods: ['POST'])]
public function resetPasswordJSON(Request $request, NormalizerInterface $normalizer, UserPasswordEncoderInterface $passwordEncoder)
{
    $em = $this->getDoctrine()->getManager();
    $user = $em->getRepository(User::class)->findOneBy(['mail' => $request->get('mail')]);

    if (!$user) {
        return new Response("User not found with mail " . $request->get('mail'), Response::HTTP_NOT_FOUND);
    }

    // Set password to "changeMe"
    $newPassword = "changeMe";
    $password = $passwordEncoder->encodePassword($user, $newPassword);
    $user->setPassword($password);

    $em->persist($user);
    $em->flush();

    $jsonContent = $normalizer->normalize($user, 'json', ['circular_reference_handler' => function ($object) {
        return $object->getId();
    }, 'max_depth' => 1]);
    return new Response("Password reset for user with mail " . $request->get('mail') . ". New password: " . $newPassword . ". User details: " . json_encode($jsonContent));
}

    



#[Route('/getUser1', name: 'getUser1', methods: ['GET', 'POST'])]
public function myApi(EntityManagerInterface $entityManager): Response
{
    $repository = $entityManager->getRepository(User::class);
    $data = $repository->createQueryBuilder('e')
        ->select('e.id,e.nom, e.mail' )
        ->getQuery()
        ->getArrayResult();

    return $this->json($data);
}



 
#[Route('/editUserJSON1', name: 'editUserJSON1', methods: ['GET','POST'])]
public function editUserJSON(Request $request, NormalizerInterface $normalizer)
{
    $em = $this->getDoctrine()->getManager();
    $id = $request->get('id');
    $user = $em->getRepository(User::class)->find($id);

    if (!$user) {
        return new Response("user with id $id not found");
    }

    $user->setNom($request->get('nom'));

    $user->setMail($request->get('mail'));
 
  
    $em -> persist($user);
    $em->flush();

    $jsonContent = $normalizer->normalize($user, 'json', [
        'circular_reference_handler' => function ($object) {
            return $object->getId();
        },
        'max_depth' => 1
    ]);
    return new Response("user updated" . json_encode($jsonContent));
}



#[Route('/jsonardelete1', name: 'jsonardelete1', methods: ['POST', 'DELETE'])]
public function deleteUserJSON(Request $request, NormalizerInterface $normalizer)
{
    $em = $this->getDoctrine()->getManager();
    $id = $request->get('id');
    $user = $em->getRepository(User::class)->find($id);

    if ($request->isMethod('DELETE')) {
        $em->remove($user);
        $em->flush();
        return new Response("user deleted");
    } else {
        $em->persist($user);
        $em->flush();
        $jsonContent = $normalizer->normalize($user, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            },
            'max_depth' => 1
        ]);
        return new Response("user updated" . json_encode($jsonContent));
    }
}




}
