<?PHP   // michele.furlan@unipd.it  17 gennaio 2024
/*
$n = sscanf($auth, "%d\t%s %s", $id, $first, $last);
dal lunedi a venerdi esclusi sabato e domenica

https://www.w3schools.com/css/tryit.asp?filename=trycss_grid_lines  <-- USARE GRID
https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_grid_layout/Basic_concepts_of_grid_layout

Validazione espressioni regolari: https://regex101.com/

Conversioni di data
echo strtotime ("midnight +1 day +10 minutes"), "\n";
echo strtotime ("midnight +1 day +20 minutes"), "\n";
echo date("Y-m-d H:i:s",  strtotime ("midnight + 20 minutes"));
$dataOra = date("Y-m-d H:i:s", $timestamp);  conversione in DATETIME da UNIX_TIMESTAMP
MySQL FROM_UNIXTIME() returns a date /datetime from a version of unix_timestamp.

*/

class Calendario { // Generazione del calendario per la selezione dello slot di prenotazione per il ritiro del carrello - massimo due fascie giorno nel formato HH.MM-HH.MM HH.MM-HH.MM

private static $TABELLA_BASE = 'menu';
private static $TABELLA_VOCI = 'vocimenu';
private $giorni = array('DOMENICA' => '', 'LUNEDI' => '', 'MARTEDI' => '', 'MERCOLEDI' => '', 'GIOVEDI' => '', 'VENERDI' => '', 'SABATO' => '');
private $giorni_indice = array();
private $db = NULL;      // Handle connessione
private $max_giorni = 0; // Numero di giorni nel calendario - valore limite di colonne nella tabella
private $intervallo = 0; // Numero di minuti che compone l' intervallo
private $id_carrello = 0;  // id univoco che identifica il carrello oggetto di prenotazione per il ritiro
private $fasce_orarie = array();  // un array che verra' ordinato con il numero di minuti piu' basso fino al piu' alto con step pari a $intervallo
private $errore = FALSE;    // Se diventa TRUE non posso generare il calendario

   public function __construct(&$conn, $id) {
        $this->db = $conn;
        $this->id_carrello = $id;    // id del carrello nuovo in lavorazione
        $this->giorni_indice = array_keys($this->giorni);
        $this->errore = $this->assegna_max_giorni();
        if($this->errore) $this->errore = $this->assegna_intervallo();
        if($this->errore) $this->errore = $this->assegna_giorni();  // Carica l' intervallo nel giorno
   }  // fine costruttore della classe


   public function genera_calendario() {   // Dopo questo passaggio gli elementi residui non assegnati sono array di lunghezza pari a 0
        $tmp = [];
        if(!$this->errore)
            return 'Errore di validazione dei dati in input del calendario';

        foreach($this->giorni as $key => $val) {
           if(strlen($val) > 1)
               $this->giorni[$key] = $this->espandi_intervalli($val);   // Generazione degli array di tempo per ogni giorno
        }
        asort($this->fasce_orarie);  // Ordina i minuti dalla mezzanotte ottenuti

        foreach($this->fasce_orarie as $key => $value)    // Devo ordinare sia le chiavi che i valori - uso un array intermedio perche' dopo asort le chiavi non sono piu' ordinate
                $tmp[] = $value;
        $this->fasce_orarie = $tmp;  // Clonazione array

   return $this->genera_html();
   }  // fine function genera_calendario


