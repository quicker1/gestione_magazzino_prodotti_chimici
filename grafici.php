<?PHP  // michele.furlan@unipd.it   09 febbraio 2023

class Grafico {   // Classe per la generazione dei grafici
private $db = NULL;   // Handle connessione
private $tipo = '';   // Tipo di grafico 'PROD' 0 'GROUP'
private $start = 0;   // DEFAULT valori
private $end = 0;
private $id = 0;
private $step = 0;

 public function __construct(&$conn) {
    $this->db = $conn;
 }

 private function chart_query($qry) {   // Restituisce i recordset delle due possibili query dove la prima genera i dati per il grafico la seconda il titolo p == 1 o p == 2
   if($qry == 0 AND $this->tipo == 'PROD') // i prodotti rappresentati sono tutti quelli caricati nei carrelli - confermati o meno o non ritirati o meno
      return $this->db->sql_query('SELECT FROM_UNIXTIME('.$this->start.', \'%Y-%b-%d\') AS giorno, IFNULL(SUM(CA.quantita), 0) AS totale FROM carrelloart AS CA WHERE CA.id_prodotto ='
                                 .$this->id.' AND CA.id_sessione = 0 AND (UNIX_TIMESTAMP(CA.dataritiro) BETWEEN '.$this->start.' AND '.($this->start+$this->step).')');
   if($qry == 1 AND $this->tipo == 'PROD') {
       $result = $this->db->query_count('SELECT nome FROM prodotti WHERE id='.$this->id.' LIMIT 1');
   return (-1 <> $result ? 'Q.ta immessa nei carrelli di prodotto: '.strtoupper($result) : 'ERRORE TITOLO GRAFICO');  // Restituisce il titolo del grafico
   }
/*Ipotesi generazione grafico per le spese del gruppo selezionato in $id */
   if($qry == 0 AND $this->tipo == 'GROUP') // i prodotti rappresentati sono tutti quelli caricati nei carrelli - confermati o meno o non ritirati o meno
       return $this->db->sql_query('SELECT FROM_UNIXTIME('.$this->start.', \'%Y-%b-%d\') AS giorno, ROUND(SUM(CA.quantita * A.prezzo * (1+A.iva/100) * (1-A.sconto/100)), 2) AS totale '
                                   .'FROM carrelli AS C JOIN carrelloart AS CA ON C.id = CA.id_carrello JOIN articoli AS A ON CA.id_articolo = A.id WHERE CA.quantita > 0 AND CA.ritirato '
                                   .'AND C.gruppo LIKE \''.$this->id.'\' AND (UNIX_TIMESTAMP(CA.dataritiro) BETWEEN '.$this->start.' AND '.($this->start+$this->step).')');
   if($qry == 1 AND $this->tipo == 'GROUP') {
       return 'Totale+iva+sconto in euro del gruppo: '.strtoupper($this->id);        // Restituisce il titolo del grafico
   }
 } // function chart_query


 public function chart_display($id=0, $start, $end, $divisioni=30, $tipo='PROD') {
// $start e $and sono rappresenti in UNIXTIMESTAMP
    $this->id = $id;
    $this->start = $start;
    if(abs($start - $end) <= 86400) $end = $start;  // Controllo necessario per evitare loop infiniti vedi SECONDI_AGGIUNTIVI
    $this->end = $end;
// Se l'intervallo di tempo deve essere un giorno solo, $start deve essere uguale ad $end a meno di un giorno
// Calcola lo step in secondi
    $step = abs($end -$start) / $divisioni;
// Trova il più grande multiplo di 86400 (secondi in un giorno) che sia minore o uguale allo step
    $step = floor($step / 86400);       // 86400 secondi in un giorno
    $step = $step * 86400;              // Valore di incremento dal valore iniziale $start
    $this->step = $step;
    $this->tipo = $tipo;
 return $this->chart_gen();
 } // fine function chart_display


 private function chart_gen() {  // Data inzio e data fine entro la quale includere la rappresentazione dei dati $id == id prodotto in esame
 $output = array();
//debug('INIZIO: '.$start.' FINE: '.$end.' STEP: '.$step);
   do {  // Ciclo sulle quantita
       $result = &$this->chart_query(0);
       if($this->db->errore)
            return 'GRAPH errore: '.$this->db->errore;  // Interrompe il processo di generazione
       $row = $result->fetch_assoc();
       $output[] = $row;
   @mysqli_free_result($result);
   $this->start += $this->step;
   } while ($this->start < $this->end);

// Inizializza le stringhe $labes e $dati
  $labels = 'labels : [';
  $dati = 'data : [';
// Itera sull'array per costruire le stringhe $labes e $dati
  foreach ($output as $elemento) {
       $labels .= '"' . $elemento['giorno'] . '", ';
       $dati .= $elemento['totale'] . ', ';
  }
// Rimuovi gli ultimi spazi e virgole dalle stringhe e aggiungi parentesi graffe e parentesi quadra
  $labels = rtrim($labels, ', ') . ']';
  $dati = rtrim($dati, ', ') . ']';
  $titolo = $this->chart_query(1);

 return $this->genera_html($labels, $dati, $titolo);
 }  // fine function chart_gen()


private function genera_html(&$labels, &$dati, &$titolo) {
//  $.cachedScript( e' definita a parte come estenzione di $.getScript
$html = <<<HTMLE
<DIV>
  <CANVAS id="myChart_prod"></CANVAS>
</DIV>
<SCRIPT type="text/javascript">
  $.cachedScript('./js/chart.min.js').done(function() {
  const ctx = document.getElementById('myChart_prod');
  new Chart(ctx, {
    type: 'bar',
    data: {
      %ETICHETTE%,
      datasets: [{
        label: '%TITOLO%',
        %PRODOTTI%,
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
  });
</SCRIPT>
HTMLE;

return str_replace('%ETICHETTE%', $labels, str_replace('%PRODOTTI%', $dati, str_replace('%TITOLO%', $titolo, $html)));
}  // fine function genera_html

}  // Fine class Grafico

/*
https://www.phind.com  AI generativa

In un browser vorrei visualizzare un grafico a barre
usando Chart.js che si appoggia alla libreria jQuery;
lato server invece uso mysql ed il linguaggio PHP.
La tabella di partenza che contiene i dati da rappresentare nel
grafico è quella sottostante avente nome: articoli,
dove il campo quantita rappresenta la quantita di articoli
presenti al una certa data: datainserimento.
Il grafico deve essere di tipo a barre dove in
in ascissa c'e' il tempo, in ordinata la quantita di articoli
inserita in un certa intervallo di tempo.
Il grafico dovrebbe prevedere da 0 ad un massimo di 30 barre
in ordinata dove c'è il tempo per un intervallo di tempo scelto a piacere
dall'utente che possiamo fissare in datainiziale e datafinale,
dove intervallo = datafinale-datainiziale/30

Puoi creare il codice lato server e lato client per
generare il grafico ?

CREATE TABLE articoli (
   id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
   quantita INT(11) DEFAULT 0,
   datainserimento DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
}


seconda domanda:
Supponiamo che ho i seguenti dati nella variabile $esito in PHP in formato json $esito = '[{"giorno":"AAA","totale":"98"},  {"giorno":"BBB","totale":"23"}]';
e voglio ottenere usando il linguaggio PHP due stringhe risultanti assegnate alle variabili $a e $b
$a = 'label : ["AAA", "BBB"]', e $b ='data : [98, 23]'
Dove nella variabile $a ci sono i valori ricavati da "giorno" mentre nella variabile $b ci sono i valori ricavati da "totale".
"giorno" e "totale" sono presenti nella stringa $esito.
*/

?>