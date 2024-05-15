<?php
namespace Bibliotek\Entity;
use Book;
use User;
use Datetime;
use Exception;

class eReservation {
    // attributes
    private int $number;
    private Book $book;
    private User $reader;
    private Datetime $estimated_date;
    //const
    const Max_Reservation_User = 15;
    public function Reservation(Book $_book, User $_reader){
        $this-> book = $_book;
        $this->reader = $_reader;
    }
    public function set_number(int $_number){
        if($_number>Max_Reservation_User){
            throw new Exception("Error, the reservation list is full ");
            }
        else{
            $this->number = $_number;
        }
    }
    public function get_number() : int {
        return $this->number;
    }
    public function get_estimated_date() : Datetime {
        return $this->estimate_date;
    }
    public function get_reader() : User{
        return $this->reader;
    }
    public function get_book() : Book{
        return $this->book;
    }
    // this function is used everytime a reader reserve a book and increases the reader's reservation number
    public function increment_number(){
        $incrementNumber = $this->number;
        if($incrementNumber>Max_Reservation_User){
            throw new Exception("Error, the maximum number of reservation has been reached");
              }
        else{
            $this->number = $_number+1;
        }
     }
     // this function is used everytime a copy of the book is added to the catalog and decreases the reader's reservation number 
     public function decrement_number(){
        $decrementNumber = $this->number;
        if ($decrementNumber==0){
            throw new Exception("it's your turn");
            }
        else{   
            $this->number = $_number-1;
        }
    }
    // this function calculates the estimated waiting time
    public function estimate_date(){
        $id_number = $this->number;
        $id_date = $this->estimate_date;
        for ($sum = 1; $sum<= $id_number; ++$sum){
            $id_date = $id_date->modify("+15 day");
        }
             
    }
    
        
        


    


}
?>