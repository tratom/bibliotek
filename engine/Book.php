<?php
namespace Bibliotek\Entity;
use DateTime;
use Exception;

class Book{

    private string $title = '';
    private int $ISBN = 0;
    private DateTime $publishYear;
    private $authors = array();
    private $genres = array();
    private string $description = '';
    private int $quantity = 0;
    private int $pagesNumber = 0;

    public function setTitle(string $_title){
        $this->title = $_title;
    }
    public function setISBN(int $_ISBN){
        $this->ISBN = $_ISBN;
    }
    public function setPublishYear(DateTime $_publishYear){
        $this->publishYear = clone $_publishYear;
    }
    public function setOneAuthor(string $_author){
        array_push($this->authors, $_author);
    }
    public function setManyAuthors(array $_authors){
        $this->authors = $_authors;
    }
    public function setOneGenre(string $_genre){
        array_push($this->genres, $_genre);
    }
    public function setManyGenres(array $_genres){
        $this->genres = $_genres;
    }
    public function setDescription(string $_description){
        $this->description = $_description;
    }
    public function setQuantity(int $_quantity){
        $this->quantity = $_quantity;
    }
    public function increaseQuantity(int $_amount){
        $this->quantity += $_amount;
    }
    public function setPagesNumber(int $_pagesNumber){
        $this->pagesNumber = $_pagesNumber;
    }
    public function getTitle() : string {
        return $this->title;
    }
    public function getISBN() : int {
        return $this->ISBN;
    }
    /**
     * @param int $_format Format of the date: 1 - string Y-m-d H:i:s, 2 - DateTime object
     */
    public function getPublishYear(int $_format = 1) {
        switch ($_format) {
            case 1:
                return $this->publishYear->format("Y");
            case 2:
                return clone $this->publishYear;
            default:
                throw new Exception('Error: parameter out of range');
        }
    }
    public function getAuthors() : array {
        if (isset($this->authors))
            return $this->authors;
        else
            return array();
    }
    public function getGenres() : array {
        if (isset($this->genres))
            return $this->genres;
        else 
            return array();
    }
    public function getDescription() : string {
        return $this->description;
    }
    public function getQuantity() : int {
        return $this->quantity;
    }
    public function getPagesNumber() : int {
        return $this->pagesNumber;
    }
    
    public function __construct(array $_values){
        try {
            $this->title = $_values['title'];
            $this->ISBN = $_values['ISBN'];
            $this->publishYear = clone $_values['publishYear'];
            $this->authors = $_values['authors'];
            $this->genres = $_values['genres'];
            if (isset($_values['description'])) $this->description = $_values['description'];
            $this->quantity = $_values['quantity'];
            $this->pagesNumber = $_values['pagesNumber'];
        }
        catch (Exception $e) {
            throw new Exception('Error: bad array configuration '.$e);
        }
        
    }
    public function __toString() {
        $output = str_pad('Title:', 20);
        $output = $output.$this->getTitle()."\n";
        $output = $output.str_pad('ISBN:', 20);
        $output = $output.$this->getISBN()."\n";
        $output = $output.str_pad('Publish Year:', 20);
        $output = $output.$this->getPublishYear(1)."\n";
        foreach ($authors = $this->getAuthors() as $author) {
            $output = $output.str_pad('Author:', 20);
            $output = $output.$author."\n";
        }
        foreach ($genres = $this->getGenres() as $genre) {
            $output = $output.str_pad('Genre:', 20);
            $output = $output.$genre."\n";
        }
        if ($this->description != ''){
            $output = $output.str_pad('Description:', 20);
            $output = $output.$this->getDescription()."\n";
        }
        $output = $output.str_pad('Quantity:', 20);
        $output = $output.$this->getQuantity()."\n";
        if ($this->pagesNumber != 0){
            $output = $output.str_pad('Pages Number:', 20);
            $output = $output.$this->getPagesNumber()."\n";
        }
        return $output;
    }
}
?>