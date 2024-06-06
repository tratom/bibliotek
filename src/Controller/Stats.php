<?php

namespace Bibliotek\Controller;

use Bibliotek\Entity\Book;
use Bibliotek\Entity\Donation;
use Bibliotek\Entity\Loan;
use Bibliotek\Entity\User;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Stats {

    public static function show(ServerRequestInterface $request): ResponseInterface {    
        $start = new \DateTime();
        $end = new \DateTime();

        $query = $request->getQueryParams();

        $last = "7d";
        if(isset($query['last'])){
            switch($query['last']){
                case '30d':
                    $last = $query['last'];
                    $start->modify("-30 days");
                    break;
                case '90d':
                    $last = $query['last'];
                    $start->modify("-90 days");
                    break;
                default:
                    $start->modify("-7 days");
            }
        } else {
            $start->modify("-7 days");  
        }
        
        $loansPerDay = Loan::getLoansPerDays($start, $end);
        $activeLoansPerDay = Loan::getActiveLoansPerDays($start, $end);
        $donationsPerDay = Donation::getDonationsPerDays($start, $end);
        $activeDonationsPerDay = Donation::getActiveDonationsPerDays($start, $end);
        $totalBooks = Book::getTotalBooks();
        $totalUsers = User::getTotalUsers();

        // var_dump($donationsPerDay);

        $html = $GLOBALS['twig']->render('stats.html.twig', [
            'last' => $last,
            'loansPerDay' => $loansPerDay,
            'activeLoansPerDay' => $activeLoansPerDay,
            'donationsPerDay' => $donationsPerDay,
            'activeDonationsPerDay' => $activeDonationsPerDay,
            'totalBooks' => $totalBooks,
            'totalUsers' => $totalUsers
        ]);

        $response = new Response();
        $response->getBody()->write($html);
        return $response;
    }
}
