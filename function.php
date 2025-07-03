<?PHP // michele.furlan@unipd.it  31 gennaio 2024

function float_to_string($float, $decimals = 3) {   // Necessario solo per lo UNIX_TIMESTAMP con calcolo del tempo in millesimi
     return number_format((float)$float, $decimals, '.', '');
}

function remove_accents($string) {
    if (!preg_match('/[\x80-\xff]/', $string) )
        return $string;

    $chars = array(
    // Decompositions for Latin-1 Supplement
    chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
    chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
    chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
    chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
    chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
    chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
    chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
    chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
    chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
    chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
    chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
    chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
    chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
    chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
    chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
    chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
    chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
    chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
    chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
    chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
    chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
    chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
    chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
    chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
    chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
    chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
    chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
    chr(195).chr(191) => 'y',
    // Decompositions for Latin Extended-A
    chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
    chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
    chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
    chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
    chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
    chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
    chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
    chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
    chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
    chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
    chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
    chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
    chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
    chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
    chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
    chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
    chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
    chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
    chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
    chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
    chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
    chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
    chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
    chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
    chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
    chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
    chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
    chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
    chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
    chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
    chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
    chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
    chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
    chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
    chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
    chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
    chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
    chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
    chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
    chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
    chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
    chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
    chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
    chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
    chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
    chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
    chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
    chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
    chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
    chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
    chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
    chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
    chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
    chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
    chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
    chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
    chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
    chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
    chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
    chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
    chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
    chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
    chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
    chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
    );
    $string = strtr($string, $chars);

return $string;
}  // fine function remove_accents


function clean_dato($str) {
   $str = remove_accents(trim($str));  // Rimozione caratteri non ascii (accentati e oltre)
   $str = preg_replace(STRINGA_REGEX_VALORI, ' ', $str);
return addslashes($str);   // addslaslashes per inserimento nel database
}  // fine function clean_dato()


function test_is_image(&$tabella, &$tabella_richiesta, $campo, $nomefile, $attr_img=FALSE) {   // se FALSE massima dimensione - se TRUE solo miniatura

$nomefile = $tabella[$tabella_richiesta]['campi'][$campo]['attributi']['cartella_upload'].(DIRECTORY_SEPARATOR).$nomefile;
  if(file_exists($nomefile) && exif_imagetype($nomefile)) {
     list($width, $height, $type, $attr) = getimagesize($nomefile);
       if($attr_img) {  // Fisso un limite massimo di dimensione per l'anteprima - larghezza 250px
          if($width > 300)
             $attr = "width='350' height='".intval((350/$width) * $height)."'";
          return '<BR /><BR /><IMG class="center_elemento_imma" src="'.addslashes($nomefile).'?'.filemtime($nomefile)."\" $attr alt=\"$attr\" />";  // filemtime per impedire caching
       }
       else {
          $larghezza_max_miniatura = $tabella[$tabella_richiesta]['campi'][$campo]['attributi']['larghezza_max_miniatura'];
          $attr = "width='$larghezza_max_miniatura' height='".intval(($larghezza_max_miniatura/$width)*$height).'\'';
          return '<IMG class="center_elemento_imma" src="'.addslashes($nomefile).'?'.filemtime($nomefile)."\" $attr alt=\"$attr\" />";   // Genero la miniatura per l'anteprima in tabella
       }
  }
  else
     return '';  // Non e' un immagine
} // fine test_is_image()


function check_figlia_record(&$conn, &$tabella_padre, &$id) {     // test se c'e' in tabella le figlie e se e' popolata - conteggio i record nelle figlie
// in $id record di tabella padre da esaminare - ritorna il numero di righe - se singola = TRUE conteggio solo i record presenti nell'unica tabella figlia indicata in &$tabella_padre
$tabelle = $GLOBALS['tabella'];
$esito = $record = 0;

  if(trim($tabelle[$tabella_padre]['chiave_padre']))  {  // Non devo conteggiare i record delle tabelle non figlie - chiave padre vuota significa che non pongo limiti al filtro
    $esito = $conn->query_count('SELECT COUNT(*) AS totale FROM '.$tabelle[$tabella_padre]['nometabella'].' WHERE '.$tabelle[$tabella_padre]['chiave_padre'].'='.$id.(2 < strlen($tabelle[$tabella_padre]['where']) ? ' AND '.$tabelle[$tabella_padre]['where'] : ''));
      if(-1 == $esito)
          return 'ERR';
      else
          return $esito;
  }  // fine if chiave_padre

return $esito;
} // check_figlia_record


function togli_alias_tbl($tbl = '') {
   return check_alias_table($tbl) ? check_alias_table($tbl) : $tbl;
}  // Fine funzione togli_alias_tabella

// tabella_tabellafiglia_figliaidpadre_nomecampo_id_azione
function info_vedi_tabella($str) {  // splitta in un array 4 valori
// tabella_nomecampo_id_azione
$res = array();
$ar = array();
$ar = explode('-', $str);
  $res['tblid'] = togli_alias_tbl($ar[0]);
  $res['tabella'] = $GLOBALS['tabella'][$res['tblid']]['nometabella'];
  $res['valore'] = $ar[1];   // Nome del campo
  $res['id'] = $ar[2];
  $res['azione'] = $ar[3];
  $res['lunghezza_max_campo'] = 0;  // False o non impostato

  if(isset($GLOBALS['tabella'][$res['tblid']]['campi'][$ar[1]]['attributi']['maxlength'])) {
        $str = $GLOBALS['tabella'][$res['tblid']]['campi'][$ar[1]]['attributi']['maxlength'];
        $res['lunghezza_max_campo'] = (!empty($str) && is_numeric($str)) ? (int)$str : 0;
  }

return $res;
}  // fine function info_vedi_tabella


function check_alias_table($tbl) {  // la funzione testa se nella tabella alla fine e presente la keyword ALIAS se si resitituisce il nome tabella - se NO una stringa vuota
   if(substr($tbl, -5, 5) === 'ALIAS')   // confronto stretto case sensitive
      return substr($tbl, 0, -5);
   else
      return '';
}  // fine function check_alias_table


function calcola_campo_calcolato_tabella(&$conn, &$tabella, &$id_td_campo) { // in id_td_campo  nometabella-nomecampo-id_riga-azione
$dati = info_vedi_tabella($id_td_campo);
$str = 'SELECT '.$tabella[$dati['tblid']]['campi'][$dati['valore']]['formula'].' AS '.$dati['valore'].' FROM '.$dati['tabella'].' WHERE id='.$dati['id'].' LIMIT 1';

  $result = &$conn->sql_query($str);
    if($result->num_rows == 1) {  // Solo un record per volta e' accettato
        $row = $result->fetch_assoc();
        $str = $row[$dati['valore']];
    }
  mysqli_free_result($result);

return $str;
} // fine function calcola_campo_calcolato_tabella


