<?PHP   // michele.furlan@unipd.it - 15 gennaio 2024

class Messaggio {
private $db = NULL;        // Handle connessione
private $id_carrello = 0;  // id univoco che identifica il carrello oggetto del messaggio
private $mail_error = '';

  public function __construct(&$conn, $id) {
           $this->db = $conn;
           $this->id_carrello = $id;    // id del carrello nuovo in lavorazione
  }  // fine costruttore della classe

  private function richiedente_carrello() {  // Estrae l'indirizzo email dello user che ha chiesto il carrello; in base all'ID carrello
  // Prima cerco l'indirizzo email nella tabella responsabile dei gruppi - poi nella tabella utenti
   $result = $this->db->query_count("SELECT emailresp FROM gruppi WHERE SUBSTRING_INDEX(emailresp, '@', 1) IN (SELECT username FROM carrelli WHERE id=".$this->id_carrello.') LIMIT 1');
      if(-1 == $result) { // Non e' stato trovato o la query ha restituito un errore  - riprovo la ricerca nella tabella degli utenti
         $result = $this->db->query_count("SELECT email FROM utenti WHERE SUBSTRING_INDEX(email, '@', 1) IN (SELECT username FROM carrelli WHERE id=".$this->id_carrello.') LIMIT 1');
      }
  return (-1 == $result ? 'UTENTE NON PRESENTE NEL DATABASE !!' : $result);
  }  // fine richiedente_carrello


  private function data_carrello() {
    $result = $this->db->query_count('SELECT UNIX_TIMESTAMP(datainserimento) FROM carrelli WHERE id='.$this->id_carrello.' LIMIT 1');

  return (-1 == $result ? 'NO_DATA' : date('l j-F-Y H:i', $result));
  }  // Fine data_carrello()

