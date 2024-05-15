<?php
namespace Bibliotek\Entity;
use DateTime;
use Book;
use User;
use Exception;

class eLoan{

    //attributes

    private DateTime $start;
    private DateTime $end;
    private Book $book;
    private User $reader;
    private string $review;


    //functions

    public function Loan(Book $_book, User $_reader){
        $this->start = new DateTime();
        $this->book = $_book;
        $this->reader = $_reader;
    }

    public function getExpirationDate() : DateTime {
        $date = $this->start->modify('+15 day');
        return $date;
    }

    public function getStartLoanDate() : DateTime {
        return $this->start;
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
        if($_review->strlen() <= 1000){
            $this->review = $_review;
        }
        else{
            throw new Exception('Error: numbers must be under 1000 charachters');
        }
        
    }
    
    public function getBook() : Book{
        return $this->book;
    }

}