function update_campo_tabella(&$conn, &$tabella, $id_input, $valore) {
$dati = info_vedi_tabella($id_input); // devo separare i campi
$last_id = 0;

// Verifica server side sui privilegi di editing in tabella nel campo
  if(!$tabella[$dati['tblid']]['campi'][$dati['valore']]['editable'] AND $tabella[$dati['tblid']]['pulsanti']['add']) {  // Prima di negare l'accesso verifico la condizione ultima, cioe' se add == true e ultimo record inserito
      $last_id = @$conn->query_count('SELECT MAX(id) AS ultimo FROM '.$dati['tabella'].' LIMIT 1');  // Valore sempre positivo
      if(-1 == $last_id)
          return 'Errore query: '.$conn->errore;
      else {
          if(!($last_id == $dati['id']))   // Non e' l' ultimo record
              return 'Non hai il permesso di update in tabella: '.$dati['tabella'];
      }
  } // Fine if verifica editabilita campo

   // Devo verificare che alcuni campi numerici delle tabelle temporanee sono modificabili
   if(!$tabella[$dati['tblid']]['campi'][$dati['valore']]['editable'] AND $last_id == 0)   // Verifica editabilita se non ultimo record aggiunto
       return 'Non hai il permesso di update in tabella: '.$dati['tabella'];

   // Riduco la lughezza massima al valore impostato in maxlength del campo se impostato - validazione della lunghezza consentita nel campo
   if($dati['lunghezza_max_campo'] > 0)
       $valore = substr($valore, 0, $dati['lunghezza_max_campo']);
   $str = 'UPDATE '.$dati['tabella'].' SET '.$dati['valore']."='".htmlspecialchars($valore, ENT_QUOTES, 'ISO-8859-1')."' WHERE id=".$dati['id'].' LIMIT 1';   // Bene limitare ad 1 l'UPDATE

   if(-1 == $conn->sql_command($str))
      return 'Errore query: '.$conn->errore;
   else
      return '';  // Nessun errore
}  // fine function update_campo_tabella


function genera_lista_campi_calcolati(&$tabella_richiesta, &$tabella, &$val) {  // Se esistono campi calcolati li compongo per la select in genera_tabella
$str = '';

 foreach($tabella[$tabella_richiesta]['campi'] as $campo => $valore)  // $key => $value
   if($valore['tipo'] == 'calcolato')
      $str .= ','.str_replace('%valore%', $val, $valore['formula']).' AS '.$campo;

return '*'.$str;
} // fine function genera_lista_campi_calcolati


function genera_tabella_alias(&$conn, $tabella_richiesta, &$tabella, $id_tabella_padre) {   // genera la tabella richiesta in forma verticale senza aggiunta di pulsanti add record o elimina
$str = '<FORM id="my_form_alias_table" onSubmit="return false;"><TABLE class="aliastable">'."\n";

  $result = &$conn->sql_query('SELECT '.genera_lista_campi_calcolati($tabella_richiesta, $tabella, $id_tabella_padre).' FROM '.$tabella[$tabella_richiesta]['nometabella'].' WHERE id = '.$id_tabella_padre.' LIMIT 1'); // query base da eseguire - LIMIT 1 massimo una riga puo' esistere
  if($id_tabella_padre > 0 AND (!$conn->errore && $result->num_rows > 0)) {   // l' ID tabella padre se e' pari a 0 significa che c'e' un errore nella procedura SQL salva_richiesta()
    $row = $result->fetch_assoc();
        foreach($row as $key => $value) {
           if($key == 'id') { // Se il campo non e' specificato in config.php non lo considero editabile
                  $str .= '<THEAD><TR><TD>'.ucfirst($key).'</TD><TD>'.$value.'</TD></TR></THEAD>'."\n";
                  break;
           }
        }  // fine ciclo foreach

     $str .= '<TBODY>'.genera_campo_tabella($tabella_richiesta, $tabella, $row, TRUE).'</TBODY>';
     $str .= '<TFOOT><TR><TH colspan="2" style="text-align:center;">Tabella: '.strtoupper($tabella[$tabella_richiesta]['nometabella']).'</TH></TR></TFOOT>'."\n";

 mysqli_free_result($result);
 }  // fine if num_rows
 else
    $str .= '<TR><TD>Errore query in genera_tabella_alias: '.($conn->errore).'</TD></TR>';

return $str."</TABLE></FORM>\n";
} // fine funzione genera_tabella_alias


