<?PHP  // michele.furlan@unipd.it  31 gennaio 2024
include ('./config.php');
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Content-Security-Policy: default-src \'self\'');  // Aggiunta il 13 maggio 2024

// Le operazioni crud non possono essere eseguite da utenti anonimi
if(!$enable_sso_auth)
     die('<P class="classe_tag_p"><BR /><BR />&nbsp;&nbsp;&nbsp;Diritti insufficienti per eseguire il comando<BR />&nbsp;&nbsp;&nbsp;Chiudere il browser e fare nuovamente il login: '.URI_SERVER_ESECUZIONE.' </P>');  // Abortisco esecuzione - solo gli autenticati possono operare
if(!$app_admin AND isset($_REQUEST['tbl_azione']) AND in_array($_REQUEST['tbl_azione'], $rpt['divieto']))
     die('<P class="classe_tag_p"><BR /><BR />&nbsp;&nbsp;&nbsp;Permesso negato a livello utente per eseguire il comando: '.$_REQUEST['tbl_azione'].'<BR />&nbsp;&nbsp;&nbsp;Chiudere il browser e fare nuovamente il login: '.URI_SERVER_ESECUZIONE.' </P>');  // Abortisco esecuzione - solo gli autenticati possono operare

// Prima di tutto verifico la durata della sessione
if(isset($_SESSION['LAST_ACTIVITY'])) {  // != lista_in_attesa per evitare doppia generazione del messaggio
  $durata_sessione_secondi = ((null !== ini_get('session.cookie_lifetime')) AND is_numeric(ini_get('session.cookie_lifetime'))) ? intval(ini_get('session.cookie_lifetime')) : DURATA_SESSIONE_OAUT2;
     if((time() - $_SESSION['LAST_ACTIVITY']) > intval($durata_sessione_secondi)) { // Ho superato il limite quindi esco e avviso l'utente
          session_unset();     // unset $_SESSION variable for the run-time
          session_destroy();   // destroy session data in storage
          die('<DIV class="div_sessione_oaut2"><B>Sessione scaduta !</B><BR />'
              .'Durata della sessione in ore: '.(intval(($durata_sessione_secondi/60)/60)).'<BR />'
              .'E\' necessario autenticarsi nuovamente.<BR /><BR />'
              .'Per rientrare tornare in:&nbsp;<A href="'.URI_SERVER_ESECUZIONE.'">'.URI_SERVER_ESECUZIONE.'</A></DIV>');
     }
}  // fine if durata sessione

if(isset($_REQUEST['tbl_id'])) {  // Per evitare id duplicati in caso di tabella verticale
    $_REQUEST['tbl_id'] = explode('v', $_REQUEST['tbl_id'])[0];
}