   private function genera_html() {
      $colonna = 0;
      $riga = -2;      // -2 perche' le prime due righe saranno usate per emettere la data e giorno del calendario
      $prima = TRUE;   // Output della prima riga con i nomi dei giorni
      $giorno_start = date('w');  // O == domenica 6 == sabato
      $selezionato = '';    // Stringa vuota o 'checked' per il controllo INPUT type = radio

      $html = 'Selezione slot di prenotazione del ritiro'
              .'<STYLE>.wrapper_cal {margin-top:10px;display:grid;grid-template-columns:repeat('.($this->max_giorni + 1).', [col-start] auto [col-end]); grid-auto-rows: auto; font-size:8pt;gap:2px;background-color:#2196F3;padding:4px;margin-right:auto;margin-left:auto;width:100%}'
              .'.wrapper_cal > DIV {min-width:60px;background-color:rgba(255,255,255,0.8);text-align:center;padding:2px 2px;} DIV.grey_cal {background-color:#8d99ae;}'."\n"
              .' DIV.red_cal {vertical-align:middle;font-size:10pt;color:red;background-color:#EFEFEF;text-align:center;}</STYLE>'."\n".'<FORM name="frm_cal" onSubmit="return false;"><DIV class="wrapper_cal">';

      do {  // Tracciamento della colonna
        if($riga == -2) {
            $html .= '<DIV style="background-color:rgba(0,0,0,0);"><LABEL>&nbsp;&nbsp;</LABEL></DIV>';
        }
        else if($riga == -1)
            $html .= '<DIV><LABEL>&nbsp;FASCIA&nbsp;</LABEL></DIV>';
        else  {
            $html .= '<DIV>&nbsp;'.sprintf("%02d.%02d", floor($this->fasce_orarie[$riga] / 60), ($this->fasce_orarie[$riga]) % 60).'&nbsp;-&nbsp;'.sprintf("%02d.%02d", ((0 == (($this->fasce_orarie[$riga] + $this->intervallo) % 60) ? 1 : 0) + floor($this->fasce_orarie[$riga] / 60)), ($this->fasce_orarie[$riga] + $this->intervallo) % 60).'&nbsp;</DIV>';
        }
        do {  // Nel tracciare la colonna parto dal giorno della settimana corrente

          if($riga == -2) {
              $html .= '<DIV style="color:black;"><LABEL>'.$this->giorni_indice[$giorno_start++].'</LABEL></DIV>';  // Giorno della settimana
          }
          else if($riga == -1)
              $html .= '<DIV><LABEL><B>'.date('d-M', strtotime(date('Y-m-d').' +'.$colonna.' days')).'</B></LABEL></DIV>';  // Data della settimana

          else {
              if((time() < $this->genera_unix_times($colonna, $this->fasce_orarie[$riga])) AND !$this->isFestivo(date('d-m', $this->genera_unix_times($colonna))) AND isset($this->giorni[$this->giorni_indice[$giorno_start]]) AND is_array($this->giorni[$this->giorni_indice[$giorno_start]]) AND count($this->giorni[$this->giorni_indice[$giorno_start]]) > 0 AND in_array($this->fasce_orarie[$riga], $this->giorni[$this->giorni_indice[$giorno_start]])) {
                   if($this->verifica_conflitto($this->fasce_orarie[$riga], $colonna))
                        $html .= '<DIV class="red_cal"><B>X</B></DIV>';
                   else  {    // Lo slot e' prenotabile
                        $selezionato = $this->verifica_se_selezionato($this->fasce_orarie[$riga], $colonna, $this->id_carrello) ? ' checked' : '';
                        $html .= '<DIV><INPUT'.$selezionato.' onChange="aggiorna_prenotazione('.$this->id_carrello.',this.value);" type="radio" name="prenota" value="'.$this->genera_unix_times($colonna, $this->fasce_orarie[$riga]).'" /></DIV>';
                   }
              }  // Fine if clausole di verifica
              else
                  $html .= '<DIV class="grey_cal">&nbsp;</DIV>';   // Slot non prenotabile perche' giorno festivo
              $giorno_start++;
         }  // Fine else
         if($giorno_start > 6)   // Non posso andare oltre il sabato
              $giorno_start = 0;

         } while(++$colonna < $this->max_giorni);  // Tracciamento delle colonne

     $giorno_start = date('w');
     $html .= "\n";   // A capo riga ad ogni nuova riga
     $colonna = 0;
     $riga++;
     } while ($riga < count($this->fasce_orarie));  // Tracciamento delle righe

   $html .= '</DIV></FORM>';  // Chiusura del wrapper grid
   return $html;
   } // fine function genera_html


   private function verifica_se_selezionato(&$minuti, &$giorni, &$id)  {   // Restituisce true se tra i carrelli aventi id esiste gia' uno slot corrispondente
   // serve per il checked del controllo input radio
     $esito = FALSE;
     $qry = 'SELECT COUNT(id) FROM carrelli WHERE UNIX_TIMESTAMP(dataritiro) LIKE '.$this->genera_unix_times($giorni, $minuti).' AND id = '.$id.' LIMIT 1';
     $result = $this->db->query_count($qry);

     if(-1 == $result)
        return FALSE;   // Errore

     if($result > 0) {
        $esito = TRUE;   // Trovato un conflitto di prenotazione
     }
   return $esito;
   }  // fine function verifica_se_selezionato


   private function verifica_conflitto($minuti, $giorni) {    // riceve i minuti generati ed il giorno successivo alla data attuale - giorno parte da zero nell'esame delle colonne
   // La verifica va fatta solo sui carrelli dove esistono articoli da ritirare
     $secondi_dopo_m = $this->genera_unix_times($giorni, $minuti);   // sono i secondi dopo la mezzanotte della prenotazione che si vuole fare
     $esito = FALSE;

     $qry = 'SELECT COUNT(id) FROM carrelli WHERE UNIX_TIMESTAMP(dataritiro) BETWEEN '.($secondi_dopo_m).' AND '.($secondi_dopo_m + (60 * $this->intervallo) -30).' AND id IN(SELECT id_carrello FROM carrelloart WHERE NOT ritirato)';
     $result = $this->db->query_count($qry);

     if(-1 == $result)
        return FALSE;   // Errore query

     if($result > 0) {
         $esito = TRUE;   // Trovato un conflitto di prenotazione
     }
   return $esito;
   }  // fine function verifica_conflitto()


   private function genera_unix_times(&$g=0, &$m=0) {   // Converte in UNIX_TIMESTAMP dalla mezzanotte odierna in avanti di $g giorni e $m minuti
        return strtotime('midnight +'.$g.' '.($g > 1 ? 'days' : 'day').' +'.$m.($m > 1 ? ' minutes' : ' minute'));
   }


