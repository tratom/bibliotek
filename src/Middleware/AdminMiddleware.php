<?php

declare(strict_types=1);

namespace Bibliotek\Middleware;

use Bibliotek\Utility\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;

class AdminMiddleware implements MiddlewareInterface {
    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        // Get cookie and check if jwt exists
        $cookies = $request->getCookieParams();

        // Get user JWT
        $jwt = $cookies['jwt'];
        if (Auth::getTokenPayload($jwt)['role'] !== "admin") {
            // if not admin redirect to homepage
            return new RedirectResponse("/");
        }

        return $handler->handle($request);
    }
}