function genera_campo_tabella($tabella_richiesta, &$tabella, &$riga, $alias_table = FALSE, $ultimo_record_editabile = FALSE) {   // Per singolo campo di ogni riga genera il controllo di input
$str = $str_query = $str_alias_tr_start = $tmp_eventi = $tmp_id = '';
$str_alias_tr_end = $alias_table ? '</TR>' : '';   // Necessario perche' i campi nella tabella alias in verticale
$str_alias_flag = $alias_table ? 'ALIAS' : '';

 foreach($tabella[$tabella_richiesta]['campi'] as $campo => $valore)  {  // produco i campi editabili
    if(!$alias_table AND $valore['vert_larghezza'])  // Se "vert_larghezza" e' impostato > 0 e' un campo che va mostrato solo nella tabella verticale
        continue;  // ciclo successivo
    if($alias_table)
        $str_alias_tr_start = "\n<TR><TD class=\"nowrap_td_alias\">".$valore['etichetta'].'</TD>';

    $tmp_id = $tabella_richiesta.$str_alias_flag.'-'.$campo.'-'.$riga['id'].'-edit';
    if($tabella[$tabella_richiesta]['drag_drop'])  // Abilito il drag_drop sui campi in tabella
        $tmp_eventi = 'onMouseDown="td_drag_drop(true,this.id);" onMouseUp="td_drag_drop(false,this.id);" onMouseLeave="td_drag_drop(false,this.id);"';

    if((isset($valore['editable']) && $valore['editable']) OR $valore['tipo']=='calcolato' OR $ultimo_record_editabile OR ($valore['tipo']=='file' AND strlen($riga[$campo]) > 1)) {

     switch($valore['tipo']) {
       case 'calcolato' : {   // I campi calcolati non sono editabili ma vanno solo aggiornati, salvo la presenza del flag ricalcola = false
          $str .= $str_alias_tr_start.'<TD class="td_campo_calcolato" id="'.$tabella_richiesta.$str_alias_flag.'-'.$campo.'-'.$riga['id'].'-campocalcolato" '
               .($valore['ricalcolo'] ? 'data-ricalcolo="SI"' : 'data-ricalcolo="NO"')." $tmp_eventi>".$riga[$campo].'</TD>'.$str_alias_tr_end;  // data-* e' un attributo custom permesso in HTML se inizia con data- per convenzione
       break;
       }  // fine case file
       case 'file' : {
            $str_query = (strlen($riga[$campo]) > 1) ? test_is_image($tabella, $tabella_richiesta, $campo, $riga[$campo], FALSE) : '';  // Se non e' un immagine ritorna un valore vuoto
               if(!$str_query)
                  $str_query = '<INPUT class="pulsante_file center_elemento_imma" type="button" value="'.(strlen($riga[$campo]) > 1 ? $riga[$campo] : '&#x1F4CE;&nbsp;UPLOAD FILE').'" />';

            $str .= $str_alias_tr_start.'<TD id="'.$tabella_richiesta.$str_alias_flag.'-'.$campo.'-'.$riga['id'].'-listafile" onClick="upload_file(this.id'.($ultimo_record_editabile ? ",'ultimorecord'" : '').');">'.$str_query.'</TD>'.$str_alias_tr_end;
       break;
       }  // fine case file
       case 'select' : {
           $str .= $str_alias_tr_start.'<TD id="select-'.$tmp_id.'"><SELECT id="'.$tmp_id.'" onChange="update_campo(this.id);">'."\n";
           $str .= '<OPTION value=""'.((!trim(strlen($riga[$campo])) OR !in_array($riga[$campo], array_keys($valore['attributi']))) ? ' selected="selected"' : '').'>'.$valore['attributi']['default_vuoto'].'</OPTION>'."\n";
                foreach($valore['attributi'] as $valore_select => $testo_select)
                       if($valore_select != 'default_vuoto')
                          $str .= '<OPTION value="'.$valore_select.'"'.($valore_select == $riga[$campo] ? ' selected="selected"' : '').">$testo_select</OPTION>\n";
                // Fine foreach SELECT
            $str .= '</SELECT></TD>'.$str_alias_tr_end;
       break;
       } // Fine case select
       case 'textarea' : {
            $str .= $str_alias_tr_start.'<TD id="textarea-'.$tmp_id.'" '.$tmp_eventi.'><TEXTAREA class="blue-input" rows="'.$valore['attributi']['rows'].'" cols="'.($valore['vert_larghezza'] ? (intval($valore['vert_larghezza']) + $valore['attributi']['cols']) : ceil($valore['attributi']['cols'] * ($alias_table ? 1.4:1)))
                 .'" onChange="update_campo(this.id);" id="'.$tmp_id.'" maxlength="'.$valore['attributi']['maxlength'].'">'.$riga[$campo].'</TEXTAREA></TD>'.$str_alias_tr_end;
       break;
       }  // fine case 'textarea'
       case 'date' : {
               $str_query = $valore['attributi']['pattern'] ? 'pattern="'.$valore['attributi']['pattern'].'" title="'.addslashes($valore['attributi']['error_pattern']).'"' : '';
               $str .= $str_alias_tr_start.'<TD id="date-'.$tmp_id.'" '.$tmp_eventi.'><INPUT '.$str_query.' class="blue-input" onChange="update_campo(this.id);" type="date" id="'.$tmp_id.'" value="'.$riga[$campo].'" '
                    .'min="'.$valore['attributi']['min'].'" max="'.$valore['attributi']['max'].'" /></TD>'.$str_alias_tr_end;
       break;
       }  // fine case 'date'
       case 'booleano' : {  // Nel booleano il valore puo' essere 0==false oppure 1==true
             $str .= $str_alias_tr_start.'<TD style="text-align:center;"><INPUT class="blue-input" onChange="this.value=(this.checked ? 1 : 0);update_campo(this.id);" type="checkbox" id="'
                  .$tabella_richiesta.$str_alias_flag.'-'.$campo.'-'.$riga['id'].'-edit" value="'.$riga[$campo].'" '.($riga[$campo] ? 'checked' : '').' /></TD>'.$str_alias_tr_end;
       break;
       }
       case 'text' : {  // Se l'utente ha inserito un espressione regolare la trovo nell attributo pattern
             $str_query = $valore['attributi']['pattern'] ? 'pattern="'.$valore['attributi']['pattern'].'" title="'.addslashes($valore['attributi']['error_pattern']).'"' : '';
             $str .= $str_alias_tr_start.'<TD id="text-'.$tmp_id.'" '.$tmp_eventi.'><INPUT '.$str_query.' class="blue-input" size="'.($valore['vert_larghezza'] ? (intval($valore['vert_larghezza']) + $valore['attributi']['size']) : ceil($valore['attributi']['size'] * ($alias_table ? 1.4:1)))
                  .'" onChange="update_campo(this.id);" type="text" id="'.$tmp_id.'" value="'.$riga[$campo].'" maxlength="'.$valore['attributi']['maxlength'].'" /></TD>'.$str_alias_tr_end;
       break;
       }  // fine case 'text'
       default:   // Ipotizzo che il campo non sia editabile per default
            $str .= $str_alias_tr_start.'<TD id="noedit-'.$tmp_id.'" '.$tmp_eventi.'>'.$riga[$campo].'</TD>'.$str_alias_tr_end;

     }  // fine switch
    } // Fine if check editabilita del campo
    else {
        if($valore['tipo'] == 'select')
            $str_query = (isset($valore['attributi'][$riga[$campo]]) ? $valore['attributi'][$riga[$campo]] : '');
        elseif($valore['tipo'] == 'booleano')
            $str_query = $riga[$campo] ? 'SI' : 'NO';
        else
            $str_query = $riga[$campo];
        $str .= $str_alias_tr_start.'<TD id="noedit-'.$tmp_id.'" '.$tmp_eventi.'>'.$str_query.'</TD>'.$str_alias_tr_end;    // campo non editabile
    }  // Fine else
 }  // Fine foreach

return $str;
} // fine function genera_campo_tabella


