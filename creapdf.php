<?PHP // michele.furlan@unipd.it  05 febbraio 2024
require_once('./config.php');

// https://stackoverflow.com/questions/24412203/dompdf-and-set-different-font-family 7 anni fa
// https://makitweb.com/how-to-set-different-font-family-in-dompdf/ OK !
// https://developer.mozilla.org/en-US/docs/Web/CSS/page

// Include autoloader
require_once 'dompdf/autoload.inc.php';
// Reference the Dompdf namespace
use Dompdf\Dompdf;

const CREA_INTESTAZIONE_PDF = '<HTML><HEAD><STYLE>BODY {display:block;margin:0px;font-size:10pt;} @page {size:a4 landscape;margin: 8mm 8mm 8mm 8mm;} '
                             .'TABLE TR TH {background-color:#EAE7DD;padding:6px;white-space:nowrap;} .moresize {font-size:10pt;} '
                             .'TABLE TR TD {border-width:1px;padding:2px;border-style:none solid dashed none;border-color:gray;} '
                             .'.nobord {border-width:0px;} '
                             .'TFOOT {background-color: #F8BF62;} P.hlev {padding:0px;width:100%;font-size:15pt;display:block;text-align:center;vertical-align:middle;} '
                             .'LABEL.prev {padding:4px;font-size:12pt;text-align:left;background-color:#fbdd40;vertical-align:middle;} '
                             .'FOOTER {text-align:center;position:fixed;bottom:0px;} '
                             .'.span_esaurito {display:inline-block;background-color:#F72585;width:100%;text-align:center;white-space:nowrap;} '
                             .'@media print fieldset {display:none;} footer {page-break-after:always;}</STYLE><TITLE>STAMPA PDF</TITLE></HEAD><BODY>'
                             .'<TABLE><TR><TD class="nobord"><IMG src="./imma/logo_stampa.png" alt="LOGO_DiSC" border="0" /></TD><TD class="nobord"><P class="hlev">&nbsp;&nbsp;&nbsp;'.MITTENTE_SERVIZIO.'</P></TD></TR></TABLE><BR /><BR />';

function stampa_con_dompdf(&$html) {

 // Instantiate and use the dompdf class
$dompdf = new Dompdf();
$dompdf->loadHtml($html, 'UTF-8');  // ex latin1 pre 07 febb 2024

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'landscape');
$dompdf->set_option('defaultMediaType', 'all');
$dompdf->set_option('isFontSubsettingEnabled', true);

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream('file_carrello', array('compress' => 1 , 'Attachment' => 1));

} // fine function stampa_con_dompdf

