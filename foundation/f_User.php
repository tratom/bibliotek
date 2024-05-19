<?php

class f_User{
    private static string $class = 'fUser';
    private static string $table = 'users';
    private static string $values="(:idUsr,:name,:surname,:birthDay,:email,:password,:maxLoanNum,:maxLoanDur,:status,:reputation,:role)";

    /** costruttore*/ 
    public function __construct(){}

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


// todo competare
    public static function bind(PDOStatement $stmt,eUser $user){
        $stmt->bindValue(':idUsr', NULL, PDO::PARAM_INT);
        $stmt->bindValue(':name', $user->getName(), PDO::PARAM_STR);
        $stmt->bindValue(':surname', $user->getSurnameArrivalDate(), PDO::PARAM_STR);
        $stmt->bindValue(':birthDay', $user->getBirthDay(), PDO::PARAM_STR);
        $stmt->bindValue(':email', $user->getEmail()->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':password', $user->getArrival()->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':maxLoanNum', $user->getWeight(), PDO::PARAM_STR);
        $stmt->bindValue(':maxLoanDur', $user->getDescription(), PDO::PARAM_STR);
        $stmt->bindValue(':status', $user->getVisibility(), PDO::PARAM_BOOL);
        $stmt->bindValue(':reputation', $user->getEmailWriter()->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':role', $user->getEmailWriter()->getEmail(), PDO::PARAM_STR);
    }

    /**
    * Permette la store sul db
    * @param eUser $usr oggetto da salvare
    */
    public static function store(eUser $usr){
        $db=FDatabase::getInstance();
        $id = $db->storeDB(static::getClass(), $usr );
    }

// todo completare

    /**
     * Permette la load sul db
     * @param $id valore da ricercare nel campo $field
     * @param $field valore del campo della ricerca
     * @return object $cli l'oggetto cliente se presente
     */
    public static function loadByField($field, $id)
    {
		$usr = null;
        $tra = null;
        $db = FDatabase::getInstance();
        $result = $db->loadDB(static::getClass(), $field, $id);
        $rows_number = $db->interestedRows(static::getClass(), $field, $id);
        if (($result != null) && ($rows_number == 1)) {
            $ute = FUtenteloggato::loadByField("email", $result["emailUtente"]);
            $usr = new eUser($ute->getName(), $ute->getSurname(), $ute->getEmail(), $ute->getPassword(),$ute->getState());
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