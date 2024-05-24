<?php
echo 'ciao';
 function increment_number(){
        $incrementNumber = 18;
        if($incrementNumber>20){
            throw new Exception("Error, the maximum number of reservation has been reached");
              }
        else{
            $incrementNumber = $incrementNumber + 1;
            return $incrementNumber;
        }}
$keys = array("c");
$a = array_fill_keys($keys, array("banana", 3) );
function add_reservation(string $key, array $add){
    $num_user =4;
    if ($add == $keys){ 
        if ($num_user->increment_number()){
            $new_res = array("14", $num_user);
            array_push($a, $new_res);}
        }}
$a1 = $a->add_reservation('ciao', array('c'));
print_r($a1);
echo 'ciao';
    
?>