switch($_REQUEST['tbl_azione']) {
  case 'veditabella' : {
    if(isset($_REQUEST['servizio_richiesto']) AND 'HOME' == $_REQUEST['servizio_richiesto']) {
         include('./infohome.php');
         echo TESTO_HOME_PAGE_USER.TESTO_HOME_PAGE_ADMIN.TESTO_RETE_DISC;
    }
    elseif(check_alias_table($_REQUEST['tbl_nome']))  { // genero la tabella verticale
         echo genera_tabella_alias($conn_obj, check_alias_table($_REQUEST['tbl_nome']), $tabella, $_REQUEST['tbl_id']);
    }
    else {
      // Gestisco la questione rapporti - implica la generazione di tabelle temporanee
      if($rpt_flag AND in_array($_REQUEST['tbl_nome'], $rpt['tabelle'])) {
           include ('./rapporti.php');
      }  // Fine if esame anno_richiesta_se_rapporti
  echo genera_tabella($conn_obj, $_REQUEST['tbl_nome'], $tabella, $_REQUEST['tbl_id'], $_REQUEST['pagina'], $_REQUEST['filtro'], $_REQUEST['ordine']);
  }
  break;
  }  // fine case veditabella

  case 'edit' : {
     echo update_campo_tabella($conn_obj, $tabella, $_REQUEST['id_input'], clean_dato($_REQUEST['valore']));
  break;
  }  // fine case edit

  case 'calcola' : {
     echo calcola_campo_calcolato_tabella($conn_obj, $tabella, $_REQUEST['id_input']);
  break;
  }  // fine case edit

  case 'aggiungirecord' : {
      echo aggiungi_record_tabella($conn_obj, $_REQUEST['tbl_nome'], $tabella, $_REQUEST['tbl_id']);
  break;
  }  // fine case aggiungirecord

  case 'eliminarerecord' : {
     // ritorna il record eliminato
     echo elimina_record_tabella($conn_obj, $tabella, trim($_REQUEST['tbl_nome']), $_REQUEST['tbl_id']);
  break;
  } // fine case eliminare record

  case 'associa' : {    // Viene ricevuto il comando di aggiunta dell' articolo dal carrello degli ordini
    // riceve altri parametri
     echo associa_record($conn_obj, $_REQUEST['tbl_nome'], $_REQUEST['id_record'], $_REQUEST['valore'], TRUE);
  break;
  }

  case 'dissocia' : {    // Viene ricevuto il comando di rimozione dell articolo dal carrello degli ordini
    // riceve altri parametri
     echo associa_record($conn_obj, $_REQUEST['tbl_nome'], $_REQUEST['id_record'], $_REQUEST['valore'], FALSE);
  break;
  }

  case 'nuovocarrello' : {    // Viene ricevuto il comando di salvataggio della richiesta del form di intervento - emette il numero di serie se tutto OK
     // Sono quando chiedo un nuovo carrello ha senso impostare $_SESSION['DB_SEME']
      if(isset($_SESSION['DB_SEME'])) { // Quando chiedo un nuovo carrello devo resettare le sessioni esistenti non salvate
            $result = @$conn_obj->query_count('SELECT reset_carrello('.$_SESSION['DB_SEME'].')');   // Non permetto carrelli duplicati con lo stesso id_sessione - la funzione ritorna ERR oppure OK
      }
      if(!isset($_SESSION['DB_SEME']) OR (!$conn_obj->errore AND 'OK' == $result)) {
            $_SESSION['DB_SEME'] = (!isset($_SESSION['DB_SEME']) ? abs(intval(10*microtime(TRUE))) : abs($_SESSION['DB_SEME']));   // Valore intero 11 cifre per salvare unicita della sessione nel database
            $result = @$conn_obj->query_count('SELECT crea_nuovo_carrello(\''.$sso_appusername.'\', '.$_SESSION['DB_SEME'].','.TIMEOUT_COMPILAZIONE_CARRELLO.')');   // La funzione ritora ERR oppure OK
            if('OK' == $result) {
                echo 'OK';
            }
            else
                echo 'ERR';
      }
  break;
  }

  case 'confermacar_a' : {    // Riceve i valori id_record e valore che identificano il carrello con gli articoli da confermare e 1 per conferma - 0 diversamente
    $result = @$conn_obj->sql_command('UPDATE carrelloart SET confermato = '.$_REQUEST['valore'].' WHERE id_carrello = '.$_REQUEST['id_record'].' AND NOT confermato AND NOT ritirato');
       if(-1 <> $result) { // La funzione sql_command ritorna -1 oppure il numero dei record interessati
            echo 0;  // OK
       }
       else
            echo 'ERR';   // Errore
  break;
  } // fine case modcarrello

  case 'modcarrello' : {    // Riceve i valori id_record e valore che identificano il prodotto da modificare nel nuovo carrello e la sua quantita $_SESSION['DB_SEME']
    $result = @$conn_obj->query_count('SELECT aggiorna_articoli_in_carrello('.$_REQUEST['id_record'].' ,'.$_REQUEST['valore'].' ,'.$_SESSION['DB_SEME'].')');   // La funzione ritorna ERR oppure OK
       if(0 == $result) {
            echo 0;
       }
       else
            echo 'ERR';
  break;
  } // fine case modcarrello

  case 'modcinattesa' : {    // Riceve i valori id_art id_car che identificano il carrello la cui quantita di articolo e da modificare
    $result = @$conn_obj->query_count('SELECT mod_articoli_carrello('.$_REQUEST['id_art'].' ,'.$_REQUEST['id_car'].' ,'.$_REQUEST['valore'].' ,\'MOD\')');   // La funzione ritorna ERR oppure OK
       if(1 == $result) {
            echo 0;
       }
       else
            echo $conn_obj->errore;  // Contiene la descrizione dell errore
  break;
  } // fine case modcinattesa

  case 'caricaquantita' : {    // Riceve i valori id_record e valore che identificano la quantita dell articolo da caricare nella tabella articoli->quantita.
 // Il valore puo' essere caricato solo quando le quantita' ordinata e arrivata inseriti nell'ordine corrispondono.
    $result = @$conn_obj->query_count('SELECT carica_quantita_articoli('.$_REQUEST['id_record'].' ,'.$_REQUEST['valore'].')');   // La funzione ritorna 0==ERR oppure 1==OK
       if(1 == $result) {
            echo 0;
       }
       else
            echo 1;
  break;
  } // fine case caricaquantita

  case 'annullacarrello' : {    // Riceve i valori id_record e valore che identificano il prodotto da modificare nel nuovo carrello e la sua quantita $_SESSION['DB_SEME']
    $result = @$conn_obj->query_count('SELECT annulla_formazione_carrello('.$_SESSION['DB_SEME'].')');   // La funzione ritorna 0 OK oppure 1 errore
       if(1 == $result) {
            unset($_SESSION['DB_SEME']);
            echo 1;
       }
       else
            echo 0;
  break;
  } // fine case annullacarrello

  case 'verificacarrello' : {    // Serve verificare che ci siano articoli nel carrello altrimenti non ha senso inviarlo
    $result = @$conn_obj->query_count('SELECT COUNT(*) FROM carrelloart WHERE id_sessione = '.$_SESSION['DB_SEME']);   // La funzione ritorna 0 == OK oppure 1 == errore
       if(0 < $result)
            echo 1;  /* Tutto OK */
       else
            echo 0;  /* Errore */
  break;
  } // fine case confermacarrello

  case 'confermacarrello' : {    // Riceve i valori id_record e valore che identificano il prodotto da modificare nel nuovo carrello e la sua quantita $_SESSION['DB_SEME']
    $result = @$conn_obj->query_count('SELECT conferma_formazione_carrello('.$_SESSION['DB_SEME'].')');   // La funzione ritorna 0 == OK oppure 1 == errore
       if(1 == $result) {
            unset($_SESSION['DB_SEME']);
            echo 1;  /* Tutto OK */
       }
       else
            echo 0;  /* Errore */
  break;
  } // fine case confermacarrello

  case 'aggiornadataslot' : {    // Aggiorna lo slot di ritiro del carrello
     if(isset($_REQUEST['valore']) AND isset($_REQUEST['id_record'])) {
        $result = @$conn_obj->sql_command('UPDATE carrelli SET dataritiro = FROM_UNIXTIME('.$_REQUEST['valore'].')  WHERE id = '.$_REQUEST['id_record'].' LIMIT 1');   // Aggiorna lo slot data-ora di ritiro del carrello
        if(-1 <> $result)
             echo 1;  /* Tutto OK */
         else
             echo 0;  /* Errore */
     }
     else
         echo 0;  /* Errore */
  break;
  } // fine case confermacarrello

  case 'apricalendario' : {    // Riceve i valori id_record e valore che identificano il prodotto da modificare nel nuovo carrello e la sua quantita $_SESSION['DB_SEME']
      if(isset($_REQUEST['tbl_id_div']) AND is_numeric($_REQUEST['tbl_id_div'])) {
          require_once('./calendario.php');
          $cal_new = new Calendario($conn_obj, $_REQUEST['tbl_id_div']);
          echo $cal_new->genera_calendario();
      }
      else
          echo 'ERRORE';
  break;
  } // fine case apricalendario

  case 'emessaggio' : {  // Apre e/o salva il messaggio email indirizzato all'utente compilatore del carrello
      if(isset($_REQUEST['tbl_id_div']) AND is_numeric($_REQUEST['tbl_id_div'])) {  // In $_REQUEST['tbl_id_div'] trovo l'id del carrello
          require_once('./messaggio.php');
          $msg = new Messaggio($conn_obj, $_REQUEST['tbl_id_div']);
      }
      else {
          echo 'ERRORE';
          break;
      }
      if('APRI' == $_REQUEST['tbl_nome']) {
           echo $msg->genera_form();
      }
      if('INVIA' == $_REQUEST['tbl_nome']) {   // Procedo all'invio e al salvataggio dei dati del messaggio
           echo $msg->invia_messaggio($_REQUEST['dati']);
      }
  break;
  }  // fine case apricalendario

  case 'gengrafico' : {    // Riceve i valori id_record e temporali identificano il prodotto del guale visualizzare la richiesta nel tempo
      if(isset($_REQUEST['tbl_id_div']) AND isset($_REQUEST['tbl_nome']) AND in_array($_REQUEST['tbl_nome'], array('PROD', 'GROUP'))) {
          require_once('./grafici.php');
          $graph_new = new Grafico($conn_obj);
          echo $graph_new->chart_display($_REQUEST['tbl_id_div'], $rpt['rapporti'][0], $rpt['rapporti'][1], 30, $_REQUEST['tbl_nome']);
      }
      else
          echo 'ERRORE IN GENERAZIONE GRAFICO !';
  break;
  } // fine case apricalendario

  default : {
        echo '<SPAN class="classe_label">COMANDO NON GESTITO: '.$_REQUEST['tbl_azione'].'</SPAN>';
  }
}  // fine switch case

?>