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
$router->post('/donations/add', 'Bibliotek\Controller\Donation::addDonation')->middleware(new \Bibliotek\Middleware\AuthMiddleware);

/*
 * Loan
 */
$router->get('/loans', 'Bibliotek\Controller\Loan::listLoans')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->get('/loans/book/{book:number}', 'Bibliotek\Controller\Loan::startLoan')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->post('/loans/book/{book:number}/start', 'Bibliotek\Controller\Loan::doLoan')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->get('/loans/{id:number}/end', 'Bibliotek\Controller\Loan::endLoan')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->post('/loans/{id:number}/end', 'Bibliotek\Controller\Loan::closeLoan')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->get('/loans/{id:number}/review', 'Bibliotek\Controller\Loan::showReviewLoan')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->post('/loans/{id:number}/review', 'Bibliotek\Controller\Loan::postReviewLoan')->middleware(new \Bibliotek\Middleware\AuthMiddleware);


/*
 * User
 */
$router->get('/login', 'Bibliotek\Controller\User::getLogin');
$router->post('/login', 'Bibliotek\Controller\User::doLogin');
// user must be logged in to logout
$router->get('/logout', 'Bibliotek\Controller\User::doLogout')->middleware(new \Bibliotek\Middleware\AuthMiddleware);

/*
 * Admin
 */
$router->group('/admin', function (\League\Route\RouteGroup $router) {
    /*
     * Books
     */
    $router->get('/books', 'Bibliotek\Controller\Book::newBook');
    $router->post('/books', 'Bibliotek\Controller\Book::addBook');
    $router->get('/books/{id:number}/edit', 'Bibliotek\Controller\Book::modifyBook');
    /*
     * Donations
     */
    $router->get('/donations/manage', 'Bibliotek\Controller\Donation::manageDonations');
    $router->get('/donations/manage/{id:number}', 'Bibliotek\Controller\Donation::getDonation');
    $router->post('/donations/manage/{id:number}', 'Bibliotek\Controller\Donation::confirmDonation');
})
    ->middleware(new \Bibliotek\Middleware\AuthMiddleware)
    ->middleware(new \Bibliotek\Middleware\AdminMiddleware);

$response = $router->dispatch($request);

// send the response to the browser
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);