function genera_tabella(&$conn, $tabella_richiesta, &$tabella, $id_tabella_padre, $pagina='prima', $filtro=NULL, $ordine=NULL) {   // sono tre le tabelle possibili
// la funzione si limita a generare la tabella richiesta
// tabella_nomecampo_id_azione
$str = genera_pulsanti_carrello($tabella_richiesta).'<FORM onSubmit="return false;"><TABLE align="left" style="border-color:'.$GLOBALS['tabella_css_color'][(array_search($tabella_richiesta, array_keys($tabella)) % 3)].';">';
$str_query = 'SELECT '.genera_lista_campi_calcolati($tabella_richiesta, $tabella, $id_tabella_padre).' FROM '.$tabella[$tabella_richiesta]['nometabella']; // query base da eseguire
$str_div_tag = '<DIV><INPUT class="xml_excel_button" type="button" id="'.$tabella_richiesta.'-inviatabellainexcel" '
              .($GLOBALS['app_admin'] ? 'value="&nbsp;xls Excel&nbsp;" onClick="download_excel_table(this.id);"' : 'value="&#x1F56E;" onClick="window.print();"').' /></DIV>';
$str_query_cond = $str_query_ord = $tmp = $result = '';
$nrecord = $npagine = $startpagina = $conta_record = $valore = $id_chiave = 0;
$ultimo_record = FALSE; $salto_colonna_x_verticale = 0;

  if(!empty($tabella[$tabella_richiesta]['intestazione']))
      $str .= '<CAPTION>'.$tabella[$tabella_richiesta]['intestazione']."</CAPTION>\n";

  if($pagina == 'ultimaedit') {  // Quando aggiungo il record devo andare nell'ultima pagina
      $pagina = 'ultima'; $ultimo_record = TRUE;
      $tabella[$tabella_richiesta]['ordine'] = array(); // resetto eventuali ordinamenti utente
  }  // fine if "ultimaedit"

// Nel caso eliminare l'ultima colonna a destra con i pulsanti ADD, DELETE, o indicazioni di tabella subordinata - genero un valore per il COLSPAN
    if($tabella[$tabella_richiesta]['pulsanti']['add'] OR $tabella[$tabella_richiesta]['pulsanti']['delete'] OR 0 < count($tabella[$tabella_richiesta]['figlia']))
       $colspan_ultima_colonna = 0;
    else
       $colspan_ultima_colonna = -1;  // per sottrarre dopo il valore

// Se in tabella_padre c'e' un chiave esterna devo filtrare la tabella
    if(!empty($tabella[$tabella_richiesta]['chiave_padre']) AND $id_tabella_padre != 0)
        $str_query_cond = ' WHERE '.$tabella[$tabella_richiesta]['chiave_padre'].'='.$id_tabella_padre;
// Altresi devo filtrare se e' presente una valore chiave figlia ??

    if(strlen(trim($tabella[$tabella_richiesta]['where'])) > 1)  // Esiste un filtro condizionale sui campi
        $str_query_cond .= ($str_query_cond ? ' AND ' : ' WHERE ').$tabella[$tabella_richiesta]['where'];

    if($tabella[$tabella_richiesta]['pulsanti']['filtro'] OR count($tabella[$tabella_richiesta]['ordine']) >0) {
        if(isset($filtro['id_filtro']) AND (strlen(trim($filtro['valore'])) >0 )) {  // Aggiunge la riga per filtrare
           $tmp = explode('-', $filtro['id_filtro']);
           $tmp = $tmp[1]; // Nome del campo richiesto nel filtro
              if($tabella[$tabella_richiesta]['campi'][$tmp]['tipo'] == 'calcolato')
                  $tmp = $tabella[$tabella_richiesta]['campi'][$tmp]['formula'];

           $tmp = $tmp." LIKE '%".$filtro['valore']."%'";    // Nome del campo
           $str_query_cond .= ($str_query_cond) ? (' AND '.$tmp) : (' WHERE '.$tmp);
        }

        $str_query_ord = ' ORDER BY ';
        if(isset($ordine['id_ordine']) AND (strlen(trim($ordine['valore'])) >= 3 )) {  // Aggiunge la riga per filtrare
           $tmp = explode('-', $ordine['id_ordine']);
           $str_query_ord .= $tmp[1].' '.$ordine['valore'];   // Nome del campo e valore ASC o DESC
        }
        elseif(count($tabella[$tabella_richiesta]['ordine']) >0) { // E' stato forzato un ordinamento preferenziale da parte dell'utente
             foreach($tabella[$tabella_richiesta]['ordine'] as $tmp => $result)
                      $str_query_ord .= $tmp.' '.$result.', ';
        }
        else
            $str_query_ord .= 'id ASC';  // Forzo l'ordine del campo ID A->Z per permettere l'editing dell'ultimo campo aggiunto con ADD record nel caso in cui l'editing fosse impedito

        if(strpos($str_query_ord, ', ', strlen($str_query_ord) -2))   // Tolgo l'eventuale carattere separatore ", "
            $str_query_ord = substr($str_query_ord, 0 , -2);
    } // Fine if pulsanti filtro e ordinamento

// conteggio dei record in tabella
     $tmp = 'SELECT COUNT(*) AS totale FROM '.$tabella[$tabella_richiesta]['nometabella'].$str_query_cond;
     $result = @$conn->query_count($tmp);
     if(-1 == $result)
         return 'Query fallita: '.$conn->errore;
     $nrecord = $result;

     $tmp = empty($tabella[$tabella_richiesta]['chiave_padre']) ? 'onMouseDown="tabella_drag_drop(true, this.id);" onMouseUp="tabella_drag_drop(false);" onMouseLeave="tabella_drag_drop(false);"'  : '';
     $str .= '<THEAD><TR id="'.$tabella_richiesta.'-numerorecord-'.$nrecord.'" '.$tmp.">\n";

     foreach($tabella[$tabella_richiesta]['figlia'] as $tmp) $str .= "<TH>$tmp</TH>";

     $str .= ($tabella[$tabella_richiesta]['pulsanti']['verticale'] ? '<TH>FULL</TH>' : '').($tabella[$tabella_richiesta]['nascondi_id'] ? '' : '<TH>ID</TH>');

     foreach($tabella[$tabella_richiesta]['campi'] as $campo => $valore)  //$key => $value
             if(!$valore['vert_larghezza'])
                 $str .= '<TH>'.$valore['etichetta'].'</TH>';
             else
                 $salto_colonna_x_verticale++;   // ogni colonna omessa nella tabella verticale va sottratta nel campo della paginazione

    if($tabella[$tabella_richiesta]['pulsanti']['add'])
       $str .= '<TH><INPUT class="pulsante" type="button" value="NEW" id="'.$tabella_richiesta.'-NONE-'.$id_tabella_padre.'-aggiungirecord" onClick="add_record(this.id);" /></TH></TR>'."\n";
    else
       $str .= ($colspan_ultima_colonna ? '' : '<TH>NO&nbsp;ADD</TH>')."</TR>\n";

    $str .= '</THEAD><TBODY>';

    if($tabella[$tabella_richiesta]['pulsanti']['filtro']) {   // Aggiunge la riga in intestazione per filtrare ed ordinare
        $valore = count($tabella[$tabella_richiesta]['figlia']) + ($tabella[$tabella_richiesta]['pulsanti']['verticale'] ? 1 : 0) + ($tabella[$tabella_richiesta]['nascondi_id'] ? 0 : 1);
        $str .='<TR style="background-color:#84cbf9;">'.($valore ? '<TD colspan="'.$valore.'">'.$str_div_tag.'</TD>' : '');  // Non c'e' spazio per emissione pulsante xls Excel
           foreach($tabella[$tabella_richiesta]['campi'] as $campo => $valore)  {  // produco i campi filtrabile
             if($valore['vert_larghezza'])  // Se "vert_larghezza" e' impostato > 0 e' un campo che va mostrato solo nella tabella verticale
                 continue;  // ciclo successivo - non aggiungo campo filtro per la tabella tabulare
           if($valore['size_filtro']) {    // Campo filtro e ordinamento solo se size_filtro > 0
             $tmp = $tabella_richiesta.'-'.$campo.'-'.$id_tabella_padre.'-ASC-ordinacampo';         // Pulsanti ordinamento
             $str .= '<TD class="nowrap_td"><INPUT class="'.((isset($ordine['id_ordine']) && $tmp == $ordine['id_ordine']) ? 'pulsante_ord_set' : 'pulsante_ord').'" type="button" id="'.$tmp.'" onClick="ordina_campo(this.id);"'.((isset($ordine['id_ordine']) && $tmp == $ordine['id_ordine']) ? ' alt="'.$ordine['valore'].'"' : '').' value="&#708;" title="Ordina A->Z" />';
             $tmp = $tabella_richiesta.'-'.$campo.'-'.$id_tabella_padre.'-DESC-ordinacampo';
             $str .= '<INPUT class="'.((isset($ordine['id_ordine']) && $tmp == $ordine['id_ordine']) ? 'pulsante_ord_set' : 'pulsante_ord').'" type="button" id="'.$tmp.'" onClick="ordina_campo(this.id);"'.((isset($ordine['id_ordine']) && $tmp == $ordine['id_ordine']) ? ' alt="'.$ordine['valore'].'"' : '').' value="&#709;" title="Ordina Z->A" />';

               $tmp = $tabella_richiesta.'-'.$campo.'-'.$id_tabella_padre.'-filtracampo';
               $str_div_tag = (isset($filtro['id_filtro']) AND $tmp == $filtro['id_filtro'] AND isset($filtro['valore']) AND trim($filtro['valore'])) ? TRUE : FALSE;
               if($valore['tipo'] == 'booleano')  // Filtro particolare per il checkbox field type
                   $str .= '&nbsp;&nbsp;<SPAN'.($str_div_tag ? ' class="input_filtro_attivo"' : '').'><INPUT class="blue-input" onChange="this.value=(this.checked ? 1 : 0);filtro_campo(this.id);" type="checkbox" id="'.$tmp.'" '
                        .'value="'.($str_div_tag  ? $filtro['valore'] : 0).'" '.($str_div_tag ? ($filtro["valore"] ? 'checked' : '') : '').'/></SPAN>';
               else
                   $str .= '&nbsp;<SPAN'.($str_div_tag ? ' class="input_filtro_attivo"' : '').'><INPUT class="blue-input" size="'.$valore['size_filtro'].'" onChange="filtro_campo(this.id);" type="text" id="'.$tmp.'" '
                        .'value="'.((isset($filtro['id_filtro']) && $tmp == $filtro['id_filtro']) ? $filtro['valore'] : '').'" '.((isset($valore['attributi']['pattern']) AND $valore['attributi']['pattern']) ? ' pattern="'.$valore['attributi']['pattern'].'"' : '' ).' /></SPAN>';
            }
            else
               $str .= '<TD class="nowrap_td">&nbsp;&nbsp;';
            $str .= "</TD>\n";
          }  // fine foreach
        $str .= ($colspan_ultima_colonna ? '' : '<TD><DIV>&nbsp;</DIV></TD>')."</TR>\n";  // ultimo campo vuoto
    } // fine if filtro tabella

// per recuperare il numero di record - nell' aggiungere un record devo andare nell'ultima pagina ma verifico di non essere oltre
   if(($tmp = $tabella[$tabella_richiesta]['pulsanti']['paginazione']) > 0) {
     $npagine = intval(ceil($nrecord / $tmp));
       if($pagina == 'ultima') {
           $startpagina = ($nrecord > $tmp) ? ($nrecord - $tmp) : 0;
           $pagina = $npagine;
       }
       elseif(is_numeric($pagina) and (($pagina * $tmp) > $nrecord)) {
           $pagina = $npagine;
           $startpagina = (($nrecord - $tmp) > -1) ? ($nrecord - $tmp) : 0;
       }
       elseif(is_numeric($pagina)) {
           $startpagina = ($pagina -1) * $tmp;
       }
       else {  // Se $pagina non e' un numero o e' "prima"
           $pagina = 1;
           $startpagina = 0;
       }
  } // fine if "paginazione"

 $tmp = $str_query.$str_query_cond.$str_query_ord.($tmp > 0 ? ' LIMIT '.$startpagina.', '.$tmp : '');
 $result = $conn->sql_query($tmp);
 if(!$result)
    return 'Query fallita: '.$tmp;

    if ($result->num_rows > 0) {
         while($row = $result->fetch_assoc()) {  // output data of each row
            $conta_record++;    // Primo record esaminato
            $str_div_tag = '';  // Reset per utilizzo successivo
            $conteggio_record_tbl_figlie = array();   // Array associativo reset

            foreach($tabella[$tabella_richiesta]['figlia'] as $tmp => $valore) { // > 0 per cut tabella studenti in $row["id"] id tabella padre - se non ci sono record nella tabella figlia non ha senso il pulsante di espansione
                     // La chiave cambia a seconda della presenza di un valore in 'chiave_figlia'
                     $id_chiave = ($tabella[$tmp]['pulsanti']['chiave_figlia'] ? (is_numeric($row[$tabella[$tmp]['pulsanti']['chiave_figlia']]) ? $row[$tabella[$tmp]['pulsanti']['chiave_figlia']] : 0) : $row['id']);
                     $conteggio_record_tbl_figlie[$tmp] = check_figlia_record($conn, $tmp, $id_chiave);
                     if(!is_numeric($conteggio_record_tbl_figlie[$tmp])) {
                         return 'Query conteggio record fallita !';
                     }
                     // Aggiungo la v perche' non posso avere id duplicati quando un record della tabella padre richiama piu' volte il medesimo record della tabella figlia
                     $id_chiave .= $tabella[$tmp]['pulsanti']['chiave_figlia'] ? 'v'.$row['id'] : '';
                     if($tabella[$tmp]['pulsanti']['add'] OR $conteggio_record_tbl_figlie[$tmp] > 0 OR !$tabella[$tmp]['pulsanti']['hidevert'])  // Non nascondo il + se hidevert FALSE
                         $str_div_tag .= '<TD><DIV class="espandi_tabella" id="'.$tmp.'-'.$tabella_richiesta.'-'.$id_chiave.'-veditabella">&#x1F50E;</DIV></TD>';   // &#x1F50E; lente destra
                     else
                         $str_div_tag .= '<TD><DIV>&nbsp;&nbsp;</DIV></TD>';
            }  // fine foreach  .($tabella[$tmp]['pulsanti']['chiave_figlia'] ? 'v'.$row['id'] : '')

            $str .= "\n<TR class=\"tr_over\">$str_div_tag".($tabella[$tabella_richiesta]['pulsanti']['verticale'] ? '<TD><DIV class="espandi_tabella_alias" id="'.$tabella_richiesta."ALIAS-$tabella_richiesta-".$row['id'].'-veditabella">+</DIV></TD>' : '')
                 .($tabella[$tabella_richiesta]['nascondi_id'] ? '' : '<TD>'.$row['id'].'</TD>');
            $str .= genera_campo_tabella($tabella_richiesta, $tabella, $row, FALSE, ($conta_record == $result->num_rows AND $ultimo_record));

            if(!$colspan_ultima_colonna)  {  // Non emetto ultima colonna se -1
                 if(!empty($tabella[$tabella_richiesta]['figlia']))
                      $str_query = array_sum($conteggio_record_tbl_figlie);
                 else
                      $str_query = 'NO SUB';  // Non ci sono tabelle figlie

                 $str_div_tag = $tabella_richiesta.'-'.$id_tabella_padre.'-'.$row['id'].'-eliminarecord';
                 if((0 == $str_query || 'NO SUB' == $str_query) && $tabella[$tabella_richiesta]['pulsanti']['delete'])
                     $str .= '<TD data-alt="'.$str_div_tag.'" data-nodel="true"><INPUT class="pulsante_x" id="'.$str_div_tag.'" type="button" value="&#128465;" onClick="elimina_record(this.id);" title="ELIMINA IL RECORD" /></TD></TR>';
                 else  // Attributo data-nodel == true per permettere il pusante di eliminazione del record -> false == non e' prevista la cancellazione dei record
                     $str .= '<TD data-alt="'.$str_div_tag.'" data-nodel="'.($tabella[$tabella_richiesta]['pulsanti']['delete'] ? 'true' : 'false').'">SUB:&nbsp;<B>'.$str_query.'</B></TD></TR>';
            }  // fine if emissione ultima riga della colonna
            else
                $str .= '</TR>';
         }  // fine while

      $str .= '</TBODY>';
         // Ora traccio i pulsanti   di navigazione della parte bassa se e' stata richiesta la paginazione
      if($tabella[$tabella_richiesta]['pulsanti']['paginazione'] > 0 AND $tabella[$tabella_richiesta]['pulsanti']['paginazione'] < $nrecord ) {
        // Numero totale delle pagine in $npagine
        // id = nometabella-npag-idpadre-paginazione
        $pagina = (($pagina > $npagine) ? $npagine : $pagina);
        $str .= '<TFOOT><TR><TD style="text-align:center;" colspan="'.($colspan_ultima_colonna + 1 + ($tabella[$tabella_richiesta]['nascondi_id'] ? 0 : 1) - $salto_colonna_x_verticale + count($tabella[$tabella_richiesta]['figlia']) + ($tabella[$tabella_richiesta]['pulsanti']['verticale'] ? 1 : 0) + count($tabella[$tabella_richiesta]['campi']))
               .'">Righe&nbsp;<B>'.$tabella[$tabella_richiesta]['pulsanti']['paginazione'].'</B>&nbsp;su&nbsp;<B>'.$nrecord.'</B>&nbsp;totali&nbsp;&nbsp;'
               .'<INPUT class="pulsante_dir" onClick="paginatore_tabella(this.id);" type="button" value="&nbsp;<<&nbsp;" id="'.$tabella_richiesta.'-prima-'.$id_tabella_padre.'-paginazione" />&nbsp;'
               .'<INPUT class="pulsante_dir" onClick="paginatore_tabella(this.id);" type="button" value="&nbsp;<&nbsp;" id="'.$tabella_richiesta.'-'.(($pagina - 1) < 1 ? 1 : ($pagina -1)).'-'.$id_tabella_padre.'-paginazione" />&nbsp;'
               .'Pagina&nbsp;<INPUT alt="'.$pagina.'" class="blue-input" onChange="paginatore_tabella(this.id);" type="text" size="3" value="'.$pagina.'" id="'.$tabella_richiesta.'-campolibero-'.$id_tabella_padre.'-paginazione" />&nbsp;'
               .'di <SPAN id="'.$tabella_richiesta.'-npagine-'.$npagine.'">'.$npagine.'</SPAN>&nbsp;'
               .'<INPUT class="pulsante_dir" onClick="paginatore_tabella(this.id);" type="button" value="&nbsp;>&nbsp;" id="'.$tabella_richiesta.'-'.(($pagina + 1) >= $npagine ? $npagine : ($pagina +1)).'-'.$id_tabella_padre.'-paginazione" />&nbsp;'
               .'<INPUT class="pulsante_dir" onClick="paginatore_tabella(this.id);" type="button" value="&nbsp;>>&nbsp;" id="'.$tabella_richiesta.'-ultima-'.$id_tabella_padre.'-paginazione" />'
               .'</TD></TR></TFOOT>'."\n";
      }  // fine if check paginazione
     }  // Fine if $result->num_rows
     else
        $str .= '</TBODY>';
     $result->close();

return $str."\n".'</TABLE></FORM>'."\n";
}  // fine funzione genera tabella


