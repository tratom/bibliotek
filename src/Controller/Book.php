<?php
namespace Bibliotek\Controller;

use Bibliotek\Utility\Assets;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use League\Route\Http\Exception\BadRequestException;
use League\Route\Http\Exception\NotFoundException;

class Book {
    public static function listBooks(ServerRequestInterface $request) : ResponseInterface {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('b')
            ->from('Bibliotek\Entity\Book', 'b')
            ->where('b.visibility = :visibility')
            ->setParameter('visibility', True)
            ->orderBy('b.id', 'DESC');
        $books = $qb->getQuery()->getResult();

        $html = $GLOBALS['twig']->render('books/list.html.twig', ['books' => $books]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function searchBooks(ServerRequestInterface $request, array $args) : ResponseInterface {
        $params = $request->getQueryParams();
        $qb = $GLOBALS['entityManager']->createQueryBuilder();

        $params['title'] = $params['search'];
        unset($params['search']);

        $expr = array();
        foreach ($params as $field => $value){
            $expr[] = $qb->expr()->like("b.$field", $qb->expr()->literal("%$value%"));
        }
        $expr[] = $qb->expr()->eq("b.visibility", $qb->expr()->literal(True));

        $qb->select('b')
            ->from('Bibliotek\Entity\Book', 'b')
            ->where($qb->expr()->andX(...$expr));
        $books = $qb->getQuery()->getResult();

        $html = $GLOBALS['twig']->render('books/search.html.twig', ['queries' => $params, 'books' => $books]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function getBook(ServerRequestInterface $request, array $args) : ResponseInterface {
        $id = $args['id'];
        $book = $GLOBALS['entityManager']->find('Bibliotek\Entity\Book', $id);
        if ($book === null) {
            throw new NotFoundException();
        }

        $html = $GLOBALS['twig']->render('books/show.html.twig', ['book' => $book]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }
}