<?php

declare(strict_types=1);

require_once "bootstrap.php";

use Bibliotek\Utility\ExceptionHandlerStategy;

$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

$router = new League\Route\Router;
// Set how we manage HTTP errors
$router->setStrategy(new ExceptionHandlerStategy);

/*
 * User
 */
$router->get('/login', 'Bibliotek\Controller\User::getLogin');
$router->post('/login', 'Bibliotek\Controller\User::doLogin');
// user must be logged in to logout
$router->get('/logout', 'Bibliotek\Controller\User::doLogout')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->get('/settings', 'Bibliotek\Controller\User::getSettings')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->post('/settings', 'Bibliotek\Controller\User::updateSettings')->middleware(new \Bibliotek\Middleware\AuthMiddleware);

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
$router->get('/donations/{id:number}/remove', 'Bibliotek\Controller\Donation::showDelete')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->post('/donations/{id:number}/remove', 'Bibliotek\Controller\Donation::deleteDonation')->middleware(new \Bibliotek\Middleware\AuthMiddleware);

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
// Reservation
$router->get('/loans/book/{id:number}/reserve', 'Bibliotek\Controller\Reservation::show')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->post('/loans/book/{id:number}/reserve', 'Bibliotek\Controller\Reservation::add')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->post('/loans/book/{id:number}/reserve/{reservationId:number}', 'Bibliotek\Controller\Reservation::reserve')->middleware(new \Bibliotek\Middleware\AuthMiddleware);
$router->post('/loans/book/{id:number}/reserve/{reservationId:number}/cancel', 'Bibliotek\Controller\Reservation::cancel')->middleware(new \Bibliotek\Middleware\AuthMiddleware);

/*
 * Admin
 */
$router->group('/admin', function (\League\Route\RouteGroup $router) {
    $router->get('/stats', 'Bibliotek\Controller\Stats::show');

    /*
     * Users
     */
    $router->get('/users', 'Bibliotek\Controller\User::listUsers');
    $router->get('/users/{id:number}', 'Bibliotek\Controller\User::showUser');
    $router->get('/users/add', 'Bibliotek\Controller\User::showAddUser');
    $router->post('/users/add', 'Bibliotek\Controller\User::addUser');
    $router->get('/users/{id:number}/edit', 'Bibliotek\Controller\User::adminShowEdit');
    $router->post('/users/{id:number}/edit', 'Bibliotek\Controller\User::adminEdit');
    $router->get('/users/{id:number}/delete', 'Bibliotek\Controller\User::adminShowDelete');
    $router->post('/users/{id:number}/delete', 'Bibliotek\Controller\User::adminDelete');
    
    /*
     * Books
     */
    $router->get('/books', 'Bibliotek\Controller\Book::newBook');
    $router->post('/books', 'Bibliotek\Controller\Book::addBook');
    $router->get('/books/{id:number}/edit', 'Bibliotek\Controller\Book::modifyBook');
    $router->post('/books/{id:number}/edit', 'Bibliotek\Controller\Book::editBook');
    $router->get('/books/{id:number}/remove', 'Bibliotek\Controller\Book::removeBook');
    $router->post('/books/{id:number}/remove', 'Bibliotek\Controller\Book::deletionBook');
    /*
     * Donations
     */
    $router->get('/donations/manage', 'Bibliotek\Controller\Donation::manageDonations');
    $router->get('/donations/manage/{id:number}', 'Bibliotek\Controller\Donation::getDonation');
    $router->post('/donations/manage/{id:number}', 'Bibliotek\Controller\Donation::confirmDonation');
    /*
     * Loans
     */
    $router->get('/loans/manage', 'Bibliotek\Controller\Loan::manageLoans');
    $router->get('/loans/manage/{id:number}/return', 'Bibliotek\Controller\Loan::earlyReturnLoan');
    $router->get('/loans/reservation/manage', 'Bibliotek\Controller\Reservation::manage');
})
    ->middleware(new \Bibliotek\Middleware\AuthMiddleware)
    ->middleware(new \Bibliotek\Middleware\AdminMiddleware);

$response = $router->dispatch($request);

// send the response to the browser
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
