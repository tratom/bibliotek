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

    public static function newBook(ServerRequestInterface $request) : ResponseInterface {
        $html = $GLOBALS['twig']->render('books/add.html.twig');

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function addBook(ServerRequestInterface $request) : ResponseInterface {
        $params = $request->getParsedBody();
        $book = new \Bibliotek\Entity\Book();
        $book->setTitle($params['title']);
        $book->setISBN($params['isbn']);
        $book->setPublishYear(new \DateTime($params['publishYear']));
        $book->setAuthors($params['authors']);
        $book->setGenres($params['genres']);
        $book->setDescription($params['description']);
        $book->setQuantity($params['quantity']);
        $book->setPagesNum($params['pagesNum']);

        // Handle user upload
        $assetsManager = new Assets("bookCover");
        $uploadedFiles = $request->getUploadedFiles();
        if (isset($uploadedFiles['cover']) && $uploadedFiles['cover']->getSize() != 0) {
            $cover = $uploadedFiles['cover'];
            $fileName = $assetsManager->uploadFile($cover);
            $book->setImageURL($assetsManager->getRelativeUrl($fileName));
        } elseif ($params['imageUrl'] != "") {
            $book->setImageURL($params['imageUrl']);
        } else {
            $book->setImageURL("https://placehold.co/400x800");
        }

        $GLOBALS['entityManager']->persist($book);
        $GLOBALS['entityManager']->flush();

        $GLOBALS['msg']->success('New book successfully added.');
        return new RedirectResponse('/books/' . $book->getId());
    }

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

    public static function modifyBook(ServerRequestInterface $request, array $args) : ResponseInterface {
        $book = $GLOBALS['entityManager']->find('Bibliotek\Entity\Book', $args['id']);
        if ($book === null) {
            throw new NotFoundException;
        }

        $html = $GLOBALS['twig']->render('books/edit.html.twig', ['book' => $book]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function editBook(ServerRequestInterface $request, array $args) : ResponseInterface {
        $params = $request->getParsedBody();
        $book = $GLOBALS['entityManager']->find('Bibliotek\Entity\Book', $args['id']);
        if ($book === null) {
            throw new NotFoundException;
        } else {
            // Handle user upload
            $assetsManager = new Assets("bookCover");
            $uploadedFiles = $request->getUploadedFiles();
            if (isset($uploadedFiles['cover']) && $uploadedFiles['cover']->getSize() != 0) {
                $cover = $uploadedFiles['cover'];
                $fileName = $assetsManager->uploadFile($cover);
                $book->setImageURL($assetsManager->getRelativeUrl($fileName));
            }

            if (!is_null($params['title']) && $params['title']!='') $book->setTitle($params['title']);
            if (!is_null($params['isbn']) && $params['isbn']!='') $book->setISBN($params['isbn']);
            if (!is_null($params['publishYear']) && $params['publishYear']!='') $book->setPublishYear(new \DateTime($params['publishYear'].'-01-01'));
            if (!is_null($params['authors']) && $params['authors']!='') $book->setAuthors($params['authors']);
            if (!is_null($params['genres']) && $params['genres']!='') $book->setGenres($params['genres']);
            if (!is_null($params['description']) && $params['description']!='') $book->setDescription($params['description']);
            if (!is_null($params['quantity']) && $params['quantity']!='') $book->setQuantity($params['quantity']);
            if (!is_null($params['pagesNum']) && $params['pagesNum']!='') $book->setPagesNum($params['pagesNum']);

            $GLOBALS['entityManager']->persist($book);
            $GLOBALS['entityManager']->flush();
        }

        $GLOBALS['msg']->success('The book was successfully edited.');
        return new RedirectResponse('/books/' . $book->getId());
    }

    public static function removeBook(ServerRequestInterface $request, array $args) : ResponseInterface {
        $book = $GLOBALS['entityManager']->find('Bibliotek\Entity\Book', $args['id']);
        if ($book === null) {
            throw new NotFoundException;
        }

        $html = $GLOBALS['twig']->render('books/remove.html.twig', ['book' => $book]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    public static function deletionBook(ServerRequestInterface $request, array $args) : ResponseInterface {
        $book = $GLOBALS['entityManager']->find('Bibliotek\Entity\Book', $args['id']);
        if ($book === null) {
            throw new NotFoundException;
        }
        $book->setVisibility(False);

        if($book->isLocalUpload()) {
            $assetsManager = new Assets("bookCover");
            $assetsManager->deleteFile($book->getImageURL());
        }

        $GLOBALS['entityManager']->persist($book);
        $GLOBALS['entityManager']->flush();

        $GLOBALS['msg']->success('The book was successfully removed.');
        return new RedirectResponse('/');
    }
}