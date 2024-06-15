<?php

declare(strict_types=1);

namespace Bibliotek\Middleware;

use Bibliotek\Utility\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;

class AuthMiddleware implements MiddlewareInterface {
    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        // Get cookie and check if jwt exists
        $cookies = $request->getCookieParams();
        if (!isset($cookies['jwt'])) {
            // no jwt, user is not logged in
            return new RedirectResponse("/login?redirectTo=" . $request->getRequestTarget());
        }

        // Get user JWT
        $jwt = $cookies['jwt'];
        if (!Auth::isValidJWT($jwt)) {
            // invalid jwt, redirect to login
            return new RedirectResponse("/login?redirectTo=" . $request->getRequestTarget());
        }

        // Check if user exist
        $userId = Auth::getTokenPayload($jwt)['sub'];
        $user = $GLOBALS['entityManager']->find('Bibliotek\Entity\User', $userId);
        if ($user === null) {
            return new RedirectResponse("/login?redirectTo=" . $request->getRequestTarget());
        }
        return $handler->handle($request);
    }
}