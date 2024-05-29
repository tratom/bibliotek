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
