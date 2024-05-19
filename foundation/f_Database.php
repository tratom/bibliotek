<?php

$dbParam = array(
    'host' => '127.0.0.1',
    'database' => 'bibliotek',
    'user' => 'root',
    'password' => 'porcodio'
);

class f_DataBase{
    /** l'unica istanza della classe */
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
			$this->db = new PDO ("mysql:dbname=".$dbParam['database'].";host=".$dbParam['host']."; charset=utf8;", $dbParam['user'], $dbParam['password']);

		} catch (PDOException $e) {
			echo "Attenzione errore: " . $e->getMessage();
			die;
		}

	}

    /**
     * Metodo che restituisce l'unica istanza dell'oggetto.
     * @return FDataBase l'istanza dell'oggetto.
     */
    public static function getInstance ()
    { //restituisce l'unica istanza (creandola se non esiste gia')
        if (self::$instance == null) {
            self::$instance = new FDatabase();
        }
        return self::$instance;
    }

    /**
     * Metodo che permette di salvare informazioni contenute in un oggetto Entity sul database.
     * @param class classe da passare
     * @param obj oggetto da salvare
     */

    public function storeDB ($class, $obj)
    {
        try {
            $this->db->beginTransaction();
            $query = "INSERT INTO " . $class::getTable() . " VALUES " . $class::getValues();    //where $class indicate the foundation class
            $stmt = $this->db->prepare($query);
            $class::bind($stmt, $obj);  //bind is a foundation's class method
            $stmt->execute();
            $id = $this->db->lastInsertId();
            $this->db->commit();
            $this->closeDbConnection();
            return $id;
        } catch (PDOException $e) {
            echo "Attenzione errore: " . $e->getMessage();
            $this->db->rollBack();
            return null;
        }
    }
    /**
	 * Funzione che viene utilizzata per la load quando ci si aspetta che la query produca un solo risultato (esempio load per id).
	 *
	 * @param $query query da eseguire
	 */
	public function loadDB ($class, $field, $id)
	{
		try {
			// $this->db->beginTransaction();
			$query = "SELECT * FROM " . $class::getTable() . " WHERE " . $field . "='" . $id . "';";
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
			$this->closeDbConnection();
			return $result;
		} catch (PDOException $e) {
			echo "Attenzione errore: " . $e->getMessage();
			$this->db->rollBack();
			return null;
		}
	}
}

?>