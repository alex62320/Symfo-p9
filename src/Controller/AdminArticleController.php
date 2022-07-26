<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Writer;
use App\Repository\WriterRepositoy;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\WriterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin/article')]
class AdminArticleController extends AbstractController
{

    private WriterRepository $writerRepository;

    public function __construct(WriterRepository $writerRepository)
    {
        $this->writerRepository = $writerRepository;
    } 

    #[Route('/', name: 'app_admin_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        $user = $this->getUser();

        // les utilisateur non identifier sont renvoyé a la page de login
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $articles = [];

         if ($this->isGranted('ROLE_EDITOR')) {
            $articles = $articleRepository->findAll();
         }elseif ($this->isGranted('ROLE_WRITER')){
            $writer = $this->writerRepository->findByUser($user);
            $articles = $writer->getArticles();
         }

        return $this->render('admin_article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'app_admin_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArticleRepository $articleRepository): Response
    {
        // les utilisateur non identifier sont renvoyé a la page de login
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');   

        // méthode alternative à la méthode denyAccesUnlessGaranted
        // if (!$this->isGranted('IS_AUTHENTICATED_FULLY')){
        //     throw new AccessDeniedException();
        // }

        if (!$this->isGranted('ROLE_EDITOR')&& !$this->isGranted('ROLE_WRITER')){
            // le redeacteur c'est pas le proprietaire de l'article
            throw new AccessDeniedException();
            // genere une erreur 404
            // throw $this->createNotFoundException('The product does not exist');
        }
    

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articleRepository->add($article, true);

            return $this->redirectToRoute('app_admin_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin_article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        // appel de la fonction qui filtre les utilisateur
        $this->filterUser($article);

        return $this->render('admin_article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        $this->filterUser($article);

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articleRepository->add($article, true);

            return $this->redirectToRoute('app_admin_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin_article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        // appel de la fonction qui filtre les utilisateur
        $this->filterUser($article);

        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $articleRepository->remove($article, true);
        }

        return $this->redirectToRoute('app_admin_article_index', [], Response::HTTP_SEE_OTHER);
    }
    private function filterUser(Article $article)
    {
        // les utilisateur non identifier sont renvoyé a la page de login
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');   

        // méthode alternative à la méthode denyAccesUnlessGaranted
        // if (!$this->isGranted('IS_AUTHENTICATED_FULLY')){
        //     throw new AccessDeniedException();
        // }

        if (!$this->isGranted('ROLE_EDITOR')&& $this->isGranted('ROLE_WRITER')){
            $user = $this->getUser();
            $writer = $this->writerRepository->findByUser($user);
            $articles = $writer->getArticles();
            if (!$articles->contains($article)){
                // le redeacteur c'est pas le proprietaire de l'article
                throw new AccessDeniedException();
                // genere une erreur 404
                // throw $this->createNotFoundException('The product does not exist');
            }
        }
    }
}
