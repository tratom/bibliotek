<?php

declare(strict_types=1);

require_once "bootstrap.php";

$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

$router = new League\Route\Router;

/*
 * Books
 */
$router->get('/', 'Bibliotek\Controller\Book::listBooks');
$router->get('/books/{id:number}', 'Bibliotek\Controller\Book::getBook');
$router->get('/search', 'Bibliotek\Controller\Book::searchBooks');


/*
 * Donations
 */
$router->get('/donations', 'Bibliotek\Controller\Donation::listDonations')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->get('/donations/search', 'Bibliotek\Controller\Donation::search')->middleware(new \Bibliotek\Middleware\AuthMiddleware);

/*
 * Loan
 */
$router->get('/loans', 'Bibliotek\Controller\Loan::listLoans')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->get('/loans/book/{book:number}', 'Bibliotek\Controller\Loan::startLoan')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->post('/loans/book/{book:number}/start', 'Bibliotek\Controller\Loan::doLoan')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->get('/loans/{id:number}/end', 'Bibliotek\Controller\Loan::endLoan')->middleware(new \Bibliotek\Middleware\AuthMiddleware);

$response = $router->dispatch($request);

// send the response to the browser
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
