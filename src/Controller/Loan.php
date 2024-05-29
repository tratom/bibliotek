<?php

namespace Bibliotek\Controller;

use Bibliotek\Entity\Loan as EntityLoan;
use Bibliotek\Utility\Assets;
use Bibliotek\Utility\Auth;
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
        //todo sort DESC
        $html = $GLOBALS['twig']->render('loans/list.html.twig', ['loans' => $loans]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function endLoan(ServerRequestInterface $request, array $args): ResponseInterface {
        $user = Auth::currentUser();
        $loan = $GLOBALS['entityManager']->find('Bibliotek\Entity\Loan', $args['id']);
        if ($loan == null || $loan->getReader()->getId() != $user->getId()) {
            throw new NotFoundException;
        }}

    public static function startLoan(ServerRequestInterface $request, array $args): ResponseInterface {
        $user = Auth::currentUser();
        $book = $GLOBALS['entityManager']->find('Bibliotek\Entity\Book', $args['book']);
        if ($book == null) {
            throw new NotFoundException;
        }
        //todo vedere se il libro è disponibile (quantità, prestiti in corso, prenotazioni)

        $html = $GLOBALS['twig']->render('loans/start.html.twig', ['book' => $book]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function doLoan(ServerRequestInterface $request, array $args): ResponseInterface {
        $user = Auth::currentUser();
        $book = $GLOBALS['entityManager']->find('Bibliotek\Entity\Book', $args['book']);
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
        
        $GLOBALS['entityManager']->persist($loan);
        $GLOBALS['entityManager']->flush();

        $GLOBALS['msg']->success('The book has been borrowed!');
        return new RedirectResponse('/loans');
    }
}