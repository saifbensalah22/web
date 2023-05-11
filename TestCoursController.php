<?php

namespace App\Controller;

use App\Entity\TestCours;
use App\Form\TestCoursType;
use App\Repository\TestCoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/test/cours')]
class TestCoursController extends AbstractController
{
    #[Route('/', name: 'app_test_cours_index', methods: ['GET'])]
    public function index(TestCoursRepository $testCoursRepository): Response
    {
        return $this->render('test_cours/index.html.twig', [
            'test_cours' => $testCoursRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_test_cours_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TestCoursRepository $testCoursRepository): Response
    {
        $testCour = new TestCours();
        $form = $this->createForm(TestCoursType::class, $testCour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $testCoursRepository->save($testCour, true);

            return $this->redirectToRoute('app_test_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('test_cours/new.html.twig', [
            'test_cour' => $testCour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_test_cours_show', methods: ['GET'])]
    public function show(TestCours $testCour): Response
    {
        return $this->render('test_cours/show.html.twig', [
            'test_cour' => $testCour,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_test_cours_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TestCours $testCour, TestCoursRepository $testCoursRepository): Response
    {
        $form = $this->createForm(TestCoursType::class, $testCour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $testCoursRepository->save($testCour, true);

            return $this->redirectToRoute('app_test_cours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('test_cours/edit.html.twig', [
            'test_cour' => $testCour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_test_cours_delete', methods: ['POST'])]
    public function delete(Request $request, TestCours $testCour, TestCoursRepository $testCoursRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$testCour->getId(), $request->request->get('_token'))) {
            $testCoursRepository->remove($testCour, true);
        }

        return $this->redirectToRoute('app_test_cours_index', [], Response::HTTP_SEE_OTHER);
    }
}