  public function genera_form() {

$html = <<<HTMLE
      <STYLE>.wrapper_email {display:grid;grid-template-columns:auto auto;grid-gap:10px;} .wrapper_email > DIV {text-align:left;font-size:14pt;}</STYLE>
      <FORM onSubmit="return false" id='form_invio_msg'><DIV class="wrapper_email">
      <DIV><LABEL>MITTENTE:</LABEL></DIV><DIV>%MITTENTE%</DIV>
      <DIV><LABEL>DESTINATARIO:</LABEL></DIV><DIV><INPUT name="destinatario" class="blue-input" type="email" required value="%DESTINATARIO%" placeholder="nome.cognome@unipd.it" size="48" maxlength="150" pattern="%PATTERN%" title="Inserire un indirizzo email valido !" /></DIV>
      <DIV><LABEL>OGGETTO:</LABEL></DIV><DIV><INPUT name="oggetto" class="blue-input" type="text" required value="%OGGETTO%" placeholder="Inoltro da parte del servizio approvvigionamento" size="74" maxlength="150" pattern=".{4,150}" title="Inserire almeno 4 caratteri nel campo OGGETTO !" /><BR />&nbsp;</DIV>
      <DIV><LABEL>MESSAGGIO:</LABEL></DIV><DIV><TEXTAREA name="messaggio" class="blue-input" required placeholder="Inserire il testo del messaggio da inviare. Max. 500 caratteri." rows="6" cols="60" maxlength="500" pattern=".{4,}" title="Inserire almeno 4 caratteri nel campo MESSAGGIO !"></TEXTAREA></DIV>
      </DIV><BR />
      <INPUT class="pulsante" type="button" value="INVIA&nbsp;IL&nbsp;MESSAGGIO" onClick="if(confirm('Vuoi inviare il messaggio a: \\n%DESTINATARIO% ?')) gestione_messaggio(%ID_CARRELLO%,'INVIA');return false;" />
      </FORM>
HTMLE;

  $html = str_replace('%PATTERN%', EMAIL_PATTERN_REGX, str_replace('%OGGETTO%', str_replace('%DATA_CARRELLO%', $this->data_carrello(), str_replace('%ID_CARRELLO%', $this->id_carrello, OGGETTO_MESSAGGIO_SERVIZIO)), $html));
  return str_replace('%MITTENTE%', EMAIL_SERVIZIO, str_replace('%DESTINATARIO%', $this->richiedente_carrello(), str_replace('%ID_CARRELLO%', $this->id_carrello, $html)));
  }  // fine function genera_form


public function invia_messaggio($dati) {  // Salva i dati nel database e gli invia tramite posta elettronica
   parse_str($dati, $out_msg);  // Processa la stringa variabile=valore&variabile2=valore2 ecc....
   $out_msg['destinatario'] = htmlspecialchars(clean_dato(urldecode($out_msg['destinatario'])));
   $out_msg['oggetto'] = htmlspecialchars(clean_dato(urldecode($out_msg['oggetto'])));
   $out_msg['messaggio'] = clean_dato(urldecode($out_msg['messaggio']));

  if(strlen($out_msg['destinatario']) < 4 || !preg_match('/'.EMAIL_PATTERN_REGX.'/',  $out_msg['destinatario']) || strlen($out_msg['oggetto']) < 4)
     return '0';   // $this->id_carrello > 0 perche' non registro i messaggi quando non c'e' un id carrello associato - utile per invio pwd nell form di login
  if($this->id_carrello > 0 AND -1 == $this->db->sql_command('INSERT INTO comunicazioni (id_carrello,datainserimento,mittente,destinatario,oggetto,mailtxt) VALUES('.$this->id_carrello.',CURRENT_TIMESTAMP(),\''.EMAIL_SERVIZIO
                                .'\',\''.$out_msg['destinatario'].'\',\''.$out_msg['oggetto'].'\',\''. $out_msg['messaggio'].'\')'))
     return '0'; // Errore di invio
  else {
     if(ENABLE_SEND_MAIL) {
        $out_msg['messaggio'] .= '<BR /><BR />'.MITTENTE_SERVIZIO.'<BR />'.EMAIL_FIRMA;    // Aggiunta della firma
        return $this->invia_email($out_msg);
     }
     else
        return 'INVIO EMAIL DISABILITATO';
  }   // Fine else
}  // fine invia_messaggio


private function invia_email(&$out_msg) {

require_once ('./PHPMailer/PHPMailerAutoload.php');
//Create a new PHPMailer instance
$mail = new PHPMailer;
//Tell PHPMailer to use SMTP
$mail->isSMTP();
//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 0;
//Ask for HTML-friendly debug output
$mail->Debugoutput = 'html';
//Set the hostname of the mail server
$mail->Host = 'smtp.unipd.it';   // ex zero.chfi.unipd.it
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = 25;
//Whether to use SMTP authentication
$mail->SMTPAuth = false;
//Set who the message is to be sent from - mettere nel from un dominio noto @chimica.unipd.it non FUNZIONA !
$mail->setFrom(EMAIL_SERVIZIO, MITTENTE_SERVIZIO);
// Set an alternative reply-to address
// $mail->addReplyTo("no_reply_disc@unipd.it", "DiSC ticket");
// Set who the message is to be sent to
$mail->addAddress($out_msg['destinatario'], ucwords(str_replace('.', ' ', explode('@', $out_msg['destinatario'])[0])));
// Add CC  - Serve il secondo campo Nome Cognome - es. $mail->AddCC('qquicker8@gmail.com', 'Nome Cognome');
//$mail->AddCC(trim($_SESSION['GAUTH_mail']), $GLOBALS['sso_appticket_cognome']);

//Set the subject line
$mail->Subject = $out_msg['oggetto'];
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->msgHTML($out_msg['messaggio']);

   if(!$mail->send()) {
      $this->mail_error = $mail->ErrorInfo;
      return 0;
   }
   else
      return 'Il messaggio e\' stato inviato con successo in data e ora: '.date('D M j G:i:s T Y');
}  // fine funzione invia mail

}  // Fine classe Messaggio

?>