function crea_pdf_carrello(&$conn, $id, $gruppi='') {  // Genera un file PDF con il carrello corrispondente all id
 $html = CREA_INTESTAZIONE_PDF;
 $result = &$conn->sql_query('SELECT * FROM carrelli WHERE id='.$id.$gruppi.' LIMIT 1');

 if($conn->errore)
    $html .= '<BR /><H3>Errore nella query SELECT CARRELLI: '.$conn->errore.'</H3>';
 else {
    $row = $result->fetch_assoc();
    $html .= '<TABLE class="moresize"><THEAD><TR><TH>ID CARRELLO:&nbsp;&nbsp;<B>'.$row['id'].'</B></TH><TH>DATA INSERIMENTO CARRELLO</TH><TH>RICHIEDENTE</TH><TH>GRUPPO DI APPARTENENZA</TH></TR></THEAD>'
          .'<TR><TD>Stato carrello del:&nbsp;'.date('d-F-Y H:i').'</TD><TD>'.$row['datainserimento'].'</TD><TD>'.$row['username'].'</TD><TD>'.$row['gruppo'].'</TD></TR></TABLE><BR /><BR />'."\n";
    @mysqli_free_result($result);
 }

 $result = &$conn->sql_query("SELECT id_articolo, dammi_campo_articoli('carart','descrizione',id_articolo) AS descr, quantita, @p:=dammi_campo_articoli('carart','prezzo',id_articolo) AS prezzo, "
                            ."@i:=dammi_campo_articoli('carart','iva',id_articolo) AS iva, @s:=dammi_campo_articoli('carart','sconto',id_articolo) AS sconto, IF((NOT confermato AND NOT ritirato), 'X', '') "
                            ."AS attesa, IF(confermato AND NOT ritirato, 'X', '') AS conf, IF(ritirato, 'X', '') AS rit, @t:=ROUND(quantita*@p,2) AS tot, ROUND(@t*(1-@s/100)*(1+@i/100),2) as tsi FROM carrelloart WHERE id_carrello=".$id.' ORDER BY descr ASC');

 if(!$conn->errore) {
   $html .= '<TABLE><THEAD><TR><TH>ID</TH><TH>DESC ARTICOLO</TH><TH>Q.T&Agrave;</TH><TH>PREZZO&nbsp;&euro;</TH><TH>IVA&nbsp;%</TH><TH>SCONTO&nbsp;%</TH><TH>ATTESA</TH><TH>CONF.</TH><TH>RIT.</TH><TH>Totale&nbsp;&euro;</TH><TH>Tot. + Iva - Sconto&nbsp;&euro;</TH></TR></THEAD><TBODY>'."\n";
      while($row = $result->fetch_assoc()) {   // Per tutti gli ID del dataset
        $html .= '<TR><TD>'.$row['id_articolo'].'</TD><TD>'.$row['descr'].'</TD><TD>'.$row['quantita'].'</TD><TD>'.$row['prezzo'].'</TD><TD>'.$row['iva'].'</TD><TD>'.$row['sconto'].'</TD><TD>'.$row['attesa'].'</TD><TD>'.$row['conf']
              .'</TD><TD>'.$row['rit'].'</TD><TD>'.$row['tot'].'</TD><TD>'.$row['tsi'].'</TD></TR>'."\n";
      }  // Fine while $row
      @mysqli_free_result($result);
 }
 else
      $html .= '<BR /><H3>Errore nella query SELECT CARRELLO ART: '.$conn->errore.'</H3>';

 $result = &$conn->sql_query('SELECT (SELECT IFNULL(SUM(A.prezzo*CR.quantita), 0) FROM articoli AS A INNER JOIN carrelloart AS CR ON CR.id_articolo = A.id WHERE NOT CR.confermato AND NOT CR.ritirato AND CR.id_carrello = '.$id.') AS t_a,'
                            .'(SELECT ROUND(IFNULL(SUM(A.prezzo*CR.quantita), 0),2) FROM articoli AS A INNER JOIN carrelloart AS CR ON CR.id_articolo = A.id WHERE CR.confermato AND NOT CR.ritirato AND CR.id_carrello = '.$id.') AS t_c,'
                            .'(SELECT ROUND(IFNULL(SUM(A.prezzo*CR.quantita), 0),2) FROM articoli AS A INNER JOIN carrelloart AS CR ON CR.id_articolo = A.id WHERE CR.confermato AND CR.ritirato AND CR.id_carrello = '.$id.') AS t_r,'
                            .'(SELECT ROUND(IFNULL(SUM(A.prezzo*CR.quantita), 0),2) FROM articoli AS A INNER JOIN carrelloart AS CR ON CR.id_articolo = A.id WHERE CR.id_carrello = '.$id.') AS tt,'
                            .'(SELECT ROUND(IFNULL(SUM(A.prezzo*CR.quantita*(1+(A.iva/100))*(1-(A.sconto/100))), 0), 2) FROM articoli AS A INNER JOIN carrelloart AS CR ON CR.id_articolo = A.id WHERE CR.confermato AND CR.ritirato AND CR.id_carrello = '.$id.') AS tis');

 if(!$conn->errore) {
    $row = $result->fetch_assoc();
    $html .= '</TBODY><TFOOT><TR><TD colspan="6">&nbsp;</TD><TD>'.$row['t_a'].'&nbsp;&euro;</TD><TD>'.$row['t_c'].'&nbsp;&euro;</TD><TD>'.$row['t_r'].'&nbsp;&euro;</TD><TD>'.$row['tt'].'</TD><TD>'.$row['tis'].'&nbsp;&euro;&nbsp;addebitati</TD></TR></TFOOT>';
       @mysqli_free_result($result);
    $html .= '</TABLE>';
 }
 else
     $html .= '<BR /><H3>Errore nella query SELECT TOTALI: '.$conn->errore.'</H3>';

$html .= '<FOOTER>'.EMAIL_FIRMA.'</FOOTER></BODY></HTML>';
stampa_con_dompdf($html);
} // fine function crea_pdf_carrello


