<?php
namespace Bibliotek\Entity;
use DateTime;
use Book;
use User;
use Exception;

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
        $this->presentationDate = new DateTime();
    }
    
    public function DonationWithoutBook(string $_bookTitle, string $_bookISBN, int $_bookYear, string $_bookAuthor, string $_bookGenre, string $_bookDescription, int $_npages, User $_giver, int $_quantity){
        $this->book->title = $_bookTitle;
        $this->book->ISBN = $_bookISBN;
        $this->book->publishYear = $_bookYear;
        $this->book->authors->array_push( $_bookAuthor);
        $this->book->genres->array_push( $_bookGenre);
        $this->book->description = $_bookDescription;
        $this->book->pagesNumber = $_npages;
        $this->quantity = $_quantity;
        $this->giver = $_giver;
        $this->presentationDate = new DateTime();
    }

    public function setComment(string $_comment){
        if($_comment->strlen <= 500){
            $this->comment = $_comment;
        }
        else {
            throw new Exception('Error: must be under 500 characters');
        }
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
        $this->convalidationDate = new DateTime();
        $this->status = TRUE;
    }

    public function decline() {
        $this->convalidationDate = new DateTime();
    }

    public function  setBook(string $_bookTitle, string $_bookISBN, int $_bookYear, string $_bookAuthor, string $_bookGenre, string $_bookDescription, int $_npages){
        $this->book->title = $_bookTitle;
        $this->book->ISBN = $_bookISBN;
        $this->book->publishYear = $_bookYear;
        $this->book->authors->array_push( $_bookAuthor);
        $this->book->genres->array_push( $_bookGenre);
        $this->book->description = $_bookDescription;
        $this->book->pagesNumber = $_npages;
    }

    public function getBook() :Book{
        return $this->book;
    }

    public function setQuantity(int $_quantity){
        $this->quantity = $_quantity;
    }

    public function getQuantity() :int {
        return $this->quantity;
    }

    public function getGiver():User{
        return $this->giver;
    }

    public function getPresentationDate():DateTime{
        return $this->presentationDate;
    }

    public function getConvalidationDate():DateTime{
        return $this->convalidationDate;
    }

}