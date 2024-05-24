<?php
namespace Bibliotek\Entity;

use DateTime;
use Exception;

class eReservation {

    private eBook $book;
    private eUser $reader;
    private DateTime $estimated_date;
    private array $res_list = [];

    const Max_Reservation_User = 15;
    
    public function __construct(){

        $this->estimated_date = new Datetime();
    }

    public function set_number(int $_number){
        if($_number > self::Max_Reservation_User){
            throw new Exception("Errore, $_number maggiore di Max_Reservation_User ");
        } else {
            $this->number = $_number;
        }
    }

    public function get_number() : int {
        return $this->number;
    }

    public function get_estimated_date() : DateTime {
        return $this->estimated_date;
    }

    public function get_reader() : eUser {
        return $this->reader;
    }

    public function get_book() : eBook {
        return $this->book;
    }

    public function set_book(eBook $_book){
        $this->book = $_book;
    }

    public function set_reservation_list(eBook $_book, eUser $_user){
        $this->res_list = array_fill_keys([$this->book], $this->book_number);
    }

    public function get_reservation_list() : array {
        return $this->res_list;
    }

    public function increment_number(int $numb){
        $incrementNumber = $numb + 1;
        if($incrementNumber > self::Max_Reservation_User){
            throw new Exception("Errore, lista di prenotazione piena");
        } 
        return $incrementNumber;
    }

    public function decrement_number(int $numb){
        $decrementNumber = $numb - 1;
        if ($decrementNumber == 0){
            throw new Exception('è il tuo turno');
        } 
        return $decrementNumber;
    }

    public function estimate_date($numb) : DateTime {
        $id_date = clone $this->estimated_date;  
        for ($sum = 1; $sum <= $numb; $sum++){
            $id_date->modify("+15 days");
        }
        return $id_date;
        
    }

    public function add_reservation(eBook $_book, eUser $_user, int $numb){
    if ($_book->getQuantity()==0){
        $incrementNumber = $this->increment_number($numb);
        $this->res_list= [$_user, $incrementNumber];
        $this->estimated_date = $this->estimate_date($incrementNumber);
        $name = $_user->getName();
        $surname = $_user->getSurname();
        $User = $name = $_user->getName();
        echo ("$User, sei stato aggiunto alla lista delle prenotazioni, sei il numero {$incrementNumber}, 
        data stimata di prelievo: " . $this->estimated_date->format('Y-m-d'));
    }
    return $this->res_list;
}

public function remove_reservation(eUser $_user) {
    $found = false;
    foreach ($this->res_list as $key => $value) {
        if ($value[1] === $_user) {
            unset($this->res_list[$key]);
            $found = true;
            break;
        }
    }

    if ($found) {
        $this->res_list = array_values($this->res_list);  
        foreach ($this->res_list as $index => $reservation) {
            $this->res_list[$index][2] = $index + 1;
        }
        $name = $_user->getName();
        $surname = $_user->getSurname();
        $User = $name . " " . $surname;
        echo ("$User sei stato rimosso dalla lista delle prenotazioni\n");
        return $this->res_list;
    } else {
        throw new Exception("Utente non trovato nella lista delle prenotazioni");
    }
}

}

    
    
    
    

    

    ?>
