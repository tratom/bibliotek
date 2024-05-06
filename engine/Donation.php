<?php
namespace Bibliotek\Entity;
use DateTime;
use Book;
use User;


class Donation{

    //attributes
    private DateTime $presentiationDate;
    private DateTime $convalidationDate;
    private Book $book;
    private User $giver;
    private int $quantity;
    private bool $status = FALSE; //the default value for status is FALSE, which means that the donation is not approved
    private string $comment;


    //functions
    public function DonationWithBook(Book $_book, User $_user, int $_quantity){
        $this->book = $_book;
        $this->giver = $_user;
        $this->quantity = $_quantity;
    }
    
    public function DonationWithoutBook(string $_bookTitle, string $_bookISBN, int $_bookYear, string $_bookAuthor, string $_bookGenre, string $_bookDescription, User $_giver, int $_quantity){
        $this->book->title = $_bookTitle;
        $this->book->ISBN = $_bookISBN;
        $this->book->year = $_bookYear;
        $this->book->author = $_bookAuthor;
        $this->book->genre = $_bookGenre;
        $this->book->description = $_bookDescription;
        $this->quantity = $_quantity;
        $this->giver = $_giver;
    }

    public function setComment(string $_comment){
        $this->comment = $_comment;
    }

    public function getComment() : string{
        return $this->comment;
    }

/* the following function returns diffrent values for the status of the donation: 
    -if the convalidation date is null, which means that the donation request is not yet been processed by an administrator, returns 0
    -if the convalidation date is not null and the status is false, it means that the donation request has been declined, returns 1
    -if the convalidation date is not null and the status is true, it means that the donation request has been accepted, return 2
*/
    public function getStatus() : int{
        if(is_null($this->convalidationDate)){
            return 0;
        }
        elseif($this->status == FALSE){
            return 1;
        }
        elseif($this->status == TRUE){
            return 2;
        }
    }

    public function approve() {
        $this->status = TRUE;
    }
}