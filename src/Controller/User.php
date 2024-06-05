<?php

namespace Bibliotek\Controller;

use Bibliotek\Entity\User as EntityUser;
use Bibliotek\Utility\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Http\Cookies;
use League\Route\Http\Exception\NotFoundException;

class User {

    public static function getLogin(ServerRequestInterface $request, array $args): ResponseInterface {
        $query = $request->getQueryParams();
        $params = [];
        if (isset($query['redirectTo'])) {
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
        ]);
    }

    public static function listUsers(ServerRequestInterface $request, array $args): ResponseInterface {
        $users = $GLOBALS['entityManager']->getRepository('Bibliotek\Entity\User')->findAll();

        $html = $GLOBALS['twig']->render('user/admin/list.html.twig', ['users' => $users]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function showUser(ServerRequestInterface $request, array $args): ResponseInterface {
        $id = $args['id'];
        $user = $GLOBALS['entityManager']->find('Bibliotek\Entity\User', $id);
        if ($user === null) {
            throw new NotFoundException();
        }
        $html = $GLOBALS['twig']->render('user/admin/show.html.twig', ['eUser' => $user]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function showAddUser(ServerRequestInterface $request): ResponseInterface {
        $html = $GLOBALS['twig']->render('user/admin/add.html.twig');

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function addUser(ServerRequestInterface $request): ResponseInterface {
        $params = $request->getParsedBody();
        $errors = self::validateUserInputs($params);

        // If there are errors, return them
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $GLOBALS['msg']->error($error);
            }
            return new RedirectResponse('/admin/users/add', 302);
        }

        // Validate banned field if it exists
        $banned = isset($params['banned']) ? ($params['banned'] === 'on') : false;

        // Validate admin field if it exists
        $admin = isset($params['admin']) ? ($params['admin'] === 'on') : false;

        $user = new EntityUser();
        $user->setName(filter_var($params['name'], FILTER_UNSAFE_RAW));
        $user->setSurname(filter_var($params['surname'], FILTER_UNSAFE_RAW));
        $user->setEmail(filter_var($params['email'], FILTER_SANITIZE_EMAIL));
        $user->setMaxLoanNum((int) $params['maxLoanNum']);
        $user->setMaxLoanDuration((int) $params['maxLoanDuration']);
        $user->setReputation((int) $params['reputation']);
        $user->setBirthday(new \DateTime($params['birthday']));
        $user->setBanned($banned);

        if (empty($params['newPassword'])) {
            $GLOBALS['msg']->error("Password is required");
            return new RedirectResponse('/admin/users/add', 302);
        }
        $user->setPassword(password_hash($params['newPassword'], PASSWORD_BCRYPT));

        // Handle admin role if applicable
        if ($admin) {
            $user->setRole('admin');
        } else {
            $user->setRole('user');
        }

        $GLOBALS['entityManager']->persist($user);
        $GLOBALS['entityManager']->flush();

        $GLOBALS['msg']->success('The user was successfully created.');
        return new RedirectResponse('/admin/users', 302);
    }
}