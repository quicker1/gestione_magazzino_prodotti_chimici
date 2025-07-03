<?PHP  // michele.furlan@unipd.it  modifica del 09 ottobre 2023
// Lo script controlla la corretteza della configurazione dell'ambiente e delle impostazioni dell'utente nel file config.php
if(!isset($_REQUEST['tbl_azione']) OR $_REQUEST['tbl_azione'] != 'verificaapp')
    die('<BR /><H2>Accesso consentito solo su inizializzazione corretta della app</H2>');

include ('./config.php');

$chiavi['tabella'] = array('nometabella', 'figlia', 'chiave_padre', 'drag_drop', 'nascondi_id', 'intestazione', 'ordine', 'pulsanti', 'where', 'campi');

$chiavi['campi'] = array('text', 'textarea', 'select', 'file', 'date', 'booleano', 'calcolato');   // Tipi di campo ammissibili
$chiavi['pulsanti'] = array('verticale', 'chiave_figlia', 'hidevert', 'add', 'delete', 'filtro', 'paginazione');
$chiavi['default_campi'] = array('tipo', 'etichetta', 'editable', 'size_filtro', 'vert_larghezza', 'attributi');   // Tipi attributo generali sui campi
$chiavi['calcolato'] = array('tipo', 'etichetta', 'size_filtro', 'vert_larghezza', 'formula', 'ricalcolo');

$chiavi['text'] = array('default_value', 'size', 'maxlength', 'pattern', 'error_pattern');  // Attributi obbligatori nei campi del tipi indicato
$chiavi['textarea'] = array('default_value', 'rows', 'cols', 'maxlength');
$chiavi['date'] = array('default_value', 'min', 'max', 'pattern', 'error_pattern');
$chiavi['file'] = array('cartella_upload', 'larghezza_max_miniatura', 'non_duplicabile');
$chiavi['booleano'] = array('default_value');

function return_bytes_from_human($val) {   // Ritorna un valore in byte a partire da un valore umanamente leggibile tipo ls -h
    $val = trim($val);
    preg_match('/([0-9]+)[\s]*([a-zA-Z]+)/', $val, $matches);
    $value = (isset($matches[1])) ? intval($matches[1]) : 0;
    $metric = (isset($matches[2])) ? strtolower($matches[2]) : 'b';
    switch ($metric) {
        case 'tb':
        case 't':
            $value *= 1024;
        case 'gb':
        case 'g':
            $value *= 1024;
        case 'mb':
        case 'm':
            $value *= 1024;
        case 'kb':
        case 'k':
            $value *= 1024;
    }
    return $value;
}  // Fine function return_bytes from human


if(!isset($tabella) OR NULL == MASSIMA_DIMENSIONE_FILE OR NULL == QUOTA_MASSIMA)
    die("Manca almeno una o piu' delle seguenti chiavi in config.php: <B>tabella</B>, <B>massima_dimensione</B>, <B>quota_massima</B>");

if(null !== ini_get('upload_max_filesize') && return_bytes_from_human(ini_get('upload_max_filesize')) < MASSIMA_DIMENSIONE_FILE)
    die('Il limite upload_max_filesize= '.ini_get('upload_max_filesize').' in php.ini &egrave; inferiore alla dimensione massima consentita del file: '.intval(MASSIMA_DIMENSIONE_FILE/MEGABYTES)."M</H3><BR />\n");

if(ini_get('post_max_size') && return_bytes_from_human(ini_get('post_max_size')) < MASSIMA_DIMENSIONE_FILE)
    die('Il limite post_max_size= '.ini_get('upload_max_filesize').' in php.ini &egrave; inferiore alla dimensione massima consentita del file: '.intval(MASSIMA_DIMENSIONE_FILE/MEGABYTES)."M</H3><BR />\n");

function array_multichiave_esistenza($a, $b) {   // in $a chiavi da verificare - tutte devono essere presenti in $b per avere true di ritorno
  if(is_array($a) AND count($a) > 1) {           // Il confronto lo devo fare solo con una chiave
       $test = array_shift($a);
       return array_key_exists($test, $b) ? array_multichiave_esistenza($a, $b) : $test;  // test ricorsivo
  }
  else  // un elemento in $a
       return array_key_exists($a[0], $b) ? '' : $a[0];
}

