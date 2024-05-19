<?php

class f_User{
    private static string $class = 'f_User';
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
     * returns the value set for the query construction
	 * @return string $values table's column name
	 */
    public static function getValues(){
        return static::$values;
    }

    public static function bind(PDOStatement $_stmt,e_User $_user){
        $_stmt->bindValue(':idUsr', NULL, PDO::PARAM_INT);
        $_stmt->bindValue(':name', $_user->getName(), PDO::PARAM_STR);
        $_stmt->bindValue(':surname', $_user->getSurname(), PDO::PARAM_STR);
        $_stmt->bindValue(':birthDay', $_user->getBirthDay(1), PDO::PARAM_STR);
        $_stmt->bindValue(':email', $_user->getEmail(), PDO::PARAM_STR);
        $_stmt->bindValue(':password', $_user->getPassword(), PDO::PARAM_STR);
        $_stmt->bindValue(':maxLoanNum', $_user->getMaxLoanNum(), PDO::PARAM_INT);
        $_stmt->bindValue(':maxLoanDur', $_user->getMaxLoanDur(), PDO::PARAM_INT);
        $_stmt->bindValue(':status', $_user->getStatus(), PDO::PARAM_BOOL);
        $_stmt->bindValue(':reputation', $_user->getReputation(), PDO::PARAM_INT);
        $_stmt->bindValue(':role', $_user->getRole(), PDO::PARAM_STR);
    }

    /**loadBy
    * store the object's attribute values into db
    * @param e_User $user object to be saved
    */
    public static function store(e_User $_user) : void {
        $db = f_Database::getInstance();
        $_value = $db->storeDB(static::getClass(), $_user);
    }

    /**
     * load object's attribute values from db
     * @param $_value value to search in $_field
     * @param $_field column name
     * @return object $user user's object
     */
    public static function load(string $_field, string $_value){
		$user = null;
        $db = f_Database::getInstance();
        $result = $db->loadDB(static::getClass(), $_field, $_value);
        $rows_number = $db->numberOfRows(static::getClass(), $_field, $_value);

        // var_dump($result);
        // print($rows_number);

        if (($result != null) && ($rows_number == 1)) {
            $tempBDay = new DateTime($result['birthDay']);
            $userData = array(
                'name'      => $result['name'],
                'surname'   => $result['surname'],
                'birthDay'  => $tempBDay,
                'email'     => $result['email'],
                'password'  => $result['password'],
                'maxLoanNum'=> $result['maxLoanNum'],
                'maxLoanDur'=> $result['maxLoanDur'],
                'status'    => $result['status'],
                'reputation'=> $result['reputation'],
                'role'      => $result['role']
            );
            $user = new e_User($userData);
        }
        else {
            if (($result != null) && ($rows_number > 1)) {
                foreach ($result as $element) {
                    $tempBDay = new DateTime($element['birthDay']);
                    $userData = array(
                            'name'      => $element['name'],
                            'surname'   => $element['surname'],
                            'birthDay'  => $tempBDay,
                            'email'     => $element['email'],
                            'password'  => $element['password'],
                            'maxLoanNum'=> $element['maxLoanNum'],
                            'maxLoanDur'=> $element['maxLoanDur'],
                            'status'    => $element['status'],
                            'reputation'=> $element['reputation'],
                            'role'      => $element['role']
                    );
                    $user[] = new e_User($userData);
                }
            }
        }
        return $user;
    }

    /**
     * Metodo che verifica se esiste un cliente con un dato valore in uno dei campi
     * @param $_value valore da usare come ricerca
     * @param $_field campo da usare come ricerca
     * @return true se esiste il cliente, altrimenti false
     */
    public static function exist($_field, $_value) {
        $db = f_Database::getInstance();
        $result = $db->existDB(static::getClass(), $_field, $_value);
        if($result!=null) return true;
        else return false;
    }

    /** Metodo che permette l'aggiornamento del valore di un attributo passato come parametro   */

	public static function update($_field, $_newvalue, $_pk, $_value){
        $db = f_Database::getInstance();
        $result = $db->updateDB(static::getClass(), $_field, $_newvalue, $_pk, $_value);
        if($result) return true;
        else return false;
    }
}

?>