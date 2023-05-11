<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;



use Symfony\Component\HttpFoundation\Session\Session;
use App\Form\ForgotPasswordType;
use Doctrine\Persistence\ManagerRegistry;

use App\Form\UserType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordType;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }


     /**
     * @Route("/email1", name="email")
     */
    public function sendEmail(Request $request, MailerInterface $mailer,UserRepository $userRepository,TokenGeneratorInterface  $tokenGenerator): Response
    {

        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()) {
            $donnees = $form->getData();//


            $user = $userRepository->findOneBy(['mail'=>$donnees]);
            if(!$user) {
                $this->addFlash('danger','cette adresse n\'existe pas');
                return $this->render("User/forgotPassword.html.twig",['form'=>$form->createView()]);

            }
            $token = $tokenGenerator->generateToken();

            try{
                $user->setResetToken($token);
                $entityManger = $this->getDoctrine()->getManager();
                $entityManger->persist($user);
                $entityManger->flush();




            }catch(\Exception $exception) {
                $this->addFlash('warning','une erreur est survenue :'.$exception->getMessage());
                return $this->redirectToRoute("app_login");


            }

         $url = $this->generateUrl('app_reset_password',array('token'=>$token),UrlGeneratorInterface::ABSOLUTE_URL);
        $email = (new Email())
        ->from('roua.ounissi@esprit.tn')
        ->To($user->getMail())
        ->subject('Rénitialisation de mot de passe')
                ->text("<p> Bonjour</p> unde demande de réinitialisation de mot de passe a été effectuée. Veuillez cliquer sur le lien suivant :".$url,
                "text/html");
        // ->text('<p> Bonjour</p> unde demande de réinitialisation de mot de passe a été effectuée. Veuillez cliquer sur le lien suivant :".$url,
        // "text/html');
       
 try {
        $mailer->send($email);
        $this->addFlash('message','E-mail  de réinitialisation du mp envoyé :');
    } catch (TransportExceptionInterface $e) {
        // Gérer les erreurs d'envoi de courriel
    }

}
return $this->render("User/forgotPassword.html.twig",['form'=>$form->createView()]);


    }
 

    //méthode pour generer le lien de réinitialisation de mdp 

    /**
 * @Route("/resetpassword/{token}", name="app_reset_password")
 */
public function resetpassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder, ManagerRegistry $doctrine)
{
    $form = $this->createForm(ResetPasswordType::class);
    $user = $doctrine->getRepository(User::class)->findOneBy(['reset_token' => $token]);

    if ($user) {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
           
            $user->setResetToken(null);
            $user->setPassword($passwordEncoder->encodePassword($user, $form->get('password')->getData()));

            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('message', 'Mot de passe mis à jour :');
            return $this->redirectToRoute("app_login");
        }
    } else {
        $this->addFlash('message', 'Problème : utilisateur non trouvé.');
    }

    return $this->render("user/resetPassword.html.twig", ['form' => $form->createView()]);
}

       


       // if($request->isMethod('POST')) {
        //    $user->setResetToken(null);
           
         //   $user->setPassword($passwordEncoder->encodePassword($user,$request->request->get('password')));
         //   $entityManger = $this->getDoctrine()->getManager();
           // $entityManger->persist($user);
           // $entityManger->flush();

           // $this->addFlash('message','Mot de passe mis à jour :');
           // return $this->redirectToRoute("app_login");

        }
        /*else {
            return $this->render("user/resetPassword.html.twig",['token'=>$token]);

        }*/
    