function crea_pdf_ordine(&$conn, $id) {   // in $id == ordine da stampare
 $html = CREA_INTESTAZIONE_PDF;
   $result = &$conn->sql_query('SELECT * FROM ordini WHERE id='.$id.' LIMIT 1');

 if($conn->errore)
    $html .= '<BR /><H3>Errore nella query SELECT ORDINI: '.$conn->errore.'</H3>';
 else {
    $row = $result->fetch_assoc();
    $html .= '<TABLE class="moresize"><THEAD><TR><TH>ID ORDINE:&nbsp;&nbsp;<B>'.$row['id'].'</B></TH><TH>DATA INSERIMENTO ORDINE</TH><TH>DATA ORDINE</TH><TH>NUMERO ORDINE</TH><TH>NOTE</TH></TR></THEAD>'
          .'<TR><TD>Stato ordine del:&nbsp;'.date('d-F-Y H:i').'</TD><TD>'.$row['datainserimento'].'</TD><TD>'.$row['dataordine'].'</TD><TD>'.$row['nordine'].'</TD><TD>'.stripslashes($row['note']).'</TD></TR></TABLE><BR /><BR />'."\n";
    @mysqli_free_result($result);
 }

 $result = &$conn->sql_query("SELECT id_articolo, dammi_campo_articoli('ordart', 'descrizione', id_articolo) AS descart, dammi_campo_articoli('ordart', 'descprodo', id_articolo) AS descprod,quantita,qarrivo,"
                            ."@p:=dammi_campo_articoli('ordart', 'prezzo', id) AS prezzo,@i:=dammi_campo_articoli('carart','iva',id_articolo) AS iva,@s:=dammi_campo_articoli('carart','sconto',id_articolo) AS sconto,"
                            .'ROUND(@p * qarrivo, 2) AS totale,ROUND((@p*qarrivo*(1+@i/100)*(1-@s/100)),2) AS tsi FROM ordineart WHERE id_ordine='.$id.' ORDER BY descart ASC');

 if(!$conn->errore) {
   $html .= '<TABLE><THEAD><TR><TH>ID</TH><TH>DESC. ARTICOLO</TH><TH>DESC. PRODOTTO</TH><TH>QT.&Agrave; ORD.</TH><TH>Q.T&Agrave; CAR.</TH><TH>PREZZO&nbsp;&euro;</TH><TH>IVA&nbsp;%</TH><TH>SC.</TH><TH>TOTALE&nbsp;&euro;</TH><TH>TOT. + IVA - SCONTO&nbsp;&euro;</TH></TR></THEAD><TBODY>'."\n";
      while($row = $result->fetch_assoc()) {   // Per tutti gli ID del dataset
        // Per ogni dottorando il cognome nome e i membri della commissione li stampo una sola volta
        $html .= '<TR><TD>'.$row['id_articolo'].'</TD><TD>'.$row['descart'].'</TD><TD>'.$row['descprod'].'</TD><TD>'.$row['quantita'].'</TD><TD>'.$row['qarrivo']
              .'</TD><TD>'.$row['prezzo'].'</TD><TD>'.$row['iva'].'</TD><TD>'.$row['sconto'].'</TD><TD>'.$row['totale'].'</TD><TD>'.$row['tsi'].'</TD></TR>'."\n";
      }  // Fine while $row
      @mysqli_free_result($result);
 }
 else
      $html .= '<BR /><H3>Errore nella query SELECT ORDINI ART: '.$conn->errore.'</H3>';

 $result = &$conn->sql_query('SELECT (SELECT SUM(quantita) FROM ordineart WHERE id_ordine = '.$id.') AS qord,'
                            .'(SELECT SUM(qarrivo) FROM ordineart WHERE id_ordine = '.$id.') AS qarr,'
                            .'(SELECT ROUND(IFNULL(SUM(A.prezzo*OA.qarrivo), 0),2) FROM articoli AS A INNER JOIN ordineart AS OA ON OA.id_articolo = A.id WHERE OA.id_ordine = '.$id.') AS totqa,'
                            .'(SELECT ROUND(IFNULL(SUM(A.prezzo*OA.quantita), 0),2) FROM articoli AS A INNER JOIN ordineart AS OA ON OA.id_articolo = A.id WHERE OA.id_ordine = '.$id.') AS totqc,'
                            .'(SELECT ROUND(IFNULL(SUM(A.prezzo*OA.qarrivo*(1+(A.iva/100))*(1-(A.sconto/100))), 0), 2) FROM articoli AS A INNER JOIN ordineart AS OA ON OA.id_articolo = A.id WHERE OA.id_ordine = '.$id.') AS totivasc');

 if(!$conn->errore) {
    $row = $result->fetch_assoc();
    $html .= '</TBODY><TFOOT><TR><TD colspan="3">&nbsp;</TD><TD>'.$row['qord'].'</TD><TD>'.$row['qarr'].'</TD><TD>'.$row['totqa'].'&nbsp;&euro;</TD><TD colspan="2">&nbsp;</TD><TD>'.$row['totqc'].'</TD><TD>'.$row['totivasc'].'&nbsp;&euro;&nbsp;in carico</TD></TR></TFOOT>';
       @mysqli_free_result($result);
    $html .= '</TABLE>';
 }
 else
     $html .= '<BR /><H3>Errore nella query SELECT TOTALI: '.$conn->errore.'</H3>';

$html .= '<FOOTER>'.EMAIL_FIRMA.'</FOOTER></BODY></HTML>';
stampa_con_dompdf($html);
}   // fine function crea_pdf_ordine


