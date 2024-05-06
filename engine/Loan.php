<?php
namespace Bibliotek\Entity;
use DateTime;
use Book;
use User;

class Loan{

    //attributes

    private DateTime $start;
    private DateTime $end;
    private Book $book;
    private User $reader;
    private string $review;


    //functions

    public function Loan(Book $_book, User $_reader){
        $this->book = $_book;
        $this->reader = $_reader;
    }

    public function getExpirationDate() : DateTime {
        $date = $this->start->modify('+15 day');
        return $date;
    }

    public function getEndLoanDate() : DateTime {
        return $this->end;
    }

    public function getReview() : string{
        return $this->review;
    }

//this function returns a bool variable for the loan status, TRUE for ended and FALSE for active
    public function getStatus() : bool {
        if ( is_null($this->end)){
            return FALSE;
        }
        else {
            return TRUE;
        }
    }

    public function getUser() : User {
        return $this->reader;
    }

    public function endLoan() {
        $this->end = new DateTime();
    }

    public function addReview(string $_review) {
        $this->review = $_review;
    }
    
}