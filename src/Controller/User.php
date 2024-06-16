<?php

namespace Bibliotek\Controller;

use Bibliotek\Entity\User as EntityUser;
use Bibliotek\Foundation\User as FoundationUser;
use Bibliotek\Utility\Auth;
use Bibliotek\Utility\Email;
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

    public static function getSettings(ServerRequestInterface $request): ResponseInterface {
        $html = $GLOBALS['twig']->render('user/settings.html.twig');

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function updateSettings(ServerRequestInterface $request, array $args): ResponseInterface {
        $user = Auth::currentUser();

        $params = $request->getParsedBody();
        $errors = self::validateUserInputs($params, ['name', 'surname', 'email', 'birthday']);

        // Check if at least one of the password field is not empty 
        if(!empty($params['newPassword']) || !empty($params['confirmNewPassword']) || !empty($params['currentPassword'])){
            // Check if current password is correct
            if(!password_verify($params['currentPassword'], $user->getPassword())) {
                $errors[] = "Current password is incorrect.";
            }
        }
        
        // If there are errors, return them
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $GLOBALS['msg']->error($error);
            }
            return new RedirectResponse('/settings', 302);
        }

        $user->setName(filter_var($params['name'], FILTER_UNSAFE_RAW));
        $user->setSurname(filter_var($params['surname'], FILTER_UNSAFE_RAW));
        $user->setEmail(filter_var($params['email'], FILTER_SANITIZE_EMAIL));
        $user->setBirthday(new \DateTime($params['birthday']));


        if (!empty($params['newPassword'])) {
            $user->setPassword(password_hash($params['newPassword'], PASSWORD_BCRYPT));
        }
        FoundationUser::saveUser($user);

        $GLOBALS['msg']->success('You settings were successfully updated.');
        return new RedirectResponse('/settings', 302);
    }

    /*
     * ADMIN
     */

    public static function listUsers(ServerRequestInterface $request, array $args): ResponseInterface {
        $users = FoundationUser::getRepository();

        $html = $GLOBALS['twig']->render('user/admin/list.html.twig', ['users' => $users]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function showUser(ServerRequestInterface $request, array $args): ResponseInterface {
        $id = $args['id'];
        $user = FoundationUser::findUser($id);
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

        FoundationUser::saveUser($user);

        // Send email to user
        $email = new Email();
        $email->new($user->getEmail(), "Your account on Bibliotek was created.", "Hey {$user->getName()},\nyour account on Bibliotek was succesfully created.\nYou can now log in with credentials provided by the administrator.");
        $email->send();

        $GLOBALS['msg']->success('The user was successfully created.');
        return new RedirectResponse('/admin/users', 302);
    }


    public static function adminShowEdit(ServerRequestInterface $request, array $args): ResponseInterface {
        $id = $args['id'];
        $user = FoundationUser::findUser($id);
        if ($user === null) {
            throw new NotFoundException();
        }
        $html = $GLOBALS['twig']->render('user/admin/edit.html.twig', ['eUser' => $user]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function adminEdit(ServerRequestInterface $request, array $args): ResponseInterface {
        $id = $args['id'];
        $user = FoundationUser::findUser($id);
        if ($user === null) {
            throw new NotFoundException();
        }

        $params = $request->getParsedBody();
        $errors = self::validateUserInputs($params);

        // If there are errors, return them
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $GLOBALS['msg']->error($error);
            }
            return new RedirectResponse('/admin/users/' . $user->getId() . '/edit', 302);
        }

        // Validate banned field if it exists
        $banned = isset($params['banned']) ? ($params['banned'] === 'on') : false;

        // Validate admin field if it exists
        $admin = isset($params['admin']) ? ($params['admin'] === 'on') : false;

        $user->setName(filter_var($params['name'], FILTER_UNSAFE_RAW));
        $user->setSurname(filter_var($params['surname'], FILTER_UNSAFE_RAW));
        $user->setEmail(filter_var($params['email'], FILTER_SANITIZE_EMAIL));
        $user->setMaxLoanNum((int) $params['maxLoanNum']);
        $user->setMaxLoanDuration((int) $params['maxLoanDuration']);
        $user->setReputation((int) $params['reputation']);
        $user->setBirthday(new \DateTime($params['birthday']));
        $user->setBanned($banned);

        if (!empty($params['newPassword'])) {
            $user->setPassword(password_hash($params['newPassword'], PASSWORD_BCRYPT));
        }

        // Handle admin role if applicable
        if ($admin) {
            $user->setRole('admin');
        } else {
            $user->setRole('user');
        }

        FoundationUser::saveUser($user);

        $GLOBALS['msg']->success('The user was successfully edited.');
        return new RedirectResponse('/admin/users', 302);
    }

    private static function validateUserInputs(array $params, array $requiredFields = ['name', 'surname', 'email', 'maxLoanNum', 'maxLoanDuration', 'reputation', 'birthday']): array {
        $errors = [];

        // Check required fields
        foreach ($requiredFields as $field) {
            if (empty($params[$field])) {
                $errors[] = "$field is required.";
            }
        }

        // Validate email format
        if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        // Validate maxLoanNum and maxLoanDuration are integers
        if (isset($params['maxLoanNum']) && !filter_var($params['maxLoanNum'], FILTER_VALIDATE_INT)) {
            $errors[] = "maxLoanNum must be an integer.";
        }
        if (isset($params['maxLoanDuration']) && !filter_var($params['maxLoanDuration'], FILTER_VALIDATE_INT)) {
            $errors[] = "maxLoanDuration must be an integer.";
        }

        // Validate reputation is an integer between 0 and 100
        if (isset($params['reputation']) && (!filter_var($params['reputation'], FILTER_VALIDATE_INT) || $params['reputation'] <= 0 || $params['reputation'] >= 100)) {
            $errors[] = "reputation must be an integer between 0 and 100.";
        }

        // Validate passwords if provided
        if (!empty($params['newPassword']) || !empty($params['confirmNewPassword'])) {
            if ($params['newPassword'] !== $params['confirmNewPassword']) {
                $errors[] = "Passwords do not match.";
            }
        }


        return $errors;
    }

    public static function adminShowDelete(ServerRequestInterface $request, array $args): ResponseInterface {
        $id = $args['id'];
        $user = FoundationUser::findUser($id);
        if ($user === null) {
            throw new NotFoundException();
        }
        $html = $GLOBALS['twig']->render('user/admin/remove.html.twig', ['eUser' => $user]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function adminDelete(ServerRequestInterface $request, array $args): ResponseInterface {
        $id = $args['id'];
        $user = FoundationUser::findUser($id);
        if ($user === null) {
            throw new NotFoundException();
        }

        FoundationUser::saveUser($user);

        $GLOBALS['msg']->success('The user was successfully deleted.');
        return new RedirectResponse('/admin/users', 302);
    }
}