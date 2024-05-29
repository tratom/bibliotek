<?php
class fReservation{
    private static $class = "fReservation";
    private static $table;
    private static $val = "(:number,:book,:reader,:estimated_date)";
    public function __construct(){}
    /** metodo che lega i parametri da inserire con quelli della INSERT
    * @param PDOStatement $stmt 
    * @param eReservation $res luogo in cui i dati devono essere inseriti nel DB */    
    public static function bind($stmt, eReservation $res){
        $stmt->bindValue(':number', NULL, PDO::PARAM_INT); //number è NULL perchè viene generato in automatico
        $stmt->bindValue(':book', $res->get_book(), PDO::PARAM_eBook);
        $stmt->bindValue(':reader', $res->get_reader(), PDO::PARAM_eUser);
        $stmt->bindValue(':estimated_date', $res->estimate_date(), PDO::PARAM_Datetime);
    }        
    //metodo di restituzione nome classe (costruzione query)
    public static function get_class(){
        return self::$class;
    }
    //metodo di restituzione nome tabella (costruzione query)
    public static function get_table_name(){
        return self::$table;
    }
    //metodo di restituzione dei valori (costruzione query)
    public static function get_values(){
        return self::$val;
    }
    /** funzione che permette il salvataggio delle reservation nel DB
     * @param $res reservation da salvare
     * @return $number della reservation salvata
     */
    public static function store(eReservation $res){
        $store_db = fDatabase::getIstance();
        $number = $store_db->storeDB(static::getClass(), $res);
        if ($number){
            return $number;
        }
        else{
            return null;
        }

    }
    /** funzione che permette di verificare se esiste una reservation nel DB 
     * @param $riga valore riga su cui si vuole verificare l'esistenza
     * @param $col valore colonna
     * @param return bool $present 
    */
    public static function exist($riga, $col){
        $present = false;
        $exist_db = fDatabase::getIstance();
        $ris =  $exist_db->existDB(static::getClass(), $col, $riga);
        if($ris!=null){
            $present = true;
        }
        return $present;

    }

}
?>