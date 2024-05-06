<?php
namespace Bibliotek\Entity;
use DateTime;
use Book;
use User;


class Donation{

    //attributes
    private DateTime presentiationDate;
    private DateTime convalidationDate;
    private Book book;
    private User giver;
    private int quantity;
    private bool status;
    private string comment;
}