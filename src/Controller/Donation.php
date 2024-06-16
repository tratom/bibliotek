<?php
namespace Bibliotek\Controller;

use Bibliotek\Foundation\Book;
use Bibliotek\Foundation\Donation as FoundationDonation;
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
            $book = FoundationDonation::search($isbn);
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
            $book = Book::findBook($params['bookId']);
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
            FoundationDonation::saveDonation($donation);

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

        Book::saveBook($book);
        FoundationDonation::saveDonation($donation);

        $GLOBALS['msg']->success('Thank you for your donation! It will be approved by an administrator as soon as possible.');
        return new RedirectResponse('/donations');
    }

    public static function showDelete(ServerRequestInterface $request, array $args) : ResponseInterface {
        $user = Auth::currentUser();
        $donation = FoundationDonation::findDonation($args['id']);
        if($donation == null || $donation->getGiver()->getId() != $user->getId()) {
            throw new NotFoundException;
        }

        //todo sort DESC
        $html = $GLOBALS['twig']->render('donations/remove.html.twig', ['donation' => $donation]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    //cancellazione della donazione dall'utente
    public static function deleteDonation(ServerRequestInterface $request, array $args) : ResponseInterface {
        $user = Auth::currentUser();
        $donation = FoundationDonation::findDonation($args['id']);
        if($donation == null || $donation->getGiver()->getId() != $user->getId()) {
            throw new NotFoundException;
        }

        FoundationDonation::removeDonation($donation);

        $GLOBALS['msg']->success('Your donation has been successfully deleted.');
        return new RedirectResponse('/donations');
    }

    public static function manageDonations(ServerRequestInterface $request) : ResponseInterface {
        $donations = FoundationDonation::retrieveDonations();

        $html = $GLOBALS['twig']->render('donations/admin/manage.html.twig', ['donations' => $donations]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }

    //metodo chiamabile da amministratore
    public static function confirmDonation(ServerRequestInterface $request, array $args) : ResponseInterface {
        $params = $request->getParsedBody();
        $donation = FoundationDonation::findDonation($args['id']);
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

        Book::saveBook($book);
        FoundationDonation::saveDonation($donation);

        // Send email to user
        $user = $donation->getGiver();
        $email = new Email();
        $email->new(
            $user->getEmail(),
            "Your donation is now {$donation->getStatus()}.",
            "Dear {$user->getName()},\nyour donation on Bibliotek has been {$donation->getStatus()}.\nThank you for donating {$book->getTitle()}."
        );
        $email->send();
        
        $GLOBALS['msg']->info('The donation is now ' . $donation->getStatus());
        return new RedirectResponse('/admin/donations/manage');
    }

    public static function getDonation(ServerRequestInterface $request, array $args) : ResponseInterface {
        $id = $args['id'];
        $donation = FoundationDonation::findDonation($args['id']);
        if ($donation === null) {
            throw new NotFoundException;
        }

        $html = $GLOBALS['twig']->render('donations/admin/show.html.twig', ['donation' => $donation]);

        $response = new Response;
        $response->getBody()->write($html);
        return $response;
    }
}