function aggiungi_record_tabella(&$conn, &$tabella_richiesta, &$tabella, $id_tabella_padre) {
// $tabella_richiesta == tabella dove aggiungere il record nuovo, $tabella == il model con le tabelle, $id_tabella_padre == chiave esterna
$str = 'INSERT INTO '.$tabella[$tabella_richiesta]['nometabella'].' ';
$valori = $campi = $chiave = $valore = '';

    foreach($tabella[$tabella_richiesta]['campi'] as $chiave => $valore)  // composizione campi default
        if(isset($valore['attributi']['default_value']) AND (0 < strlen(strval($valore['attributi']['default_value'])))) {  // se il valore e' settato e previsto
           $campi .= $chiave.',';
           $valori .= "'".addslashes(trim($valore['attributi']['default_value']))."',";
        }
    if($valori) {  // Se esiste almeno un valore di default tra i campi tolgo la virgola finale separatrice
        $campi = substr($campi, -1) == ',' ? substr($campi, 0, -1) : $campi;
        $valori = substr($valori, -1) == ',' ? substr($valori, 0, -1) : $valori;
    }
    if($id_tabella_padre == 0) // Non c'e' chiave esterna da aggiungere
       $str .= "($campi) VALUES($valori)"; // Inserisco una riga con i valori di default
    else
       $str .= '('.$tabella[$tabella_richiesta]['chiave_padre'].($campi ? ",$campi" : '').') VALUES('.$id_tabella_padre.($valori ? ",$valori" : '').')';  // Inserisco solo un valore vuoto

    if (-1 == @$conn->sql_command($str))
        return 'Errore query: '.$conn->errore;
    else
        return '';  // No problem
}  // fine function aggiungi_record_tabella