function array_multivalore_esistenza($a, $b) { // in $a valori da verificare - tutte devono essere presenti in $b per avere true di ritorno
  if(is_array($a) AND count($a) > 1) {         // Il confronto lo devo fare solo con una chiave
       $test = array_shift($a);
       return in_array($test, $b) ? array_multivalore_esistenza($a, $b) : $test;   // test ricorsivo
  }
  else  // un elemento in $a
       return in_array($a[0], $b) ? '' : $a[0];
}

foreach($tabella as $chiavet => $valoret) {
$str = $str_attr = '';
   $str = array_multichiave_esistenza($chiavi['tabella'], $tabella[$chiavet]);  // test primo livello su tabella
   if($str)
      die("Manca la seguente chiave obbligatoria in tabella: <B>$chiavet</B>: $str");

// Estraggo le chiavi dalla variabile campi in tabella - serve per il test select
$str = 'id'; // l' ID c'e' sempre
  foreach($tabella[$chiavet]['campi'] as $campo => $valore) { //$key => $value
   $tipo_campo[] = $valore['tipo'];  // Per la successiva verifica del tipo di campo

     if($valore['tipo'] == 'calcolato')
        $str .= ','.str_replace('%valore%', '1', $valore['formula']).' AS '.$campo;
     else
        $str .= ','.$campo;

     if($valore['tipo'] != 'select' AND $valore['tipo'] != 'calcolato') {
         $str_attr = array_multichiave_esistenza($chiavi[$valore['tipo']], $valore['attributi']);
          if($str_attr)
             die("Manca il seguente attributo nel campo <B>$campo</B> tipo ".$valore['tipo']." in tabella: <B>$chiavet</B>: $str_attr");
     }
     if($valore['tipo'] == 'file') {
        if(!trim($valore['attributi']['cartella_upload']))   // Errore per attributo vuoto senza valore - serve un cartella per l'upload dei file
           die("Manca il seguente attributo nel campo <B>$campo</B> di tipo ".$valore['tipo']." in tabella: <B>$chiavet</B>: cartella_upload");
        if(!file_exists(dirname($_SERVER['SCRIPT_FILENAME']).(DIRECTORY_SEPARATOR).$valore['attributi']['cartella_upload']) OR !is_writable(dirname($_SERVER['SCRIPT_FILENAME']).(DIRECTORY_SEPARATOR).$valore['attributi']['cartella_upload']))
           die('La directory di upload <B>'.$valore['attributi']['cartella_upload']."</B> nel campo <B>$campo</B> in tabella: <B>$chiavet</B> non esiste o non e' scrivibile");
     }
     // Test presenza nei campi delle chiavi setting generali comuni a tutti i campi (escluso il calcolato)
    if($valore['tipo'] != 'calcolato') {
        $str_attr = array_multichiave_esistenza($chiavi['default_campi'], $tabella[$chiavet]['campi'][$campo]);
         if($str_attr)
             die("Nella tabella <B>$chiavet</B>  mancano le seguenti chiavi di campo: <B>".$str_attr.'</B>');
    }
    else {
        $str_attr = array_multichiave_esistenza($chiavi['calcolato'], $tabella[$chiavet]['campi'][$campo]);
         if($str_attr)
             die("Nella tabella <B>$chiavet</B>  mancano le seguenti chiavi di campo: <B>".$str_attr.'</B>');
    } // fine else campo calcolato
  } // fine foreach

  $str = 'SELECT '.$str.' FROM '.$tabella[$chiavet]['nometabella'];
     if(!@$conn_obj->sql_query($str))
         die('Query fallita: '.$str);

// Test presenza tipo di campi
  $str = array_multivalore_esistenza($tipo_campo, $chiavi['campi']);
   if($str)
       die("Nella tabella <B>$chiavet</B> i seguenti tipi di campo non sono previsti: ".$str);

// Test della corretta presenza delle chiavi sui pulsanti
   $str = array_multichiave_esistenza($chiavi['pulsanti'], $tabella[$chiavet]['pulsanti']);
     if($str)
        die('Nella tabella <B>'.$chiavet.'</B> mancano le seguenti chiavi nei pulsanti: '.$str);

// Test della correttezza catena tabella padre - figlia
   foreach($tabella[$chiavet]['figlia'] as $str => $str_attr)
     if($str AND !isset($tabella[$str]))
         die("Non esiste in tabella <B>$chiavet</B> la tabella figlia: ".$str);

}  // Fine foreach ciclo tabella

?>