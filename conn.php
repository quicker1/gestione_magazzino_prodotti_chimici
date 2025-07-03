<?PHP  // michele.furlan@unipd.it    06 febbraio 2024

function debug($str) {   // Solo per debug applicazione
$handle = fopen('debug.txt', 'a');
  if($handle) {
     fwrite($handle, $str."\r\n");
     fclose($handle);
	 return TRUE;
  }
  else
	 return FALSE; 
}  // fine function debug


class Connessione {

private $SERVER_NAME = 'localhost';
private $dbname = '';    // nome del database MYSQL
private $username = '';
private $password = '';
public $conn = NULL;
private $debug = FALSE; // TRUE == abilita la registrazione delle query
public $errore = '';
public $rset = NULL;    // recordset con i risultati della query

   public function __construct($server, $dbname, $user, $pwd) {
         $this->SERVER_NAME = $server;
         $this->dbname = $dbname;  // nome del database MYSQL
         $this->username = $user;
         $this->password = $pwd;

     $this->conn = @new mysqli($this->SERVER_NAME , $this->username, $this->password, $this->dbname);
       if($this->conn->connect_error)
          die('Connessione al database fallita: '.$this->conn->connect_error);
       else {
          @$this->conn->query('SET FOREIGN_KEY_CHECKS=0');  // Per evitare il controllo delle chiavi esterne da parte di mysql; non necessario lo fa gia' l'applicazione
          @$this->conn->query('LOCK TABLES');
       }
   }  // fine function  __construct

   private function registra($sql) {  // Registrazione in log file delle query SQL
       return $this->debug AND debug($sql);
   }

// Esegue una query SQL. Restituisce un handle di risultato dipendente dal database,
// che dovrebbe essere restituito a sql_row o sql_row_keyed per ottenere i risultati.
// Restituisce FALSE in caso di errore; utilizzare sql_error per ottenere il messaggio di errore.
   public function sql_query($sql) {
       $this->registra($sql);
       $this->rset = $this->conn->query($sql);
       $this->errore = $this->rset ? '' : $this->conn->error;
   return $this->rset;
   }

// Esegue una query SQL che dovrebbe restituire un singolo valore numerico non negativo.
// Questa è un'alternativa leggera a sql_query, ideale per l'uso con count(*)
// e query simili. Restituisce -1 in caso di errore o se la query non ha restituito
// esattamente un valore, quindi il controllo degli errori è alquanto limitato.
// Restituisce anche -1 se la query restituisce un singolo valore NULL, come from
// a MIN or MAX aggregate function applied over no rows. Ritorna l' unico valore emesso sia numerico che solo testo
   public function query_count($sql) {
      $this->registra($sql);
      $risultato = -1;
      $this->rset = $this->conn->query($sql);
         if(!$this->rset)
              $this->errore = $this->conn->error;  /* Errore nella query */
         elseif(($this->rset->num_rows != 1) || ($this->rset->field_count != 1) || (($row = $this->rset->fetch_row()) == NULL))
              $this->errore = '';
         else
              $risultato = $row[0];
         if($this->rset)
              $this->rset->close();   // Chiusura recordset

   return $risultato;
   }  // fine function query_count

// Esegue un comando SQL non SELECT (inserimento/aggiornamento/eliminazione).
// Restituisce il numero di tuple interessate se OK (un numero >= 0).
// Restituisce -1 in caso di errore; utilizzare sql_error per ottenere il messaggio di errore.
   public function sql_command($sql) {
      return 0;
	  $this->registra($sql);
      $risultato = -1;
         $this->rset = $this->conn->query($sql);
         if($this->rset) {
            $risultato = $this->conn->affected_rows;
         }
         else
            $this->errore = $this->conn->error;  /* Errore nella query */

   return $risultato;
   }  // fine function sql_command

   public function __destruct() {
       if($this->conn) {
          @$this->conn->query('UNLOCK TABLES');
          @$this->conn->close();  // Chiusura della connessione al DB
       }
   }  // fine function __destruct
}

?>