if(!function_exists('json_encode')) {
  function json_encode($array){
    if(!is_array($array))
        return false;

    $associative = count(array_diff(array_keys($array), array_keys(array_keys($array))));
    if($associative) {
        $construct = array();
        foreach($array as $key => $value){
            // We first copy each key/value pair into a staging array,
            // formatting each key and value properly as we go.
            // Format the key:
            if( is_numeric($key) ){
                $key = "key_$key";
            }
            $key = '"'.addslashes($key).'"';
            // Format the value:
            if( is_array( $value )){
                $value = json_encode( $value );
            } else if( !is_numeric( $value ) || is_string( $value ) ){
                $value = '"'.addslashes($value).'"';
            }
            // Add to staging array:
            $construct[] = "$key: $value";
        }
        // Then we collapse the staging array into the JSON form:
        $result = '{ ' . implode(', ', $construct ) . ' }';

    } else { // If the array is a vector (not associative):
        $construct = array();
        foreach( $array as $value ){
            // Format the value:
            if( is_array( $value )){
                $value = json_encode( $value );
            } else if( !is_numeric( $value ) || is_string( $value ) ){
                $value = '"'.addslashes($value).'"';
            }
            // Add to staging array:
            $construct[] = $value;
        }
        // Then we collapse the staging array into the JSON form:
        $result = '[ ' . implode(', ', $construct ) . ' ]';
    }
  return $result;
  } // fine function json_encode
}  // fine if not exists


