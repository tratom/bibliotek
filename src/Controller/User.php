<?php

namespace Bibliotek\Controller;

use Bibliotek\Entity\User as EntityUser;
use Bibliotek\Utility\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Http\Cookies;

class User {

    public static function getLogin(ServerRequestInterface $request, array $args): ResponseInterface {
        $query = $request->getQueryParams();
        $params = [];
        if (isset($query['redirectTo'])){
            $params += ['redirectTo' => $query['redirectTo']];
        }

        $html = $GLOBALS['twig']->render('user/login.html.twig', $params);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function doLogin(ServerRequestInterface $request): ResponseInterface {
        $params = $request->getParsedBody();
        $email = $params['email'];
        $password = $params['password'];

        $user = EntityUser::checkLogin($email, $password);

        if (!$user) {
            $GLOBALS['msg']->error('The user entered does not exist.');
            return new RedirectResponse('/login');
        }

        $jwt = Auth::issueJWT($user->getId(), $user->getRole());
        $cookie = 'jwt=' . $jwt . '; Path=/; Expires=' . gmdate('D, d M Y H:i:s T', time() + 3600) . '; HttpOnly';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $cookie .= '; Secure';
        }
        $response = new Response();
        $response = $response->withAddedHeader('Set-Cookie', $cookie);
        if (isset($params['redirectTo']) && $params['redirectTo'] !== "") {
            $response = $response->withHeader('Location', $params['redirectTo'])->withStatus(301);
        } else {
            $response = $response->withHeader('Location', '/')->withStatus(301);
        }        
        
        return $response;
    }

    public static function doLogout(ServerRequestInterface $request): ResponseInterface {
        unset($_COOKIE['jwt']);
        
        // Create the expired cookie
        $cookie = 'jwt=; Path=/; Expires=' . gmdate('D, d M Y H:i:s T', time() - 3600) . '; Max-Age=0; HttpOnly';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $cookie .= '; Secure';
        }

        return new RedirectResponse('/login', 302, [
            'Set-Cookie' => $cookie
        ]);;
    }
    
}
