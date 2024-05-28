<?php

class f_DataBase{

	const DB_PARAM = array(
		'host' 		=> '127.0.0.1',
		'database' 	=> 'bibliotek',
		'user' 		=> 'root',
		'password' 	=> 'porcodio'
	);
	private static $instance;
	/** oggetto PDO che effettua la connessione al dbms */
	private $db;


	/** costruttore privato, l'unico accesso è dato dal metodo getInstance() */
	private function __construct ()
	{
		//global $host, $database, $username, $password;
		/*$host = "127.0.0.1"; //localhost
		$database = "fillspace";
		$username = "root";
		$password = ""; */

		try {
			//$this->db = new PDO("mysql:host=$host; dbname=$database", $username, $password);
			$this->db = new PDO ("mysql:dbname=".static::DB_PARAM['database'].";host=".static::DB_PARAM['host']."; charset=utf8;", static::DB_PARAM['user'], static::DB_PARAM['password']);

		} catch (PDOException $e) {
			echo "Attenzione errore: " . $e->getMessage();
			die;
		}

	}

    /**
     * Metodo che restituisce l'unica istanza dell'oggetto.
     * @return f_Database l'istanza dell'oggetto.
     */
    public static function getInstance ()
    { //restituisce l'unica istanza (creandola se non esiste gia')
        if (self::$instance == null) {
            self::$instance = new f_Database();
        }
        return self::$instance;
    }

    /**
     * Metodo che permette di salvare informazioni contenute in un oggetto Entity sul database.
     * @param _class classe da passare
     * @param obj oggetto da salvare
     */

    public function storeDB ($_class, $obj)
    {
        try {
            $this->db->beginTransaction();
            $query = "INSERT INTO " . $_class::getTable() . " VALUES " . $_class::getValues();    //where $_class indicate the foundation _class
            $stmt = $this->db->prepare($query);
            $_class::bind($stmt, $obj);  //bind is a foundation's _class method
            $stmt->execute();
            $__id = $this->db->lastInsert_Id();
            $this->db->commit();
            //$this->closeDbConnection();
            return $__id;
        } catch (PDOException $e) {
            echo "Attenzione errore: " . $e->getMessage();
            $this->db->rollBack();
            return null;
        }
    }
    /**
	 * Funzione che viene utilizzata per la load quando ci si aspetta che la query produca un solo risultato (esempio load per __id).
	 *
	 * @param $query query da eseguire
	 */
	public function loadDB ($_class, $_field, $__id)
	{
		try {
			// $this->db->beginTransaction();
			$query = "SELECT * FROM " . $_class::getTable() . " WHERE " . $_field . "='" . $__id . "';";
			$stmt = $this->db->prepare($query);
			$stmt->execute();
			$num = $stmt->rowCount();
			if ($num == 0) {
				$result = null;        //nessuna riga interessata. return null
			} elseif ($num == 1) {                          //nel caso in cui una sola riga fosse interessata
				$result = $stmt->fetch(PDO::FETCH_ASSOC);   //ritorna una sola riga
			} else {
				$result = array();                         //nel caso in cui piu' righe fossero interessate
				$stmt->setFetchMode(PDO::FETCH_ASSOC);   //imposta la modalità di fetch come array associativo
				while ($row = $stmt->fetch())
					$result[] = $row;                    //ritorna un array di righe.
			}
			//$this->closeDbConnection();
			return $result;
		} catch (PDOException $e) {
			echo "Attenzione errore: " . $e->getMessage();
			$this->db->rollBack();
			return null;
		}
	}

	/**
	 * returns the number of interested rows by the query
	 * @param _class classe interessata
	 * @param _field campo usato per la ricerca
	 * @param __id ,__id usato per la ricerca
	 */
	public function numberOfRows(string $_class, string $_field, string $__id) : int {
		try {
			$this->db->beginTransaction();
			$query = "SELECT * FROM " . $_class::getTable() . " WHERE " . $_field . "='" . $__id . "';";
			$stmt = $this->db->prepare($query);
			$stmt->execute();
			$num = $stmt->rowCount();
			$this->closeDbConnection();
			return $num;
		}
		catch (PDOException $e) {
			echo "Attenzione errore: " . $e->getMessage();
			$this->db->rollBack();
			return null;
		}
	}

	/**  Metodo che ritorna tutti gli attributi di un'istanza dando come parametro di ricerca il valore di un attributo
	 * passato come parametro
	 * @param _class ,nome della classe
	 * @param _field campo della classe
	 * @param _value ,_value della classe
	 */
	public function existDB (string $_class, string $_field, string $_value)
	{
		try {
			$query = "SELECT * FROM " . $_class::getTable() . " WHERE " . $_field . "='" . $_value . "'";
			$stmt = $this->db->prepare($query);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (count($result) == 1) return $result[0];  //rimane solo l'array interno
			else if (count($result) > 1) return $result;  //resituisce array di array
			$this->closeDbConnection();
		}
		catch (PDOException $e) {
			echo "Attenzione errore: " . $e->getMessage();
			return null;
		}
	}

	/**  Metodo che permette di aggiornare il valore di un attributo passato come parametro
	 *@param _class ,classe interessata
	*@param _field campo da aggiornare
	*@param _newvalue nuovo valore da inserire
	*@param _pk chiave primaria della classe interessata
	*/
	public function updateDB ($_class, $_field, $_newvalue, $_pk, $_value){
		try {
			$this->db->beginTransaction();
			$query = "UPDATE " . $_class::getTable() . " SET " . $_field . "='" . $_newvalue . "' WHERE " . $_pk . "='" . $_value . "';";
			$stmt = $this->db->prepare($query);
			$stmt->execute();
			$this->db->commit();
			$this->closeDbConnection();
			return true;
		}
		catch (PDOException $e) {
			echo "Attenzione errore: " . $e->getMessage();
			$this->db->rollBack();
			return false;
		}
	}


	/**  Metodo che chiude la connesione con il db */
	public function closeDbConnection (){
		static::$instance = null;
	}

}

?>