function ripristina_quantita_prima_cancellazione(&$conn, &$nome_tabella, &$id_car) {  // Prima di cancellare un articolo dal carrello bisogna ripristinarne la quantita in magazzino
// Solo gli articoli nella tabella dei carrelli in attesa di conferma possono essere cancellati
  if(0 <> strcasecmp('carartatt', $nome_tabella) OR -1 <> @$conn->sql_command('UPDATE articoli AS A SET A.quantita = (A.quantita + (SELECT C.quantita FROM carrelloart AS C WHERE C.id ='.$id_car
     .')) WHERE A.id = (SELECT C.id_articolo FROM carrelloart AS C WHERE C.id = '.$id_car.') LIMIT 1'))
        return TRUE;
  else
        return FALSE;
}  // fine function ripristina_quantita_prima_cancellazione


function elimina_record_tabella(&$conn, &$tabella, $tabella_del, $id_del) {
// Verifico se ho i permessi per la cancellazione record in tabella - controllo server side
if(!$tabella[$tabella_del]['pulsanti']['delete'])
    return "\nNon hai i permessi di cancellazione nella tabella: ".$tabella[$tabella_del]['nometabella'];

$str_query = 'SELECT * FROM '.$tabella[$tabella_del]['nometabella'].' WHERE id='.$id_del.' LIMIT 1';    // LIMIT 1 x sicurezza
$result = &$conn->sql_query($str_query);  // Ritorna 0 se errore

 if($result) {  // Solo un record per volta e' accettato
     $row = $result->fetch_assoc();
 // Controllo la presenza di campi di tipo file per la rimozione del file nel file system
    foreach($tabella[$tabella_del]['campi'] as $campo => $valore)  {  // produco i campi
        if($valore['tipo']=='file' AND $row[$campo]) {
           $str_query = dirname($_SERVER['SCRIPT_FILENAME']).(DIRECTORY_SEPARATOR).$valore['attributi']['cartella_upload'].(DIRECTORY_SEPARATOR).$row[$campo];
           $str_query = (file_exists($str_query) AND unlink($str_query));  // Non emetto errori
        }
    } // fine foreach

     $result->close();
     if(ripristina_quantita_prima_cancellazione($conn, $tabella_del, $id_del) AND -1 <> (@$conn->sql_command('DELETE FROM '.$tabella[$tabella_del]['nometabella']." WHERE id=$id_del LIMIT 1")))   // LIMIT 1 per sicurezza
          return "CANCELLATO record:\n\n".str_replace(array("{", "}"), '', implode("\n", explode(',', json_encode($row, JSON_FORCE_OBJECT))));
     else
          return "\nERRORE in cancellazione record: ".$conn->errore;
 }  // fine if num_rows
 else
     return "\nERRORE nella query: ".$str_query;
} // fine function elimina_record_tabella


function genera_select(&$conn, $tabella='nonesiste', $campo='nonesiste') {  //   produce l'array con la coppia descrizone valore da usare nella select
// Devo limitare l'overload delle query alle sole tabelle necessarie e solo all' admin
  if(!$GLOBALS['app_admin'] OR !isset($_REQUEST['tbl_nome']) OR !in_array(togli_alias_tbl($_REQUEST['tbl_nome']), array('prod', 'prodo', 'prodcar')))
      return array('default_vuoto' => '--------');
$arr = array();

    $str_query = 'SELECT valore, descrizione FROM vocimenu WHERE id_menu IN (SELECT id FROM menu WHERE tabella LIKE \''.$tabella.'\' AND nomecampo LIKE \''.$campo.'\');';
    $result = &$conn->sql_query($str_query);
    if($result AND $result->num_rows > 0) {  // Solo un record per volta e' accettato
        while($row = $result->fetch_row())
           $arr[$row[0]] = $row[1];
        // fine ciclo foreach
        mysqli_free_result($result);
        asort($arr);
        $arr = array_merge(array('default_vuoto' => '--------'), $arr);
    }
    else
      $arr = array('default_vuoto' => 'ERRORE_QRY');

return array_unique($arr);
} // fine function genera_select


function associa_record(&$conn, $nometabella, $valore, $id_tabella, $associa = TRUE) {
  if($nometabella == 'articoli') {
     if(-1 <> @$conn->sql_command('UPDATE '.$nometabella.' SET id_prodotto='.$id_tabella.' WHERE id='.$valore.' LIMIT 1'))
        return $id_tabella;  // Valore numerico tutto OK
     else
        return 'ERRORE query:'.$conn->errore;
  }
  if($nometabella == 'ordineart' AND $associa) {
     if(-1 <> @$conn->sql_command('INSERT INTO '.$nometabella.' (id_articolo, id_ordine, datainserimento) VALUES ('.$id_tabella.','.$valore.',\''.date('Y-m-d H:i:s').'\')'))
        return $id_tabella;  // Valore numerico tutto OK
     else
        return 'ERRORE query: '.$conn->errore;
  }
  if($nometabella == 'ordineart' AND !$associa) {
     if(-1 <> @$conn->sql_command('DELETE FROM '.$nometabella.' WHERE id_articolo = '.$id_tabella.' AND id_ordine = '.$valore.' LIMIT 1'))
        return $id_tabella;  // Valore numerico tutto OK
     else
        return 'ERRORE query: '.$conn->errore;
  }
}  // fine function associa record


