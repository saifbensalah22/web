<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Entity\Likes;
use App\Form\CoursType;
use App\Form\SeachCoursFormType;
use App\Repository\CoursRepository;
use App\Repository\LikesRepository;
use Doctrine\ORM\Mapping\Id;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Pusher\Pusher;
use stdClass;

#[Route('/cours')]
class CoursController extends AbstractController
{
    #[Route('/', name: 'app_cours_index', methods: ['GET'])]
    public function index(Request $request, CoursRepository $coursRepository): Response
    {
        $searchForm = $request->query->get('seach_cours_form');
        $search = $searchForm['search'] ?? null;
        $sort=$searchForm['sort'] ?? 'asc';
        $form = $this->createForm(SeachCoursFormType::class, null, ['search' => $search,'sort' => $sort, 'method' => 'GET',]);

        

        if ($search) {
            $qb = $coursRepository->createQueryBuilder('c');
            $qb->where(
                $qb->expr()->orX(
                    $qb->expr()->like('c.cours_name', ':search'),
                    $qb->expr()->like('c.description', ':search'),
                    $qb->expr()->like('c.nom_tuteur', ':search'),
                    $qb->expr()->like('c.contenu', ':search'),
                )
            );

            $qb->setParameter('search', '%' . $search . '%');
            $query = $qb->orderBy('c.prix', $sort == 'desc' ? 'desc' : 'asc')->getQuery();
            $results = $query->getResult();
        } else {
            $results = $coursRepository->findAllSortedByPrice($sort);
        }



        return $this->render('cours/index.html.twig', [
            'cours' => $results,
            'form' => $form->createView(),
            'sort' => $sort,
            'search' => $search

        ]);
    }


    #[Route('/like/{id}', name: 'front_cour_like_unlike', methods: ['POST', 'GET'])]
    public function like(Request $request, Cours $cour, LikesRepository $LikesRepository)
    {
        $user = $this->getUser();
        $isCoursAlreadyLiked = false;
        if ($user) {
            $em = $this->getDoctrine()->getManager();
            $isCoursAlreadyLiked = $em->getRepository(Likes::class)->findOneBy([
                'user' => $user,
                'cours' => $cour
            ]);
            if (!$isCoursAlreadyLiked) {
                $like = new Likes();
                $like->setCours($cour);
                $like->setUser($user);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($like);
                $entityManager->flush();
                return new JsonResponse([
                    'success' => true,
                    'like' => $like
                ]);
            } else {

                $like = $em->getRepository(Likes::class)->findOneBy([
                    'user' => $user,
                    'cours' => $cour
                ]);

                if ($like) {
                    // Element found
                    $LikesRepository->remove($like, true);
                } else {
                    // Element not found
                }


                return new JsonResponse([
                    'success' => true,
                    'like' => $like
                ]);
            }
        }
        return new JsonResponse([
            'success' => false,
        ]);
    }
    #[Route('/tuteurCours', name: 'app_cours_listCours', methods: ['GET'])]
    public function tuteur(CoursRepository $coursRepository): Response
    {
        $user = $this->getUser();
        return $this->render('cours/listCours.html.twig', [
            'cours' => $coursRepository->findBy(['id_user' => $user]),

        ]);
    }
    #[Route('/new', name: 'app_cours_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CoursRepository $coursRepository, SluggerInterface $slugger): Response
    {
        $cour = new Cours();
        $form = $this->createForm(CoursType::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $request->files->get('cours')['upload'];
            // dd($request);
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $file->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'filename' property to store the PDF file name
                // instead of its contents
                $cour->setUpload($newFilename);
                $user = $this->getUser();

                $cour->setIdUser($user);
            }

            $coursRepository->save($cour, true);


            $options = array(
                'cluster' => 'eu',
                'useTLS' => true
            );
            /*$pusher = new Pusher(
                'cb8d2c2cd750b6babfc7',
                '2966aa4022085930c0be',
                '1591339',
                $options
            );*/
            $coursObject = new stdClass();
            $coursObject->id = $cour->getId();
            $coursObject->contenu = $cour->getContenu();
            $coursObject->cours_name = $cour->getCoursName();
            $coursObject->nom_tuteur = $cour->getNomTuteur();
            $coursObject->description = $cour->getDescription();
            $coursObject->prix = $cour->getPrix();
            $coursObject->upload = $cour->getUpload();
            $coursObject->occurence = $cour->getOccurence();
            $coursObject->id_user = $cour->getIdUser()->getId();
            $coursObject->testCours = $cour->getTestCours();
            // $coursObject = json_encode($cour);
            $data['message'] = $coursObject;
           // $pusher->trigger('my-channel', 'my-event', $data);
            return $this->redirectToRoute('app_cours_listCours', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cours/new.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cours_show', methods: ['GET'])]
    public function show(Cours $cour): Response
    {

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $isCoursAlreadyLiked = false;
        if ($user)
            $isCoursAlreadyLiked = $em->getRepository(Likes::class)->findOneBy([
                'user' => $user,
                'cours' => $cour
            ]);
        $likes = count($em->getRepository(Likes::class)->findBy([
            'cours' => $cour
        ]));
        return $this->render('cours/show.html.twig', [
            'cour' => $cour,
            'isCoursAlreadyLiked' => $isCoursAlreadyLiked ? true : false,
            'likes' => $likes

        ]);
    }
    #[Route('/tuteur/{id}', name: 'app_cours_tuteur_show', methods: ['GET'])]
    public function tuhow(Cours $cour): Response
    {
        return $this->render('cours/tuteurshow.html.twig', [
            'cour' => $cour,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cours_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cours $cour, CoursRepository $coursRepository): Response
    {
        $form = $this->createForm(CoursType::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coursRepository->save($cour, true);

            return $this->redirectToRoute('app_cours_listCours', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cours/edit.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cours_delete', methods: ['POST'])]
    public function delete(Request $request, Cours $cour, CoursRepository $coursRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cour->getId(), $request->request->get('_token'))) {
            $coursRepository->remove($cour, true);
        }

        return $this->redirectToRoute('app_cours_listCours', [], Response::HTTP_SEE_OTHER);
    }
}
