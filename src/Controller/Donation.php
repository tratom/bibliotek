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

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    //aggiunge una nuova donazione con inserimento manuale di tutte le info del libro
    public static function addDonation(ServerRequestInterface $request) : ResponseInterface {
        $params = $request->getParsedBody();

        if(isset($params['bookId'])){
            // Get book from id
            $book = $GLOBALS['entityManager']->find('Bibliotek\Entity\Book', $params['bookId']);
            // New donation from existing book
            $donation = new \Bibliotek\Entity\Donation();
            $donation->setPresentationDate(new \DateTime());
            $donation->setQuantity($params['quantity']);
            $donation->setGiver(Auth::currentUser());
            $donation->setBook($book);

            // Handle user upload
            $assetsManager = new Assets("userDonations");
            $uploadedFiles = $request->getUploadedFiles();
            if (isset($uploadedFiles['statusPhoto']) && $uploadedFiles['statusPhoto']->getSize() != 0) {
                $statusPhoto = $uploadedFiles['statusPhoto'];
                $fileName = $assetsManager->uploadFile($statusPhoto);
                $donation->setImageURL($assetsManager->getRelativeUrl($fileName));
            } else {
                throw new BadRequestException;
            }

            // Save
            $GLOBALS['entityManager']->persist($donation);
            $GLOBALS['entityManager']->flush();

            $GLOBALS['msg']->success('Thank you for your donation! It will be approved by an administrator as soon as possible.');
            return new RedirectResponse('/donations');
        }
        // New donation from NON-existing book
        $donation = new \Bibliotek\Entity\Donation();
        $donation->setPresentationDate(new \DateTime());
        $donation->setQuantity($params['quantity']);
        $donation->setGiver(Auth::currentUser());
        // Create new book
        $book = new \Bibliotek\Entity\Book();
        $book->setTitle($params['title']);
        $book->setISBN($params['isbn']);
        $book->setPublishYear(new \DateTime($params['publishYear']));
        $book->setAuthors($params['authors']);
        $book->setGenres($params['genres']);
        $book->setDescription($params['description']);
        $book->setQuantity(0);
        $book->setPagesNum($params['pagesNum']);
        $book->setVisibility(False);


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

        $donation->setBook($book);

        $assetsManager = new Assets("userDonations");
        $uploadedFiles = $request->getUploadedFiles();
        if (isset($uploadedFiles['statusPhoto']) && $uploadedFiles['statusPhoto']->getSize() != 0) {
            $statusPhoto = $uploadedFiles['statusPhoto'];
            $fileName = $assetsManager->uploadFile($statusPhoto);
            $donation->setImageURL($assetsManager->getRelativeUrl($fileName));
        } else {
            throw new BadRequestException;
        }

        $GLOBALS['entityManager']->persist($book);
        $GLOBALS['entityManager']->persist($donation);
        $GLOBALS['entityManager']->flush();

        $GLOBALS['msg']->success('Thank you for your donation! It will be approved by an administrator as soon as possible.');
        return new RedirectResponse('/donations');
    }

    public static function manageDonations(ServerRequestInterface $request) : ResponseInterface {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('d')
            ->from('Bibliotek\Entity\Donation', 'd')
            // ->where('d.status = :status')
            // ->setParameter('status', 'pending')
            ->orderBy('d.id', 'DESC');
        $donations = $qb->getQuery()->getResult();

        $html = $GLOBALS['twig']->render('donations/admin/manage.html.twig', ['donations' => $donations]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    //metodo chiamabile da amministratore
    public static function confirmDonation(ServerRequestInterface $request, array $args) : ResponseInterface {
        $params = $request->getParsedBody();
        $donation = $GLOBALS['entityManager']->find('Bibliotek\Entity\Donation', $args['id']);
        if($donation == null) {
            throw new NotFoundException;
        }
        $book = $donation->getBook();

        if (isset($params['approve'])){
            $donation->setStatus('accepted');
            $book->setVisibility(True);
            $book->setQuantity($book->getQuantity()+$donation->getQuantity());
        } elseif (isset($params['reject'])) {
            $donation->setStatus('rejected');
        } else {
            throw new BadRequestException();
        }
        $donation->setConvalidationDate(new \DateTime());
        $donation->setComment($params['comment']);

        $GLOBALS['entityManager']->persist($donation);
        $GLOBALS['entityManager']->persist($book);
        $GLOBALS['entityManager']->flush();
        
        $GLOBALS['msg']->info('The donation is now ' . $donation->getStatus());
        return new RedirectResponse('/admin/donations/manage');
    }

    public static function getDonation(ServerRequestInterface $request, array $args) : ResponseInterface {
        $id = $args['id'];
        $donation = $GLOBALS['entityManager']->find('Bibliotek\Entity\Donation', $id);
        if ($donation === null) {
            throw new NotFoundException;
        }

        $html = $GLOBALS['twig']->render('donations/admin/show.html.twig', ['donation' => $donation]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }
}