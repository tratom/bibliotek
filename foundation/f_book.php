<?php

class f_book{

    private static string $class = 'f_book';
    private static string $table = 'books';
    private static string $values = "(:IDbook,:title,:ISBN,:publishYear,:authors,:genres,:description,:quantity,:pagesNumber)";

    //costruttore
    

     /**
	 * return the class name for the query construction
	 * @return string $class class name
	 */
    public static function getClass(){
        return static::$class;
    }

	/**
	 * return the table name for the query construction
	 * @return string $table table name
	 */
    public static function getTable(){
        return static::$table;
    }

	/**
	 * questo metodo restituisce l'insieme dei valori per la costruzione delle Query
	 * @return string $values nomi delle colonne della tabella
	 */
    public static function getValues(){
        return static::$values;
    }

    public static function bind(PDOStatement $stmt,eBook $book){
        $stmt->bindValue(':IDbook', NULL, PDO::PARAM_INT);
        $stmt->bindValue(':title', $book->getTitle(), PDO::PARAM_STR);
        $stmt->bindValue(':ISBN', $book->getISBN(), PDO::PARAM_INT);
        $stmt->bindValue(':publishYear', $book->getPublishYear(), PDO::PARAM_INT);
        $stmt->bindValue(':authors', $book->getAuthors(), PDO::PARAM_ARRAY);
        $stmt->bindValue(':genres', $book->getGenres(), PDO::PARAM_ARRAY);
        $stmt->bindValue(':description', $book->getDescription(), PDO::PARAM_STR);
        $stmt->bindValue(':quantity', $book->getQuantity(), PDO::PARAM_INT);
        $stmt->bindValue(':pagesNumber', $book->getPagesNumber(), PDO::PARAM_INT);
    }

     /**
    * Permette la store sul db
    * @param eBook $_book oggetto da salvare
    */
    public static function store(eBook $_book){
        $db=FDatabase::getInstance();
        $id = $db->storeDB(static::getClass(), $_book );
    }

    /**
     * Permette la load sul db
     * @param $val valore da ricercare nel campo $field
     * @param $field valore del campo della ricerca
     * @return object $book_ l'oggetto libro se presente
     */
    public static function loadByField($field, $val)
    {
		$_book = null;
        $tra = null;
        $db = FDatabase::getInstance();
        $result = $db->loadDB(static::getClass(), $field, $val);
        $rows_number = $db->interestedRows(static::getClass(), $field, $val);
        if (($result != null) && ($rows_number == 1)) {
            $ute = FUtenteloggato::loadByField("email", $result["emailUtente"]);
            $_book = new eUser($ute->getName(), $ute->getSurname(), $ute->getEmail(), $ute->getPassword(),$ute->getState());
        } else {
            if (($result != null) && ($rows_number > 1)) {
                $tra = array();
                for ($i = 0; $i < count($result); $i++) {
                    $ute[] = FUtenteloggato::loadByField("email", $result[$i]["emailUtente"]);
                    $cli[] = new ECliente($ute[$i]->getName(), $ute[$i]->getSurname(), $ute[$i]->getEmail(), $ute[$i]->getPassword(),$ute->getState());
                }
            }
        }
        return $cli;
    }


}
?>