<?php
include 'eBook.php'
$data1 = new Datetime();
$data1->setDate(14, 03, 2002);
$_book1 = new eBook("libro1", 4, $data1, "autore1", "bello1", 0, 15);
$data2->setDate(14, 03, 2005);
$_book2 = new eBook("libro2", 4, $data2, "autore2", "bello2", 0, 15);
$data3->setDate(14, 04, 2002);
$_book3 = new eBook("libro2", 4, $data2, "autore2", "bello2", 0, 15); 
$_user1 = new eUser("name1", "surname1", $birth1, "email1", "pass1");
$birth1 = new Datetime();
$_user2 = new eUser("name2", "surname1", $birth1, "email1", "pass1");
$_user3 = new eUser("name3", "surname1", $birth, "email1", "pass1");
$_user4 = new eUser("name4", "surname1", $birth1, "email1", "pass1");
$_user5 = new eUser("name5", "surname1", $birth1, "email1", "pass1");
$_user6 = new eUser("name6", "surname1", $birth1, "email1", "pass1");
$_user7 = new eUser("name7", "surname1", $birth1, "email1", "pass1");
$_user8 = new eUser("name8", "surname1", $birth1, "email1", "pass1");
$_user9 = new eUser("name9", "surname1", $birth1, "email1", "pass1");
$_user10 = new eUser("name10", "surname1", $birth1, "email1", "pass1");
$_user11 = new eUser("name11", "surname1", $birth1, "email1", "pass1");
$_user12 = new eUser("name12", "surname1", $birth1, "email1", "pass1");
$_user13 = new eUser("name13", "surname1", $birth1, "email1", "pass1");
$_user14 = new eUser("name14", "surname1", $birth1, "email1", "pass1");
$_user15 = new eUser("name15", "surname1", $birth1, "email1", "pass1");
$_user16 = new eUser("name16", "surname1", $birth1, "email1", "pass1"); 
$Reservation1 = new eReservation();
$Reservation1->set_number(16);

?>