function ottieni_gruppi_utente(&$conn, $email, $passwd = '') {  // data l'email restituisce l'array con i gruppi di appartenenza dell'utente - eventuale password per autenticazione con password
$gruppi = array();
  // L 'utente potrebbe essere responsabile di un gruppo quindi e' autenticato a livello di gruppo senza necessita di esaminare la lista degli utenti
  // Considero che non puo' essere responsabile di piu' gruppi ! */
 $result = &$conn->sql_query('SELECT nome FROM gruppi WHERE emailresp LIKE \''.$email.'\''.($passwd ? ' AND password LIKE \''.$passwd.'\'' : ''));
   if($result) {
      if($result->num_rows > 0)     // Solo un record esamino
          while($row = $result->fetch_row())
                 $gruppi[strtoupper($row[0])] = strtoupper($row[0]);
   mysqli_free_result($result);
   }

// Si potrebbe usando una password diversa assegnare un gruppo specifico aggiuntivo
 $result = &$conn->sql_query('SELECT nome FROM gruppi WHERE id IN (SELECT id_gruppo FROM utenti WHERE email LIKE \''.$email.'\''.($passwd ? ' AND password LIKE \''.$passwd.'\'' : '').')');
   if($result) {
       if($result->num_rows > 0)   // Solo un record per volta e' accettato
           while($row = $result->fetch_row())
                $gruppi[strtoupper($row[0])] = strtoupper($row[0]);
   mysqli_free_result($result);
   }

   if(0 == count($gruppi))
        $gruppi['MANCA_GRUPPO'] = 'MANCA_GRUPPO';

   if(in_array('ADMIN', $gruppi)) {     // L'amministratore appartiene a tutti i gruppi - includo tutti i gruppi per l'ADMIN
       $gruppi = [];
       $result = &$conn->sql_query('SELECT nome FROM gruppi');
          while($row = $result->fetch_row())
                 $gruppi[strtoupper($row[0])] = strtoupper($row[0]);
   mysqli_free_result($result);
   }
   asort($gruppi);

return array_unique($gruppi);  // Rimozione degli eventuali gruppi duplicati
} // fine function ottieni gruppo utente


function password_recovery(&$conn, $email) {   // Recupera la password per un dato indirizzo email

 $result = $conn->query_count('SELECT password FROM gruppi WHERE emailresp LIKE \''.$email.'\' LIMIT 1');   // Ricerca nei gruppi
     if(-1 == $result) {   // Non presente nei gruppi
        $result = $conn->query_count('SELECT password FROM utenti WHERE email LIKE \''.$email.'\' LIMIT 1');   // Ricerca pwd negli utenti
        if(-1 == $result)
            return 'Utente non registrato !';  // Utente non presente
     }

     require_once('./messaggio.php');
     $msg = new Messaggio($conn, 0);
     $out_msg = 'destinatario='.urlencode($email).'&oggetto='.urlencode('Invio password').'&messaggio='.urlencode('username: '.$email.'<BR />password: '.$result);

return $msg->invia_messaggio($out_msg);  // Risultato dell invio della password
} // fine function password_recovery


function genera_pulsanti_carrello(&$tabella) {  // Display pulsanti di annullo e conferma - vale solo per la $tabella['carnuovo']
 if(isset($_SESSION['DB_SEME']) AND $tabella == 'carnuovo')
    return '<DIV style="margin-left:100px;padding-bottom:10px;white-space:nowrap;"><FORM onSubmit="return false;"><INPUT class="pulsante" type="button" value="X&nbsp;ANNULLA&nbsp;CARRELLO" onclick="annulla_formazione_carrello();return false;" />'
           .'&nbsp;&nbsp;&nbsp;<INPUT class="pulsante" type="button" value="&#10003;&nbsp;INVIA&nbsp;IL&nbsp;CARRELLO" onclick="conferma_formazione_carrello();" />'
           .'&nbsp;&nbsp;<METER id="timer_1m" type="meter" low="3" high="10" optimum="4" min="1" max="10" value="'.TIMEOUT_COMPILAZIONE_CARRELLO
           .'"></METER><LABEL class="classe_label"><SPAN id="meter_id1">'.TIMEOUT_COMPILAZIONE_CARRELLO.' minuti</SPAN> rimanenti per inviare il carrello</LABEL></FORM></DIV>';
 elseif(isset($_SESSION['DB_SEME']) AND $tabella == 'prodcar') {
    return '<TABLE class="specialegend"><TR><TD colspan="6"><B>Vengono visualizzati SOLO i prodotti ordinabili o presenti nel deposito del servizio di approvvigionamento</B></TD></TR>'
           ."\n".'<TR><TD>LEGENDA:</TD><TD><SPAN class="span_dispo">prodotti presenti</SPAN></TD><TD><SPAN class="span_riordino">prodotti in fase di riordino</SPAN></TD><TD><SPAN class="span_ord">prodotti non presenti (ordinabili)</SPAN></TD>'
           .'<TD><SPAN class="span_esaurim">prodotti a esaurimento scorte (non ordinabili)</SPAN></TD><TD><SPAN class="span_limit">prodotti con limitazioni</SPAN></TD></TR></TABLE>'."\n";
 }
 else
    return '';
}  // fine function genera_pulsanti_carrello

// key = chiave dell'array dopo la quale inserire il nuovo elemento - $array = array associativo target dell'inserimento - $new_key = nuova chiave - $new_value = nuovo valore (puo essere anche un array
// Es.  $tabella['lista_in_attesa']['campi'] = array_insert_after('descrizioneutente', $tabella['lista_in_attesa']['campi'], 'servizio', array('tipo' => 'select', 'etichetta' => 'Servizio', 'editable' => TRUE, 'size_filtro' => 10, 'vert_larghezza' => 12, 'attributi' => ...));
function array_insert_after($key, array &$array, $new_key, array $new_value) {
  if(array_key_exists($key, $array)) {
      $new = array();
         foreach ($array as $k => $value) {
              $new[$k] = $value;
              if ($k === $key) {
                  $new[$new_key] = $new_value;
              }
         }
  return $new;
  }
return FALSE;
}  // fine function array_insert_after

?>