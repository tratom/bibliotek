<?php
namespace Bibliotek\Controller;

use Bibliotek\Utility\Assets;
use Bibliotek\Utility\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use League\Route\Http\Exception\BadRequestException;
use League\Route\Http\Exception\NotAcceptableException;
use League\Route\Http\Exception\NotFoundException;

class Donation {

    public static function listDonations(ServerRequestInterface $request) : ResponseInterface {
        $user = Auth::currentUser();
        $donations = $user->getDonationsSortedByIdDesc();
        //todo sort DESC
        $html = $GLOBALS['twig']->render('donations/list.html.twig', ['donations' => $donations]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function search(ServerRequestInterface $request) : ResponseInterface {
        $params = $request->getQueryParams();
        if(!isset($params['isbn'])){
            $html = $GLOBALS['twig']->render('donations/search.html.twig');
        } else {
            $isbn = $params['isbn'];
            $qb = $GLOBALS['entityManager']->createQueryBuilder();
            $qb->select('b')
                ->from('Bibliotek\Entity\Book', 'b')
                ->where('b.isbn = :isbn')
                ->setParameter('isbn', $isbn)
                ->setMaxResults(1);
            $book = $qb->getQuery()->getResult();
            $html = $GLOBALS['twig']->render('donations/completeSearch.html.twig', ['book' => $book, 'isbn' => $isbn]);
        }
}