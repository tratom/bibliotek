<?php

namespace Bibliotek\Controller;

use Bibliotek\Entity\Loan;
use Bibliotek\Entity\Reservation as EntityReservation;
use Bibliotek\Utility\Auth;
use Bibliotek\Utility\Email;
use DateTime;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use League\Route\Http\Exception\NotFoundException;

class Reservation {

    public static function show(ServerRequestInterface $request, array $args): ResponseInterface {
        $user = Auth::currentUser();
        $id = $args['id'];
        $book = $GLOBALS['entityManager']->find('Bibliotek\Entity\Book', $id);
        if ($book === null) {
            throw new NotFoundException();
        }

        if($book->getAvailability() > 0) {
            $GLOBALS['msg']->warning('The book can\'t be reserved since it\'s not available.');
            return new RedirectResponse('/books/' . $book->getId());
        }

        // check if user has already reservation for this book
        if($user->hasActiveBookReservation($book)) {
            $GLOBALS['msg']->warning('You have already an active reservation for this book.');
            return new RedirectResponse('/books/' . $book->getId());
        }

        // check current loans, get min estimated date and calculate reservation date
        $firstAvailableDate = $book->getFirstAvailableReservationDate();

        $html = $GLOBALS['twig']->render('loans/reserve.html.twig', ['book' => $book, 'estimatedDate' => $firstAvailableDate]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function add(ServerRequestInterface $request, array $args): ResponseInterface {
        $user = Auth::currentUser();
        $id = $args['id'];
        $book = $GLOBALS['entityManager']->find('Bibliotek\Entity\Book', $id);
        if ($book === null) {
            throw new NotFoundException();
        }

        $reservation = new EntityReservation();
        $reservation->setReservationDate(new DateTime());
        $reservation->setUser($user);
        $reservation->setBook($book);
        
        $GLOBALS['entityManager']->persist($reservation);
        $GLOBALS['entityManager']->flush();

        $firstAvailableDate = $book->getFirstAvailableReservationDate();

        // Send email to user
        $email = new Email();
        $email->new(
            $user->getEmail(),
            "Reservation added succesfully.",
            "Dear {$user->getName()},\nyour reservation for book \"{$book->getTitle()}\" on Bibliotek has been added.\n
            The book is estimated to be available for you on {$firstAvailableDate->format('Y-m-d')}.\nThank you."
        );
        $email->send();

        $GLOBALS['msg']->success('The book was successfully reserved.');
        return new RedirectResponse('/books/' . $book->getId());
    }

    public static function reserve(ServerRequestInterface $request, array $args): ResponseInterface {
        $user = Auth::currentUser();
        $bookId = $args['id'];
        $reservationId = $args['reservationId'];
        $book = $GLOBALS['entityManager']->find('Bibliotek\Entity\Book', $bookId);
        if ($book === null) {
            throw new NotFoundException();
        }

        $reservation = $GLOBALS['entityManager']->find('Bibliotek\Entity\Reservation', $reservationId);
        if ($reservation === null) {
            throw new NotFoundException();
        }

        // Create Loan
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

        $loan = new Loan;
        $loan->setBegin(new \DateTime());
        $loan->setReader($user);
        $loan->setBook($book);
        
        $GLOBALS['entityManager']->persist($loan);

        // Update reservation
        $reservation->setLoan($loan);
        $GLOBALS['entityManager']->persist($reservation);

        // Save all
        $GLOBALS['entityManager']->flush();

        $GLOBALS['msg']->success('The book was successfully loaned.');
        return new RedirectResponse('/loans');
    }

    public static function cancel(ServerRequestInterface $request, array $args) : ResponseInterface {
        $user = Auth::currentUser();
        $reservationId = $args['reservationId'];
        $reservation = $GLOBALS['entityManager']->find('Bibliotek\Entity\Reservation', $reservationId);

        if ($reservation === null) {
            throw new NotFoundException();
        }

        // Check if the user is admin and the loan has not been made by admin
        $isAdmin = false;
        if($user->getRole() == "admin"){
            $isAdmin = true;
        }
        
        if ($isAdmin == false && $reservation->getUser()->getId() != $user->getId()) {
            throw new NotFoundException();
        }

        $GLOBALS['entityManager']->remove($reservation);
        $GLOBALS['entityManager']->flush();

        if($isAdmin) {
            $email = new Email();
            $email->new(
                $reservation->getUser()->getEmail(),
                "Reservation canceled.",
                "Dear {$reservation->getUser()->getName()},\n
                an administrator canceled your reservation for book \"{$reservation->getBook()->getTitle()}\".
                Thank you."
            );
            $email->send();

            $GLOBALS['msg']->success('The reservation has been successfully deleted.');
            return new RedirectResponse('/admin/loans/reservation/manage');
        } else {
            $email = new Email();
            $email->new(
                $reservation->getUser()->getEmail(),
                "Reservation canceled.",
                "Dear {$reservation->getUser()->getName()},\n
                your reservation for book \"{$reservation->getBook()->getTitle()}\" has been canceled.
                Thank you."
            );
            $email->send();

            $GLOBALS['msg']->success('Your reservation has been successfully deleted.');
            return new RedirectResponse('/loans');
        }

        $GLOBALS['msg']->success('Your reservation has been successfully deleted.');
        return new RedirectResponse('/loans');
    }

    /*
     * Admin
     */
    public static function manage(ServerRequestInterface $request): ResponseInterface {
        $reservations = $GLOBALS['entityManager']->getRepository('Bibliotek\Entity\Reservation')->findAll();
        $html = $GLOBALS['twig']->render('loans/admin/reservations.html.twig', ['reservations' => $reservations]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }
}
