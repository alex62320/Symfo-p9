<?php

namespace App\Security;

use App\Repository\WriterRepository;
use App\Repository\EditorRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private WriterRepository $writerRepository;
    private EditorRepository $editorRepository;


    public function __construct(UrlGeneratorInterface $urlGenerator, WriterRepository $writerRepository, EditorRepository $editorRepository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->editorRepository = $editorRepository;
        $this->writerRepository = $writerRepository;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // For example:
        // return new RedirectResponse($this->urlGenerator->generate('some_route'));
        
        dump($token);

        // récupération du compte de l'utilisateur qui viens de se connecter
        $user = $token->getUser();
        // récupération de la liste de ses rôles
        // note : tous les utilisateur connectés possèdent le rôle 'ROLE_USER'
        $roles = $user->getRoles();
        // méthode alternative pour récupéré les roles
        // $roles = $token->getRoleNames();
        
        if (in_array('ROLE_EDITOR', $roles)){
            $editor = $this->editorRepository->findByUser($user);
            // il est possible de récupérer des informations sur le profil
            // $id = $editor->getId();

            return new RedirectResponse($this->urlGenerator->generate('app_admin_article_index'));
        } elseif (in_array('ROLE_WRITER', $roles)){
            $writer = $this->writerRepository->findByUser($user);
            // il est possible de récupérer des informations sur le profil
            // $articles = $editor->getArticles();

            return new RedirectResponse($this->urlGenerator->generate('app_front_index'));
        }

        // la redirection par défault pour les utilisateur qui ont que le roles 'ROLE_USER'
        return new RedirectResponse($this->urlGenerator->generate('app_db_test_fixtures'));

        exit();
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
