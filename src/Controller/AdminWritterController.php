<?php

namespace App\Controller;

use App\Entity\Writer;
use App\Form\WriterType;
use App\Repository\WriterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/writter')]
class AdminWritterController extends AbstractController
{
    #[Route('/', name: 'app_admin_writter_index', methods: ['GET'])]
    public function index(WriterRepository $writerRepository): Response
    {
        return $this->render('admin_writter/index.html.twig', [
            'writers' => $writerRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_writter_new', methods: ['GET', 'POST'])]
    public function new(Request $request, WriterRepository $writerRepository): Response
    {
        $writer = new Writer();
        $form = $this->createForm(WriterType::class, $writer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $writerRepository->add($writer, true);

            return $this->redirectToRoute('app_admin_writter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin_writter/new.html.twig', [
            'writer' => $writer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_writter_show', methods: ['GET'])]
    public function show(Writer $writer): Response
    {
        return $this->render('admin_writter/show.html.twig', [
            'writer' => $writer,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_writter_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Writer $writer, WriterRepository $writerRepository): Response
    {
        $form = $this->createForm(WriterType::class, $writer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $writerRepository->add($writer, true);

            return $this->redirectToRoute('app_admin_writter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin_writter/edit.html.twig', [
            'writer' => $writer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_writter_delete', methods: ['POST'])]
    public function delete(Request $request, Writer $writer, WriterRepository $writerRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$writer->getId(), $request->request->get('_token'))) {
            $writerRepository->remove($writer, true);
        }

        return $this->redirectToRoute('app_admin_writter_index', [], Response::HTTP_SEE_OTHER);
    }
}