   private function assegna_max_giorni() {
       $result = $this->db->query_count('SELECT V.descrizione FROM '.(self::$TABELLA_VOCI).' AS V INNER JOIN '.(self::$TABELLA_BASE)." AS M ON V.id_menu = M.id WHERE M.tabella LIKE 'calendario' AND M.nomecampo LIKE 'step' AND V.valore LIKE 'step_giorni' LIMIT 1");
       if(-1 <> $result AND is_numeric($result)) {
           $this->max_giorni = intval($result);  // Unico risultato
           return TRUE;
       }
       else
           return FALSE;
   }  // fine function assegna_max_giorni


   private function assegna_intervallo() {
       $result = $this->db->query_count('SELECT V.descrizione FROM '.(self::$TABELLA_VOCI).' AS V INNER JOIN '.(self::$TABELLA_BASE)." AS M ON V.id_menu = M.id WHERE M.tabella LIKE 'calendario' AND M.nomecampo LIKE 'step' AND V.valore LIKE 'step_minuti' LIMIT 1");
       if(-1 <> $result AND is_numeric($result)) {
           $this->intervallo = intval($result);  // Unico risultato
           if($this->intervallo > 1) // Non accetto valori <= 1 minuto
              return TRUE;
           else
              return FALSE;
       }
       else
           return FALSE;
   }  // Fine function  assegna_intervallo


   private function assegna_giorni() {   // Estrae gli intervalli delle prenotazioni dati per i giorni della settimana
       $result = &$this->db->sql_query('SELECT V.valore, V.descrizione FROM '.(self::$TABELLA_VOCI).' AS V INNER JOIN '.(self::$TABELLA_BASE)." AS M ON V.id_menu = M.id WHERE M.tabella LIKE 'calendario' AND M.nomecampo LIKE 'giorni'");
       $expr = '/^((0\d|1\d|2[0-3])|(\d))\.\d{2}-(0\d|1\d|2[0-3])\.\d{2}(\40((0\d|1\d|2[0-3])|(\d))\.\d{2}-(0\d|1\d|2[0-3])\.\d{2})?$/';  // Validazione della stringa 00.00-00.00 accetta anche 0.00-0.00  ore.minuti
       $valore = '';
       if($result) {
           while($row = $result->fetch_assoc()) {
                  $valore =  trim($row['descrizione']);
                  if(preg_match($expr, $valore) OR 0 == strlen($valore))
                      $this->giorni[$row['valore']] = $row['descrizione'];  // Carico l'intervallo di prenotazione nel giorno
                  else
                      return FALSE;   // Invalido match string
           }
          $this->db->rset->close();
          return TRUE;
       }
       else
          return FALSE;
   }  // Fine function assegna_giorni


   private function convertiOraInMinuti(&$ora) {   // Riceve una string nel formato HH.MM tipo 09.30 e converte in minuti dalla mezzanotte
       list($ore, $minuti) = explode('.', $ora);       // Dividi l'ora e i minuti
       $minutiDallaMezzanotte = $ore * 60 + $minuti;   // Calcola i minuti totali
       return intval($minutiDallaMezzanotte);
   }  // Fine function convertiOraInMinuti


   private function espandi_intervalli(&$elemento) {   // Assegna il range di intervalli ai giorni
       $minuti_inizio = '';
       $minuti_fine = '';
       $res = array();
       $fasce_giorno =  explode(' ', $elemento);   // HH.MM-HH.MM HH.MM-HH.MM

       foreach($fasce_giorno as $fascia)  {
           list($minuti_inizio, $minuti_fine) = explode('-', $fascia);
                $minuti_inizio = $this->convertiOraInMinuti($minuti_inizio);
                $minuti_fine = $this->convertiOraInMinuti($minuti_fine);
// Generazione per ogni elemento GIORNO della array intervallo.... in base a $this->intervallo
           while($minuti_inizio < $minuti_fine) {
                  array_push($res, $minuti_inizio);    // Sono array  - archivio il primo minuto di start della prenotazione
                    if(!in_array($minuti_inizio, $this->fasce_orarie)) {
                        $this->fasce_orarie[] = $minuti_inizio;
                    }
           $minuti_inizio += $this->intervallo; // Continuo fine a raggiungere il limite orario massimo della prenotazione
           }   // Fine while
       } // Fine foreach

   return $res;
   }  // Fine function espandi_intervalli


   private function isFestivo(&$data) {   // In input giorno-mese GG-MM
      $festivitaItaliane = array(
           '01-01',  // Capodanno
           '06-01',  // Epifania
           '04-04',  // Pasqua
           '05-04',  // Lunedì dell'Angelo (Pasquetta)
           '25-04',  // Festa della Liberazione
           '01-05',  // Festa dei Lavoratori
           '02-06',  // Festa della Repubblica
           '13-06',  // Festa del Santo patrono
           '15-08',  // Assunzione di Maria
           '01-11',  // Ognissanti
           '08-12',  // Immacolata Concezione
           '25-12',  // Natale
           '26-12'   // Santo Stefano
      );
  return in_array($data, $festivitaItaliane);
  }  // Fine function isFestivo

}  // Fine classe Calendario

?>