<?php

namespace Bibliotek\Controller;

use Bibliotek\Entity\Loan as EntityLoan;
use Bibliotek\Foundation\Book;
use Bibliotek\Foundation\Loan as FoundationLoan;
use Bibliotek\Foundation\Reservation;
use Bibliotek\Utility\Assets;
use Bibliotek\Utility\Auth;
use Bibliotek\Utility\Email;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use League\Route\Http\Exception\BadRequestException;
use League\Route\Http\Exception\NotAcceptableException;
use League\Route\Http\Exception\NotFoundException;

class Loan {

    public static function listLoans(ServerRequestInterface $request): ResponseInterface {
        $user = Auth::currentUser();
        $loans = $user->getLoansSortedByIdDesc();
        $reservations = $user->getActiveReservations();
        $html = $GLOBALS['twig']->render('loans/list.html.twig', ['loans' => $loans, 'reservations' => $reservations]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function endLoan(ServerRequestInterface $request, array $args): ResponseInterface {
        $user = Auth::currentUser();
        $loan = FoundationLoan::findLoan($args['id']);
        if ($loan == null || $loan->getReader()->getId() != $user->getId()) {
            throw new NotFoundException;
        }

        $html = $GLOBALS['twig']->render('loans/end.html.twig', ['loan' => $loan]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function closeLoan(ServerRequestInterface $request, array $args): ResponseInterface {
        $user = Auth::currentUser();
        $loan = FoundationLoan::findLoan($args['id']);
        if ($loan == null || $loan->getReader()->getId() != $user->getId()) {
            throw new NotFoundException;
        }
        $loan->setEnd(new \DateTime());

        FoundationLoan::saveLoan($loan);

        $reservations = $loan->getBook()->getActiveReservations();
        if(!empty($reservations)){
            $firstReservation = $reservations[0];
            $u = $firstReservation->getUser();
            $email = new Email();
            $email->new(
                $u->getEmail(),
                "You can now loan {$firstReservation->getBook()->getTitle()}.",
                "Dear {$u->getName()},\nyou're receiving this notification because you're the first in the queue for the book {$firstReservation->getBook()->getTitle()}.\n
                You can now loan this book from your personal loans page! Remember, you can loan it until tomorrow."
            );
            $email->send();
        }

        // Send email to user
        $user = $loan->getReader();
        $email = new Email();
        $email->new(
            $user->getEmail(),
            "Your loan has ended.",
            "Dear {$user->getName()},\nyour loan on Bibliotek has ended.\nThank you for returning {$loan->getBook()->getTitle()}."
        );
        $email->send();

        $GLOBALS['msg']->info('Thank you for returning the book!');
        return new RedirectResponse('/loans');
    }

    public static function showReviewLoan(ServerRequestInterface $request, array $args): ResponseInterface {
        $user = Auth::currentUser();
        $loan = FoundationLoan::findLoan($args['id']);
        if ($loan == null || $loan->getReader()->getId() != $user->getId()) {
            throw new NotFoundException;
        }

        $html = $GLOBALS['twig']->render('loans/review.html.twig', ['loan' => $loan]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function postReviewLoan(ServerRequestInterface $request, array $args): ResponseInterface {
        $params = $request->getParsedBody();
        $user = Auth::currentUser();
        $loan = FoundationLoan::findLoan($args['id']);
        if ($loan == null || $loan->getReader()->getId() != $user->getId()) {
            throw new NotFoundException;
        }
        $loan->setReview($params['review']);

        FoundationLoan::saveLoan($loan);

        $GLOBALS['msg']->info('Thank you for leaving a review!');
        return new RedirectResponse('/loans');
    }

    public static function startLoan(ServerRequestInterface $request, array $args): ResponseInterface {
        $book = Book::findBook($args['book']);
        if ($book == null) {
            throw new NotFoundException;
        }

        $html = $GLOBALS['twig']->render('loans/start.html.twig', ['book' => $book]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function doLoan(ServerRequestInterface $request, array $args): ResponseInterface {
        $user = Auth::currentUser();
        $book = Book::findBook($args['book']);
        if ($book == null) {
            throw new NotFoundException;
        }

        if ($user->hasActiveBookLoans($book)) {
            $GLOBALS['msg']->warning('You already have this book on loan.');
            return new RedirectResponse('/books/' . $book->getId());
        }
        if ($user->getBanned()) {
            $GLOBALS['msg']->error('You have been banned and therefore cannot borrow books.');
            return new RedirectResponse('/books/' . $book->getId());
        }
        if ($user->countActiveLoans() >= $user->getMaxLoanNum()) {
            $GLOBALS['msg']->warning('You have reached the maximum limit of active loans.');
            return new RedirectResponse('/books/' . $book->getId());
        }
        if ($book->getQuantity() - $book->countActiveLoans() <= 0) {
            $GLOBALS['msg']->info('There are not enough books available.');
            return new RedirectResponse('/books/' . $book->getId());
        }

        $loan = new EntityLoan;
        $loan->setBegin(new \DateTime());
        $loan->setReader($user);
        $loan->setBook($book);
        
        FoundationLoan::saveLoan($loan);

        // Send email to user
        $user = $loan->getReader();
        $email = new Email();
        $email->new(
            $user->getEmail(),
            "Your loan has started.",
            "Dear {$user->getName()},\nyour loan for book \"{$loan->getBook()->getTitle()}\" on Bibliotek has started.\nThank you."
        );
        $email->send();

        $GLOBALS['msg']->success('The book has been borrowed!');
        return new RedirectResponse('/loans');
    }

    public static function postponeLoan(ServerRequestInterface $request, array $args): ResponseInterface {
        $user = Auth::currentUser();
        $loan = FoundationLoan::findLoan($args['id']);
        if ($loan == null || $loan->getReader()->getId() != $user->getId()) {
            throw new NotFoundException;
        }

        // Check if book has active reservations
        if($loan->getBook()->countActiveReservations() > 0) {
            $GLOBALS['msg']->error('The book has active reservations, the loan can\'t be postponed!');
            return new RedirectResponse('/loans');
        }

        $userMaxDuration = $loan->getReader()->getMaxLoanDuration();
        $newBegin = clone $loan->getBegin()->modify("+$userMaxDuration days");
        $loan->setBegin($newBegin);

        FoundationLoan::saveLoan($loan);

        $GLOBALS['msg']->info("The loan has been postponed by {$userMaxDuration} days!");
        return new RedirectResponse('/loans');
    }

    /*
     * Admin
     */

    public static function manageLoans(ServerRequestInterface $request): ResponseInterface {
        $loans = FoundationLoan::getRepository();
        // $reservations = $user->getActiveReservations();
        $reservations = Reservation::getRepository();
        $html = $GLOBALS['twig']->render('loans/admin/list.html.twig', ['loans' => $loans, 'reservations' => $reservations]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function earlyReturnLoan(ServerRequestInterface $request, array $args): ResponseInterface {
        $loan = FoundationLoan::findLoan($args['id']);
        if ($loan == null) {
            throw new NotFoundException;
        }

        // Send email to user
        $user = $loan->getReader();
        $email = new Email();
        $email->new(
            $user->getEmail(),
            "Early return of book.",
            "Dear {$user->getName()},\n
            early return of the book you have on loan {$loan->getBook()->getTitle()} has been requested.
            We ask you to return it as soon as possible.
            Thank you."
        );
        $email->send();

        $GLOBALS['msg']->success('Early return of the book has been requested.');
        return new RedirectResponse('/admin/loans/manage');
    }
}