function crea_pdf_preventivo(&$conn, $id) {   // in $id == preventivo da stampare
 $html = CREA_INTESTAZIONE_PDF;
 $id_preventivo = FALSE;

 $result = &$conn->sql_query('SELECT F.id AS f_id, F.denominazione AS f_den, F.contatto AS f_con, P.* FROM fornitori AS F JOIN preventivi AS P ON F.id = P.id_fornitore WHERE P.id='.$id.' LIMIT 1');

 if($conn->errore)
    $html .= '<BR /><H3>Errore nella query SELECT PREVENTIVI: '.$conn->errore.'</H3>';
 else {
    $row = $result->fetch_assoc();
    $id_preventivo = $row['id'];
    $html .= '<LABEL class="prev"><B>FORNITORE ID:</B> ['.$row['f_id'].'] <B>DENOMINAZIONE:</B> ['.$row['f_den'].'] <B>CONTATTO:</B> ['.$row['f_con'].']</LABEL><BR /><BR /><BR />'."\n"
          .'<TABLE class="moresize"><THEAD><TR><TH>ID PREVEVENTIVO:&nbsp;&nbsp;<B>'.$row['id'].'</B></TH><TH>DATA PREV.</TH><TH>NUMERO</TH><TH>DATA SCADENZA</TH><TH>MINIMO ORDINE</TH><TH>NOTE</TH></TR></THEAD>'
          .'<TBODY><TR><TD>DATA INSERIMENTO&nbsp;'.$row['datainserimento'].'</TD><TD>'.$row['datapreventivo'].'</TD><TD>'.$row['numero'].'</TD><TD>'.$row['datascadenza'].'</TD><TD>'.$row['minimo_ordine'].'</TD><TD>'.stripslashes($row['note']).'</TD></TR></TBODY></TABLE><BR /><BR />'."\n";
    @mysqli_free_result($result);
 }

// Generazione della tabella con gli articoli del preventivo
 if($id_preventivo && ($result = &$conn->sql_query('SELECT A.*, dammi_campo_articoli(\'art\', \'nome\', id) AS nomeprod FROM articoli AS A WHERE A.id_preventivo='.$id_preventivo))) {
     $html .= '<TABLE><THEAD><TR><TH>ID</TH><TH>DATA INS.</TH><TH>DESC. ARTICOLO</TH><TH>DESC. PRODOTTO</TH><TH>PREZZO&nbsp;&euro;</TH><TH>QT.&Agrave; NETTA.</TH><TH>CODICE FORNI.</TH><TH>IVA&nbsp;%</TH><TH>SC.&nbsp;%</TH><TH>NOTE</TH></TR></THEAD><TBODY>'."\n";
        while($row = $result->fetch_assoc()) {   // Per tutti gli ID del dataset
              $html .= '<TR><TD>'.$row['id'].'</TD><TD>'.$row['datainserimento'].'</TD><TD>'.$row['descrizione'].'</TD><TD>'.$row['nomeprod'].'</TD><TD>'.$row['prezzo']
              .'</TD><TD>'.$row['quantita'].'</TD><TD>'.$row['codice'].'</TD><TD>'.$row['iva'].'</TD><TD>'.$row['sconto'].'</TD><TD>'.stripslashes($row['note']).'</TD></TR>'."\n";
     }  // Fine while $row
     @mysqli_free_result($result);
 $html .= '</TBODY></TABLE>'."\n";
 }  // Fine if selezione articoli nel preventivo
 else
     $html .= '<BR /><H3>Errore nella query SELECT PREV. ARTICOLI: '.$conn->errore.'</H3>';

$html .= '<FOOTER>'.EMAIL_FIRMA.'</FOOTER></BODY></HTML>';
stampa_con_dompdf($html);
}  // fine function crea_pdf_preventivo

// La stampa del PDF puo' avvenire solo se l'utente appartiene al gruppo del carrello avente ID richiesto
if($_REQUEST['tipo'] == 'carrello')
   crea_pdf_carrello($conn_obj, isset($_REQUEST['tbl_id']) ? $_REQUEST['tbl_id'] : 0, !$app_admin ? $rpt['filtro_utente'] : '');  // Filtro sul gruppo proprietario dei carrelli

if($_REQUEST['tipo'] == 'ordine' && $app_admin)  // Solo admin puo' vedere gli ordini
   crea_pdf_ordine($conn_obj, isset($_REQUEST['tbl_id']) ? $_REQUEST['tbl_id'] : 0);

if($_REQUEST['tipo'] == 'preventivo' && $app_admin)  // Solo admin puo' vedere gli ordini
   crea_pdf_preventivo($conn_obj, isset($_REQUEST['tbl_id']) ? $_REQUEST['tbl_id'] : 0);

?>