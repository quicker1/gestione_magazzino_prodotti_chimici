<?PHP  // michele.furlan@unipd.it  02 febbraio 2024
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once('./function.php');
require_once('./conn.php');  // Classe per la connessione al DB

// !NON MODIFICARE le sottostanti due righe - $tabella_css_color stabilisce l'alternanza dei colori dei bordi nelle tabelle
define('MEGABYTES', pow(2,20));
define('EMAIL_PATTERN_REGX', "^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$");
define('URL_PATTERN_REGEX', "^(http(s)?:\/\/)?(www\.)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$");
define('STRINGA_REGEX_VALORI', "/[^\x01-\x7F]+|[\{\}\'\(\)\[\]\"]+/im", TRUE);   // Filtro sui valori inseribili nei campi  - no accentate e no [] () ' "
date_default_timezone_set('Europe/Rome');
setlocale(LC_ALL, 'it_IT.iso885915@euro'); //  comando specifico per la piattaforma linux wwwdisc su CentosOS 7 (locale -a su bash per verifica)
$tabella_css_color = array('#023E8A', '#D90429', '#48CAE4');
if(NULL === DIRECTORY_SEPARATOR) define('DIRECTORY_SEPARATOR', '/');  // UNIX == /  WIN == \
// FINE NON MODIFICARE

define('NOME_SERVER_ESECUZIONE', 'wwwdisc.chimica.unipd.it');  // HOSTNAME server in produzione
define('URI_SERVER_ESECUZIONE', 'https://'.NOME_SERVER_ESECUZIONE.'/approvvigionamento/index.php');
define('URI_DOPO_SERVER', 'approvvigionamento', FALSE);  // In uso su ticket.php per comporre il link di download
define('DURATA_SESSIONE_OAUT2', 60*60*48);       // in secondi - vedi variabile session.cookie_lifetime in php.ini che la determina in secondi
define('ENABLE_GOOGLE_AUTH', FALSE, FALSE);       // Bisogna escludere l'SSO lato server se abilito il modo di autenticazione su google
define('check_oauth.php', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX.apps.googleusercontent.com', TRUE);   // ID client per autenticazione su wwwdisc.chimica.unipd.it
define('LOGIN_USER_FORM', FALSE);                // Abilita o disabilita l'autenticazione tramite form classico di inserimento username e password in luogo di google oauth2
define('FORZA_PROTOCOLLO_HTTPS', TRUE, FALSE);   // Costringe il redirect ad https://
define('INVIA_EMAIL_NOTIFICA', FALSE, FALSE);    // Abilitazione invio email notifica all' utente compilazione del form
define('TIMEOUT_COMPILAZIONE_CARRELLO', 10);     // Tempo limite concesso all'utente per la compilazione del carrello - in minuti
define('TIMEOUT_CHECK_NUOVO_CARRELLO', 20);      // Ogni quanto si verifica l'inserimento di un nuovo carrello per la notifica gestita in checkcarrello.php - in secondi
define('SITO_IN_MANUTENZIONE', FALSE, FALSE);    // Accesso negato per manutenzione
define('SECONDI_AGGIUNTIVI', 86390);             // Valore necessario a coprire la giornata nello UNIX_TIMESTAMP dei rapporti

/* Setting per invio posta elettronica */
define('ENABLE_SEND_MAIL', FALSE);   // Abilita o disabilita l'invio delle email - per debug
define('EMAIL_SERVIZIO', 'approvvigionamento.chimica@unipd.it');   // Email mittente dei messaggii agli utenti compilatori
define('EMAIL_FIRMA', 'Catullo Romano - Nerone Agrippa - tel. (+39) 049 8275241 - e-mail: <A href="mailto:approvvigionamento.chimica@unipd.it">approvvigionamento.chimica@unipd.it</A>');
define('MITTENTE_SERVIZIO', 'Servizio approvvigionamento <A href="https://'.NOME_SERVER_ESECUZIONE.'/approvvigionamento">https://'.NOME_SERVER_ESECUZIONE.'/approvvigionamento</A>');
define('OGGETTO_MESSAGGIO_SERVIZIO', 'Servizio approvvigionamento ID carrello: %ID_CARRELLO% - del: %DATA_CARRELLO%');
define('SERVER_SMTP', 'smtp.unipd.it');  // Indirizzo server inoltro posta elettronica - PORTA 25 default

// Gestione upload file
DEFINE('MASSIMA_DIMENSIONE_FILE', MEGABYTES * 10);  // dimensione massima del file in bytes - in esempio 10 MB
DEFINE('QUOTA_MASSIMA', MEGABYTES * 3000);          // dimensione massima cartella di upload in bytes  - in esempio 3000MB

if($_SERVER['SERVER_NAME'] == NOME_SERVER_ESECUZIONE)
   $conn_obj = new Connessione('localhost', 'approvvigionamento', 'nomeutente_DB', 'password_DB');     // Creazione della obj connessione -- HOSTNAME - database - username - password
else
   $conn_obj = new Connessione('localhost', 'magazzino', 'nomeutente_DB', 'password_DB');     // Creazione della obj connessione -- HOSTNAME - database - username - password

$lang_accettato = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : '';
  if($lang_accettato == 'it')
       $lang_accettato = 'Immettere nel formato giorno-mese-anno (DD-MM-YYYY)';
  if($lang_accettato == 'en')
       $lang_accettato = 'Immettere nel formato mese-giorno-anno (MM-DD-YYYY)';

$email_super_admin = array('michele.furlan@unipd.it');

if(LOGIN_USER_FORM && !isset($_SESSION['GAUTH_mail']) && isset($_REQUEST['frm_email_login'])) {  // Verifico username (email) e password immessi dall'utente nel form di login
  // Potrebbe essere una richiesta di invio password
  if('FALSE' <> $_REQUEST['frm_requestpwd_login']) {   // Verra' rimesso il login utente per avvisare del risultato
       $esito_recupero_pwd = password_recovery($conn_obj, $_REQUEST['frm_email_login']);
  }
  else {
    $_SESSION['GRUPPO'] = ottieni_gruppi_utente($conn_obj, trim($_REQUEST['frm_email_login']), $_REQUEST['frm_password_login']);  // Verifica anche email e password
     if(!array_key_exists('MANCA_GRUPPO', $_SESSION['GRUPPO'])) {  // Diversamente devo reiterare l'autenticazione perche' non e' stato trovato l'utente
         $_SESSION['GAUTH_mail'] = trim($_REQUEST['frm_email_login']);
         $_SESSION['GAUTH_fullname'] = explode('@', $_SESSION['GAUTH_mail'])[0];
     }
  }
}  // Fine if ricezione email dal login

// $enable_sso_auth e' la variabile che determina se emettere o meno il form di login TRUE o FALSE
$enable_sso_auth = (($_SERVER['SERVER_NAME'] != NOME_SERVER_ESECUZIONE) OR ((ENABLE_GOOGLE_AUTH || LOGIN_USER_FORM) AND isset($_SESSION['GAUTH_mail'])) OR !(ENABLE_GOOGLE_AUTH || LOGIN_USER_FORM)) ? TRUE : FALSE;  // sono nel file check_oauth.php avviene l'impostazione della sessione
$sso_appusername = ((ENABLE_GOOGLE_AUTH || LOGIN_USER_FORM) AND isset($_SESSION['GAUTH_mail'])) ? (explode('@', $_SESSION['GAUTH_mail'])[0]) : ($enable_sso_auth ? (explode('@', $email_super_admin[0])[0]) : 'utente.prova');
// Assegnazione diritti all'utenza  - Se sono in esecuzione in localhost oppure non chiedo l'autenticazione Gmail mi ipotizzo sempre admin
$sso_appticket_cognome = isset($_SESSION['GAUTH_fullname']) ? $_SESSION['GAUTH_fullname'] : 'nome_utente_gmail_anonimo';
$sso_appticket_logout = "$.ajax({url:'./oauth2/check_oauth.php?user_logout_dott=out',async: false});signOut_google(".(!LOGIN_USER_FORM ? 'true' : 'false').')';
if(!isset($_SESSION['GRUPPO'])) {  // Evito l'interrogazione continua del gruppo di appartenenza dell'utente memorizzandolo in $_SESSION
   $sso_gruppo = (ENABLE_GOOGLE_AUTH || LOGIN_USER_FORM) ? ottieni_gruppi_utente($conn_obj, isset($_SESSION['GAUTH_mail']) ? $_SESSION['GAUTH_mail'] : $email_super_admin[0]) : array('ADMIN' => 'ADMIN'); // Ritorna l'array con i gruppi di appartenenza
   $_SESSION['GRUPPO'] = $sso_gruppo;  // E' un array con i gruppi di appartenenza dell utente
}
else
   $sso_gruppo = $_SESSION['GRUPPO'];

$app_admin = in_array('ADMIN', $sso_gruppo) ? TRUE : FALSE;   // ex $appticket_auth['admin_app']
$app_super_admin = (isset($_SESSION['GAUTH_mail']) AND in_array($_SESSION['GAUTH_mail'], $email_super_admin)) ? TRUE : FALSE;

// Devo settare un servizio di modo che sia possibile visualizzare il titolo nella tabelle e per le query successive - NON CAMBIARE L ORDINE DEL GRUPPO SOTTOSTANTE
if(!isset($_REQUEST['lista_richieste_tabella']['tipo_richiesta']) OR (isset($_REQUEST['tbl_nome']) AND 'lista_in_attesa' == $_REQUEST['tbl_nome']))   // Default sfoglio la lista in attesa - NECESSARIO per la cancellazione eventuale del record da parte dell'admin
    $_REQUEST['lista_richieste_tabella']['tipo_richiesta'] = 'lista_in_attesa';
if(!isset($_REQUEST['lista_richieste_tabella']['servizio_richiesto']))   // Set valori del default
    $_REQUEST['lista_richieste_tabella']['servizio_richiesto'] = (isset($_REQUEST['servizio_richiesto_xls']) AND 'rapporti' == $_REQUEST['servizio_richiesto_xls']) ? 'rapporti' : 'HOME';
if(!isset($_REQUEST['lista_richieste_tabella']['anno_richiesta']))
    $_REQUEST['lista_richieste_tabella']['anno_richiesta'] = isset($_REQUEST['anno_richiesta_xls']) ? $_REQUEST['anno_richiesta_xls'] :  date('Y');  // Potrebbe essere richiesta la generazione del file excel
$is_home_page = $_REQUEST['lista_richieste_tabella']['servizio_richiesto'] == 'HOME' ? TRUE : FALSE; // Per brevita nelle comunicazioni

/* Varibili per la gestione dei rapporti */
$rpt_flag = (('rapporti' == $_REQUEST['lista_richieste_tabella']['servizio_richiesto']) ? TRUE : FALSE);
$rpt = array('tabelle' => array('artdeposito', 'artvalore', 'spesadettaglio', 'spesaxgruppo', 'proddeposito'),
             'rapporti' => ($rpt_flag ? explode('_', $_REQUEST['lista_richieste_tabella']['anno_richiesta']) : array(time(),time())),  // UNIX_TIMESTAMP inizio-fine;  // Array con la data di inizio e fine di selezione dei rapporti
             'chiave_carrelloart' => 'id_carrello',
             'chiave_ordini' => 'id_ordine',
             'ordini' => ($_REQUEST['lista_richieste_tabella']['servizio_richiesto'] == 'ordini' ? TRUE : FALSE),
             'nuovocar' => ('nuovocarrello' == $_REQUEST['lista_richieste_tabella']['servizio_richiesto'] ? TRUE : FALSE),
             'filtro_utente' => ($app_admin ? '' : ' AND gruppo IN (\''.implode('\',\'', array_values($sso_gruppo)).'\')'),   // Filtro per i carrelli dove c'e' il gruppo - lo user vede solo i carrelli del suo gruppo
             'filtro_gruppo' => ($app_admin ? '' : 'gruppo IN (\''.implode('\',\'', array_values($sso_gruppo)).'\')'),  // usato in clausola where in rapporti.php
             'filtro_gruppo_utenti' => ($app_admin ? '' : 'nome IN (\''.implode('\',\'', array_values($sso_gruppo)).'\')'),  // usato in clausola where in rapporti.php
             'divieto' => array('confermacar_a', 'caricaquantita', 'emessaggio', 'eliminarerecord', 'aggiungirecord', 'associa', 'dissocia')   // Elenco dei comandi inibiti a livello user
       );

if($rpt_flag AND isset($_REQUEST['lista_richieste_tabella']['tipo_richiesta'])) {
   $rpt['rapporti'][1] = $rpt['rapporti'][1] + SECONDI_AGGIUNTIVI;   // Devo coprire l'interna giornata perche' datainizio e datafine possono avere lo stesso unixtimestamp e non va bene
      switch($_REQUEST['lista_richieste_tabella']['tipo_richiesta']) {
              case 'artvalore' :
              case 'artdeposito' : {
                 $rpt['chiave_carrelloart'] = 'id_articolo';
                 $rpt['chiave_ordini'] = $rpt['chiave_carrelloart'];
              break;
              }  // fine case 'artdeposito' e 'artvalore'
              case 'proddeposito' : {
                 $rpt['chiave_carrelloart'] = 'id_prodotto';
                 $rpt['chiave_ordini'] = "dammi_campo_articoli('ordart', 'idprod', id_articolo)";
              break;
              }  // fine case 'proddeposito'
              default : {
                 $rpt['chiave_carrelloart'] = 'id_carrello';
              }
    }  // Fine switch
}  // Fine if $rpt_flag

$rpt_where = ($rpt_flag ? ' AND (UNIX_TIMESTAMP(dataritiro) BETWEEN '.$rpt['rapporti'][0].' AND '.$rpt['rapporti'][1].')' : '');
/* Fine variabili per la gestione dei rapporti */

if($app_admin)  { // Ci sono 2 livelli di utenza
        $flag_tipo_utente_autenticato = 'admin';
}
else {
        $flag_tipo_utente_autenticato = 'user';
}

 $intestazione_tabella_nome = '';      /* Reset variabili - intestazione delle tabelle */
 $where_filtro_tabella_richiesta = '';

 if(!isset($_REQUEST['servizio_richiesto']) OR 'HOME' <> $_REQUEST['servizio_richiesto']) {   // Sono usati quando chiedo la compilazione della richiesta
   switch($_REQUEST['lista_richieste_tabella']['servizio_richiesto']) {
     case 'carrelli' : {   // ATTE - CONF - EVAS
         if(isset($_SESSION['DB_SEME'])) unset($_SESSION['DB_SEME']);
     break;
     }  // Fine case carrelli
     default: {   // In caso di richiesta malformata o intento malevolo inserisco comunque la clausola where di blocco
          $intestazione_tabella_nome_servizio = 'Richiesta malformata tipo non ammesso: '.$_REQUEST['lista_richieste_tabella']['tipo_richiesta'];
          $where_filtro_tabella_richiesta = 'email LIKE \'non_esiste_indirizzo@pippo.com\'';
     }  // Fine default case
   }  // Fine switch case
 }  // fine if "HOME"


$tabella['prod'] =  array('nometabella' => 'prodotti',      // in lavorazione - oppure completati - completati con spese  TABELLA GENERICA - WARNING: non posso caricare due tabelle con lo stesso nome
                          'figlia' => array('artprod' => 'Articoli'),
                          'chiave_padre' => ($rpt_flag ? 'id' : ''),
                          'drag_drop' => FALSE,
                          'nascondi_id' => FALSE,
                          'intestazione' => ($rpt_flag ? '' : 'Prodotti'),
                          'ordine' => array('nome' => 'ASC'),
                          'pulsanti' => array('verticale' => TRUE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => $app_admin, 'delete' => $app_admin, 'filtro' => TRUE, 'paginazione' => 25),
                          'where' => '',
                          'campi' => array('calcolato4' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' => 4, 'vert_larghezza' => 20, 'formula' => "DATE_FORMAT(datainserimento, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE),
                                           'nome' => array('tipo' => 'text', 'etichetta' => 'Nome prodotto', 'editable' => ($app_admin && !$rpt_flag), 'size_filtro' => 20, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 26, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                           'ganalitico' => array('tipo' => 'select', 'etichetta' => 'Grado analitico', 'editable' => ($app_admin && !$rpt_flag), 'size_filtro' => 10, 'vert_larghezza' => 0, "attributi" => genera_select($conn_obj, 'prodotti', 'ganalitico')),
                                           'marca' => array('tipo' => 'select', 'etichetta' => 'Marca', 'editable' => ($app_admin && !$rpt_flag), 'size_filtro' => 12, 'vert_larghezza' => 0, 'attributi' => genera_select($conn_obj, 'prodotti', 'marca')),
                                           'confezione' => array('tipo' => 'select', 'etichetta' => 'Confezione', 'editable' => ($app_admin && !$rpt_flag), 'size_filtro' => 8, 'vert_larghezza' => 0, 'attributi' => genera_select($conn_obj, 'prodotti', 'confezione')),
                                           'scheda' => array('tipo' => 'file', 'etichetta' => 'Scheda di rischio', 'editable' => ($app_admin && !$rpt_flag), 'size_filtro' => 15, 'vert_larghezza' => 20, 'attributi' => array('cartella_upload' => 'upload/schede', 'larghezza_max_miniatura' => 90, 'non_duplicabile' => FALSE)),
                                           'capacita' => array('tipo' => 'text', 'etichetta' => 'Capacit&agrave;', 'editable' => ($app_admin && !$rpt_flag), 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 6, 'maxlength' => 10, 'pattern' => '^(\d{0,4})?(\.\d{0,2})?$', 'error_pattern' => 'Immettere numero 0000.00 !')),
                                           'unita_misura' => array('tipo' => 'select', 'etichetta' => 'Unit&agrave; di misura', 'editable' => ($app_admin && !$rpt_flag), 'size_filtro' => 7, 'vert_larghezza' => 0, 'attributi' => genera_select($conn_obj, 'prodotti', 'unita_misura')),
                                           'maxordine' => array('tipo' => 'text', 'etichetta' => 'Limite carrello', 'editable' => ($app_admin && !$rpt_flag), 'size_filtro' => 4, 'vert_larghezza' => 4, 'attributi' => array('default_value' => '100', 'size' => 6, 'maxlength' => 4, 'pattern' => '^(0|[1-9]\d{0,3}|9999)$', 'error_pattern' => 'Immettere un numero intero')),
                                           'classe' => array('tipo' => 'select', 'etichetta' => 'Classe', 'editable' => ($app_admin && !$rpt_flag), 'size_filtro' => 16, 'vert_larghezza' => 0, 'attributi' => genera_select($conn_obj, 'prodotti', 'classe')),
                                           'magazz' => array('tipo' => 'select', 'etichetta' => 'Magazzino', 'editable' => ($app_admin && !$rpt_flag), 'size_filtro' => 12, 'vert_larghezza' => 0, 'attributi' => genera_select($conn_obj, 'prodotti', 'magazz')),
                                           'in_uso' => array('tipo' => 'booleano', 'etichetta' => 'In uso', 'editable' => ($app_admin && !$rpt_flag), 'size_filtro' => 10, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '1')),
                                           'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => ($app_admin && !$rpt_flag), 'size_filtro' => 22, 'vert_larghezza' => 20, 'attributi' => array('default_value' => '', 'rows' => 3, 'cols' => 30, 'maxlength' => 500))
                                     )
                          );


$tabella['artprod'] = array('nometabella' => 'articoli',      // in lavorazione - oppure completati - completati con spese  TABELLA GENERICA - WARNING: non posso caricare due tabelle con lo stesso nome
                            'figlia' => array(),
                            'chiave_padre' => ('0001' == $_REQUEST['lista_richieste_tabella']['anno_richiesta'] ? '' : 'id_prodotto'),
                            'drag_drop' => FALSE,
                            'nascondi_id' => FALSE,
                            'intestazione' => ('0001' == $_REQUEST['lista_richieste_tabella']['anno_richiesta'] ? 'Tutti gli articoli' : ''),
                            'ordine' => array('descrizione' => 'ASC'),
                            'pulsanti' => array('verticale' => FALSE, 'hidevert' => ($rpt_flag OR 'prodotti' == $_REQUEST['lista_richieste_tabella']['servizio_richiesto']), 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => ('0001' == $_REQUEST['lista_richieste_tabella']['anno_richiesta'] ? TRUE : FALSE), 'paginazione' => 30),
                            'where' => '',
                            'campi' => array('calcolato3' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' => 8, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(datainserimento, '%Y-%m-%d')", 'ricalcolo' => FALSE),
                                             'datainserimento' => array('tipo' => 'date', 'etichetta' => 'Data', 'editable' => FALSE, 'size_filtro' => 20, 'vert_larghezza' => 2, 'attributi' => array('default_value' => '', 'max' => date('Y-m-d'), 'min' => '2022-01-01', 'pattern' => '\d{4}-\d{2}-d{2}', 'error_pattern' => $lang_accettato)),
                                             'quantita' => array('tipo' => 'text', 'etichetta' => 'Q.t&agrave; disp.', 'editable' => FALSE, 'size_filtro' => 4, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 5, 'maxlength' => 6, 'pattern' => '', 'error_pattern' => '')),
                                             'calcolato34' => array('tipo' => 'calcolato', 'etichetta' => 'Stato', 'size_filtro' => 7, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('art', 'ordine', id)", 'ricalcolo' => FALSE),
                                             'cod_barre' => array('tipo' => 'text', 'etichetta' => 'Codice a barre', 'editable' => FALSE, 'size_filtro' => 10, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 14, 'maxlength' => 250, 'pattern' => '', 'error_pattern' => '')),
                                             'in_uso' => array('tipo' => 'booleano', 'etichetta' => 'In uso', 'editable' => ($app_admin && !$rpt_flag && !$rpt['nuovocar']), 'size_filtro' => 10, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '1')),
                                             'descrizione' => array('tipo' => 'text', 'etichetta' => 'Descrizione', 'editable' => FALSE, 'size_filtro' => 34, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 40, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => '')),
                                             'calcolato78' => array('tipo' => 'calcolato', 'etichetta' => 'Fornitore', 'size_filtro' => 8, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'fornitore', id)", 'ricalcolo' => FALSE),
                                             'codice' => array('tipo' => 'text', 'etichetta' => 'Codice for.', 'editable' => FALSE, 'size_filtro' => 8, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 10, 'maxlength' => 50, 'pattern' => '', 'error_pattern' => '')),
                                             'prezzo' => array('tipo' => 'text', 'etichetta' => 'Prezzo&nbsp;&euro;', 'editable' => FALSE, 'size_filtro' => 4, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 6, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => '')),
                                             'iva' => array('tipo' => 'text', 'etichetta' => 'Iva&nbsp;%', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 6, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => '')),
                                             'foto' => array('tipo' => 'file', 'etichetta' => 'Immagine', 'editable' => FALSE, 'size_filtro' => 15, 'vert_larghezza' => 0, 'attributi' => array('cartella_upload' => 'upload/foto', 'larghezza_max_miniatura' => 90, 'non_duplicabile' => FALSE)),
                                             'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => ($app_admin && !$rpt_flag && !$rpt['nuovocar']), 'size_filtro' => 34, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 2, 'cols' => 40, 'maxlength' => 500))
                                        )
                       );

$tabella['forni'] =  array('nometabella' => 'fornitori',      // Tabella fornitori
                           'figlia' => array('prev' => 'Preventivi'),
                           'chiave_padre' => $rpt['ordini'] ? 'id' : '',
                           'drag_drop' => FALSE,
                           'nascondi_id' => FALSE,
                           'intestazione' => $rpt['ordini'] ? '' : 'Fornitori e inserimento preventivi',
                           'ordine' => array('denominazione' => 'ASC'),
                           'pulsanti' => array('verticale' => TRUE, 'hidevert' => TRUE, 'chiave_figlia' => ($rpt['ordini'] ? 'id_fornitore' : ''), 'add' => (!$rpt['ordini'] && $app_admin), 'delete' => $app_admin, 'filtro' => TRUE, 'paginazione' => 0),
                           'where' => '',
                           'campi' => array('denominazione' => array('tipo' => 'text', 'etichetta' => 'Fornitore', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 34, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 40, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => '')),
                                            'contatto' => array('tipo' => 'text', 'etichetta' => 'Contatto', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 34, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 40, 'maxlength' => 150, 'pattern' => '', 'error_pattern' => '')),
                                            'indirizzo' => array('tipo' => 'text', 'etichetta' => 'Indirizzo', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 20, 'vert_larghezza' => 20, 'attributi' => array('default_value' => '', 'size' => 40, 'maxlength' => 150, 'pattern' => '', 'error_pattern' => '')),
                                            'cap' => array('tipo' => 'text', 'etichetta' => 'CAP', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 4, 'vert_larghezza' => 20, 'attributi' => array('default_value' => '', 'size' => 10, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')),
                                            'citta' => array('tipo' => 'text', 'etichetta' => 'Citt&agrave;', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 6, 'vert_larghezza' => 20, 'attributi' => array('default_value' => '', 'size' => 10, 'maxlength' => 50, 'pattern' => '^.{0,50}$', 'error_pattern' => '')),
                                            'telefono' => array('tipo' => 'text', 'etichetta' => 'Telefono fisso', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 10, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 16, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                            'cellulare' => array('tipo' => 'text', 'etichetta' => 'Cellulare', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 10, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 16, 'maxlength' => 100, 'pattern' => '^.{0,50}$', 'error_pattern' => '')),
                                            'fax' => array('tipo' => 'text', 'etichetta' => 'Fax', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 9, 'vert_larghezza' => 12, 'attributi' => array('default_value' => '', 'size' => 16, 'maxlength' => 100, 'pattern' => '^.{0,100}$', 'error_pattern' => '')),
                                            'email' => array('tipo' => 'text', 'etichetta' => 'Indirizzo e-mail', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 25, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 30, 'maxlength' => 150, 'pattern' => EMAIL_PATTERN_REGX, 'error_pattern' => 'Immettere un indirizzo email valido !')),
                                            'indirizzo_web' => array('tipo' => 'text', 'etichetta' => 'Indirizzo web', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 20, 'vert_larghezza' => 20, 'attributi' => array('default_value' => '', 'size' => 50, 'maxlength' => 150, 'pattern' => URL_PATTERN_REGEX, 'error_pattern' => 'Inserire un indirizzo valido !')),
                                            'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 20, 'vert_larghezza' => 20, 'attributi' => array('default_value' => '', 'rows' => 2, 'cols' => 42, 'maxlength' => 500))
                                       )
                          );


$tabella['prev'] =  array('nometabella' => 'preventivi',      // Tabella preventivi
                          'figlia' => $rpt['ordini'] ? array() : array('art' => 'Articoli'),
                          'chiave_padre' => 'id_fornitore',
                          'drag_drop' => FALSE,
                          'nascondi_id' => FALSE,
                          'intestazione' => '',
                          'ordine' => array('datascadenza' => 'DESC'),
                          'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => (!$rpt['ordini'] && $app_admin), 'delete' => (!$rpt['ordini'] && $app_admin), 'filtro' => TRUE, 'paginazione' => 0),
                          'where' => '',
                          'campi' => array('calcolato17' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' => 10, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(datainserimento, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE),
                                           'datapreventivo' => array('tipo' => 'date', 'etichetta' => 'Data&nbsp;preventivo', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 9, 'vert_larghezza' => 0, 'attributi' => array('default_value' => date('Y-m-d'), 'max' => date('Y-m-d', strtotime('+1 year')), 'min' => '2022-01-01', 'pattern' => '\d{4}-\d{2}-d{2}', 'error_pattern' => $lang_accettato)),
                                           'numero' => array('tipo' => 'text', 'etichetta' => 'Numero', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 21, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 26, 'maxlength' => 50, 'pattern' => '^.{0,50}$', 'error_pattern' => '')),
                                           'datascadenza' => array('tipo' => 'date', 'etichetta' => 'Data scadenza', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 9, 'vert_larghezza' => 0, 'attributi' => array('default_value' => date('Y-m-d'), 'max' => '2035-01-01', 'min' => '2022-01-01', 'pattern' => '\d{4}-\d{2}-d{2}', 'error_pattern' => $lang_accettato)),
                                           'minimo_ordine' => array('tipo' => 'text', 'etichetta' => 'Ordine minimo', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 3, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 8, 'maxlength' => 4, 'pattern' => '^\d{0,4}$', 'error_pattern' => 'Inserire massimo 4 cifre intere !')),
                                           'calcolato73' => array('tipo' => 'calcolato', 'etichetta' => 'PDF', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => "CONCAT('<INPUT id=\"stampaprev_', id, '\" type=\"button\" class=\"emaildec center_elemento_imma\" value=\"&#x1F5B6;\" onClick=\"stampa_prev(',id,');\" />')", 'ricalcolo' => FALSE),
                                           'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => (!$rpt['ordini'] && $app_admin), 'size_filtro' => 38, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 1, 'cols' => 42, 'maxlength' => 500))
                                     )
                          );


$tabella['art'] =  array('nometabella' => 'articoli',      // in lavorazione - oppure completati - completati con spese  TABELLA GENERICA - WARNING: non posso caricare due tabelle con lo stesso nome
                         'figlia' => array('prodo' => 'Associa prodotto'),
                         'chiave_padre' => 'id_preventivo',
                         'drag_drop' => FALSE,
                         'nascondi_id' => FALSE,
                         'intestazione' => '',
                         'ordine' => array('descrizione' => 'ASC'),
                         'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => $app_admin, 'delete' => $app_admin, 'filtro' => FALSE, 'paginazione' => 26),
                         'where' => '',
                         'campi' => array('calcolato3' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' => 4, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(datainserimento, '%Y-%m-%d')", 'ricalcolo' => FALSE),
                                          'datainserimento' => array('tipo' => 'date', 'etichetta' => 'Data', 'editable' => $app_admin, 'size_filtro' => 20, 'vert_larghezza' => 2, 'attributi' => array('default_value' => '', 'max' => date('Y-m-d'), 'min' => '2022-01-01', 'pattern' => '\d{4}-\d{2}-d{2}', 'error_pattern' => $lang_accettato)),
                                          'descrizione' => array('tipo' => 'text', 'etichetta' => 'Descrizione&nbsp;articolo', 'editable' => $app_admin, 'size_filtro' => 34, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 40, 'maxlength' => 255, 'pattern' => '^.{0,255}$', 'error_pattern' => '')),
                                          'calcolato6' => array('tipo' => 'calcolato', 'etichetta' => 'Nome prodotto', 'size_filtro' => 4, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('art', 'nome', id)", 'ricalcolo' => FALSE),
                                          'in_uso' => array('tipo' => 'booleano', 'etichetta' => 'In uso', 'editable' => $app_admin, 'size_filtro' => 10, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '1')),
                                          'cod_barre' => array('tipo' => 'text', 'etichetta' => 'Codice a barre', 'editable' => $app_admin, 'size_filtro' => 10, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 12, 'maxlength' => 10, 'pattern' => '\d{0,10}.', 'error_pattern' => 'Imttere un numero intero non negativo 10 cifre max.!')),
                                          'codice' => array('tipo' => 'text', 'etichetta' => 'Codice for.', 'editable' => $app_admin, 'size_filtro' => 8, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 10, 'maxlength' => 50, 'pattern' => '^.{0,50}$', 'error_pattern' => '')),
                                          'prezzo' => array('tipo' => 'text', 'etichetta' => 'Prezzo&nbsp;&euro;', 'editable' => $app_admin, 'size_filtro' => 4, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 6, 'maxlength' => 10, 'pattern' => '^(\d{1,8}(\.\d{1,2})?)?$', 'error_pattern' => 'Immettere un numero con massimo due decimali !')),
                                          'iva' => array('tipo' => 'select', 'etichetta' => 'Iva&nbsp;%', 'editable' => $app_admin, 'size_filtro' => 10, 'vert_larghezza' => 0, "attributi" => array('default_vuoto' => '0', '19' => '19', '20' => '21', '22' => '22', '23' => '23')),
                                          'sconto' => array('tipo' => 'text', 'etichetta' => 'Sconto&nbsp;%', 'editable' => $app_admin, 'size_filtro' => 4, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 5, 'maxlength' => 6, 'pattern' => '^(\d{1,3}(\.\d{1,2})?)?$', 'error_pattern' => 'Immettere un numero con massimo due decimali e tre interi !')),
                                          'foto' => array('tipo' => 'file', 'etichetta' => 'Immagine', 'editable' => $app_admin, 'size_filtro' => 15, 'vert_larghezza' => 0, 'attributi' => array('cartella_upload' => 'upload/foto', 'larghezza_max_miniatura' => 90, 'non_duplicabile' => FALSE)),
                                          'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => $app_admin, 'size_filtro' => 22, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 2, 'cols' => 30, 'maxlength' => 500))
                                    )
                         );


$tabella['prodo'] =  array('nometabella' => 'prodotti',    // Tabella con la lista dei prodotti da accociare ad uno specifico articolo
                          'figlia' => array(),
                          'chiave_padre' => '',
                          'drag_drop' => FALSE,
                          'nascondi_id' => FALSE,
                          'intestazione' => '',
                          'ordine' => array('nome' => 'ASC'),
                          'pulsanti' => array('verticale' => FALSE, 'hidevert' => FALSE, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => TRUE, 'paginazione' => 30),
                          'where' => '',
                          'campi' => array('calcolato5' => array('tipo' => 'calcolato', 'etichetta' => 'Associa prodotto', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => 'check_box_prodotti(%valore%, id)', 'ricalcolo' => FALSE),
                                           'nome' => array('tipo' => 'text', 'etichetta' => 'Nome', 'editable' => FALSE, 'size_filtro' => 22, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 30, 'maxlength' => 100, 'pattern' => '^.{0,100}$', 'error_pattern' => '')),
                                           'ganalitico' => array('tipo' => 'select', 'etichetta' => 'Grado analitico', 'editable' => FALSE, 'size_filtro' => 10, 'vert_larghezza' => 0, "attributi" => genera_select($conn_obj, 'prodotti', 'ganalitico')),
                                           'marca' => array('tipo' => 'select', 'etichetta' => 'Marca', 'editable' => FALSE, 'size_filtro' => 10, 'vert_larghezza' => 0, 'attributi' => genera_select($conn_obj, 'prodotti', 'marca')),
                                           'confezione' => array('tipo' => 'select', 'etichetta' => 'Confezione', 'editable' => FALSE, 'size_filtro' => 10, 'vert_larghezza' => 0, 'attributi' => genera_select($conn_obj, 'prodotti', 'confezione')),
                                           'capacita' => array('tipo' => 'text', 'etichetta' => 'Capacit&agrave;', 'editable' => FALSE, 'size_filtro' => 4, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 10, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')),
                                           'in_uso' => array('tipo' => 'booleano', 'etichetta' => 'In uso', 'editable' => FALSE, 'size_filtro' => 0, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '1'))
                                     )
                      );


$tabella['ordini'] =  array('nometabella' => 'ordini',       // Tabella ordini in carico con articoli non ancora pervenuti a magazzino
                           'figlia' => ($rpt_flag ? array() : array('ordart' => 'Articoli Q.t&agrave;', 'arto' => 'Articoli SC')),
                           'chiave_padre' => ($rpt_flag ? 'id' : ''),  // !! da usare in combinazione con chiave figlia - serve ad evitare che ad esempio nel caso specifico: siano visualizzati solo l' ordini che corrisponde all articolo elencato in ordart
                           'drag_drop' => FALSE,
                           'nascondi_id' => FALSE,
                           'intestazione' => (!$rpt_flag ? 'Ordini e carico in lavorazione' : ''),
                           'ordine' => array('datainserimento' => 'DESC'),
                           'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => ($rpt_flag ? 'id_ordine' : ''), 'add' => (!$rpt_flag AND $app_admin), 'delete' => ($app_admin AND !$rpt_flag), 'filtro' => TRUE, 'paginazione' => 20),
                           'where' => "filtro_ordini(id, 'ATTI')",
                           'campi' => array('calcolato17' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' => 10, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(datainserimento, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE),
                                            'dataordine' => array('tipo' => 'date', 'etichetta' => 'Data&nbsp;ordine', 'editable' => (!$rpt_flag AND $app_admin), 'size_filtro' => 10, 'vert_larghezza' => 0, 'attributi' => array('default_value' => date('Y-m-d'), 'max' => date('Y-m-d', strtotime('+1 year')), 'min' => '2024-01-01', 'pattern' => '\d{4}-\d{2}-d{2}', 'error_pattern' => $lang_accettato)),
                                            'nordine' => array('tipo' => 'text', 'etichetta' => 'Numero ordine', 'editable' => (!$rpt_flag AND $app_admin), 'size_filtro' => 17, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 22, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                            'calcolato71' => array('tipo' => 'calcolato', 'etichetta' => 'PDF', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => "CONCAT('<INPUT id=\"stampao_', id, '\" type=\"button\" class=\"emaildec center_elemento_imma\" value=\"&#x1F5B6;\" onClick=\"stampa_ordine(',id,');\" />')", 'ricalcolo' => FALSE),
                                            'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => (!$rpt_flag AND $app_admin), 'size_filtro' => 36, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 1, 'cols' => 40, 'maxlength' => 500))
                                      )
                      );


$tabella['ordart'] = array('nometabella' => 'ordineart',     // Tabella degli articoli non ancora pervenuti a magazzino a seguito dell'ordine
                           'figlia' =>  ($rpt_flag ? array('ordini' => 'Ordine') : ($rpt['ordini'] ? array('forni' => 'Fornit.'): array())),
                           'chiave_padre' => $rpt['chiave_ordini'],
                           'drag_drop' => FALSE,
                           'nascondi_id' => FALSE,
                           'intestazione' => '',
                           'ordine' => array('calcolato62' => 'ASC'),
                           'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => FALSE, 'paginazione' => 50),
                           'where' => 'qarrivo = 0 AND quantita >= 0',
                           'campi' => array('calcolato62' => array('tipo' => 'calcolato', 'etichetta' => 'Descrizione articolo', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('ordart', 'descrizione', id_articolo)", 'ricalcolo' => FALSE),
                                            'calcolato48' => array('tipo' => 'calcolato', 'etichetta' => 'Descrizione prodotto', 'size_filtro' => 20, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('ordart', 'descprodo', id_articolo)", 'ricalcolo' => FALSE),
                                            'calcolato9' => array('tipo' => 'calcolato', 'etichetta' => 'Q.t&agrave; prelevabile', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('ordart', 'quantita', id_articolo)", 'ricalcolo' => FALSE),
                                            'calcolato23' => array('tipo' => 'calcolato', 'etichetta' => 'Qt.&agrave; in giacenza', 'size_filtro' => 8, 'vert_larghezza' => 0, 'formula' => 'somma_giacenza_articoli(id_articolo)', 'ricalcolo' => FALSE),
                                            'calcolato18' => array('tipo' => 'calcolato', 'etichetta' => 'Prezzo&nbsp;&euro;', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('ordart', 'prezzo', id)", 'ricalcolo' => FALSE),
                                            'quantita' => array('tipo' => 'text', 'etichetta' => 'Q.t&agrave; ordinata', 'editable' => (!$rpt_flag && $app_admin), 'size_filtro' => 3, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 4, 'maxlength' => 5, 'pattern' => '[0-9]+', 'error_pattern' => 'Immettere un numero intero')),
                                            'calcolato19' => array('tipo' => 'calcolato', 'etichetta' => 'Q.t&agrave; caricata', 'size_filtro' => 3, 'vert_larghezza' => 0, 'formula' => ($rpt_flag ? 'qarrivo' : "CONCAT('<INPUT class=\"blue-input\" size=\"4\" maxlength=\"5\" id=\"QT19_', id, '\" type=\"text\" value=\"', qarrivo, '\" onchange=\"carica_quantita(', id,');\" pattern=\"^[0-9]{1,5}$\" error_pattern=\"Immettere un numero intero da 0 a 99999\" />')"), 'ricalcolo' => FALSE),
                                            'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => (!$rpt_flag AND $app_admin), 'size_filtro' => 22, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 1, 'cols' => 40, 'maxlength' => 500))
                                      )
                     );


$tabella['ordini_e'] = array('nometabella' => 'ordini',      // Tabella ordini degli articoli caricati a magazzino
                             'figlia' => ($rpt_flag ? array() : array('ordart_e' => 'Articoli Q.t&agrave;')),
                             'chiave_padre' => ($rpt_flag ? 'id' : ''),
                             'drag_drop' => FALSE,
                             'nascondi_id' => FALSE,
                             'intestazione' => (!$rpt_flag ? 'Ordini e carico evasi' : ''),
                             'ordine' => array('datainserimento' => 'DESC'),
                             'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => ($rpt_flag ? 'id_ordine' : ''), 'add' => FALSE, 'delete' => FALSE, 'filtro' => TRUE, 'paginazione' => 20),
                             'where' => "filtro_ordini(id, 'EVAS')",   // filtro_ordini(id, 'EVAS')
                             'campi' => array('calcolato17' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' => 10, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(datainserimento, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE),
                                              'nordine' => array('tipo' => 'text', 'etichetta' => 'Numero ordine', 'editable' => FALSE, 'size_filtro' => 16, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 22, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                              'calcolato72' => array('tipo' => 'calcolato', 'etichetta' => 'PDF', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => "CONCAT('<INPUT id=\"stampaoe_', id, '\" type=\"button\" class=\"emaildec center_elemento_imma\" value=\"&#x1F5B6;\" onClick=\"stampa_ordine(',id,');\" />')", 'ricalcolo' => FALSE),
                                              'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => (!$rpt_flag AND $app_admin), 'size_filtro' => 40, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 1, 'cols' => 40, 'maxlength' => 500))
                                        )
                       );


$tabella['ordart_e'] =  array('nometabella' => 'ordineart',     // Tabella degli articoli caricati a magazzino a seguito dell' evasione dell ordine
                              'figlia' => ($rpt_flag ? array('ordini' => 'Ordine') : ($rpt['ordini'] ? array('forni' => 'Fornit.'): array())),
                              'chiave_padre' => $rpt['chiave_ordini'],
                              'drag_drop' => FALSE,
                              'nascondi_id' => FALSE,
                              'intestazione' => '',
                              'ordine' => array('calcolato61' => 'ASC'),
                              'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => FALSE, 'paginazione' => 50),
                              'where' => 'qarrivo > 0',
                              'campi' => array('calcolato33' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' => 10, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(datainserimento, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE),
                                               'calcolato61' => array('tipo' => 'calcolato', 'etichetta' => 'Descrizione articolo', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('ordart', 'descrizione', id_articolo)", 'ricalcolo' => FALSE),
                                               'calcolato49' => array('tipo' => 'calcolato', 'etichetta' => 'Descrizione prodotto', 'size_filtro' => 20, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('ordart', 'descprodo', id)", 'ricalcolo' => FALSE),
                                               'calcolato65' => array('tipo' => 'calcolato', 'etichetta' => 'Q.t&agrave; prelevabile', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('ordart', 'quantita', id_articolo)", 'ricalcolo' => FALSE),
                                               'calcolato18' => array('tipo' => 'calcolato', 'etichetta' => 'Prezzo&nbsp;&euro;', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('ordart', 'prezzo', id)", 'ricalcolo' => FALSE),
                                               'quantita' => array('tipo' => 'text', 'etichetta' => 'Quantit&agrave; ordinata', 'editable' => FALSE, 'size_filtro' => 3, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 4, 'maxlength' => 5, 'pattern' => '[0-9]+', 'error_pattern' => 'Immettere un numero intero')),
                                               'qarrivo' => array('tipo' => 'text', 'etichetta' => 'Q.t&agrave; caricata', 'editable' => FALSE, 'size_filtro' => 3, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 4, 'maxlength' => 5, 'pattern' => '[0-9]+', 'error_pattern' => 'Immettere un numero intero')),
                                               'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => (!$rpt_flag AND $app_admin), 'size_filtro' => 22, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 1, 'cols' => 40, 'maxlength' => 500))
                                       )
                        );

if($rpt_flag)  {  // Aggiunge la chiave esterna id_prodotto nelle tabelle degli ordini - serve nel generare il rapporto
    $id_carrello_nuovo_campo = array('id_ordine' => array('tipo' => 'text', 'etichetta' => 'ID&nbsp;ordine', 'editable' => FALSE, 'size_filtro' => 3, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 5, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')));
    $tabella['ordart']['campi'] = $id_carrello_nuovo_campo + $tabella['ordart']['campi'];
    $tabella['ordart_e']['campi'] = $id_carrello_nuovo_campo + $tabella['ordart_e']['campi'];
}

if($rpt['ordini']) { // Aggiungo la chiave esterna id_fornitore nelle tabelle degli ordini
    $id_carrello_nuovo_campo = array('id_fornitore' => array('tipo' => 'calcolato', 'etichetta' => 'ID fornitore', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('ordart','idforn',id_articolo)", 'ricalcolo' => FALSE));
    $tabella['ordart']['campi'] = $id_carrello_nuovo_campo + $tabella['ordart']['campi'];
    $tabella['ordart_e']['campi'] = $id_carrello_nuovo_campo + $tabella['ordart_e']['campi'];
}

$tabella['arto'] =  array('nometabella' => 'articoli',    // Tabella di scelta degli articoli per fare l'ordine
                          'figlia' => array(),
                          'chiave_padre' => '',
                          'drag_drop' => FALSE,
                          'nascondi_id' => FALSE,
                          'intestazione' => '',
                          'ordine' => array('descrizione' => 'ASC'),
                          'pulsanti' => array('verticale' => TRUE, 'hidevert' => FALSE, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => TRUE, 'paginazione' => 30),
                          'where' => 'id_preventivo IN(SELECT id FROM preventivi WHERE preventivi.datascadenza > NOW())',   // Non si possono elencare articoli senza preventivo o con preventivo scaduto
                          'campi' => array('calcolato7' => array('tipo' => 'calcolato', 'etichetta' => 'Aggiungi articolo', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => 'genera_checkbox_articoli(%valore%, id)', 'ricalcolo' => FALSE),
                                           'quantita' => array('tipo' => 'text', 'etichetta' => 'Qt.&agrave; prelevabile', 'editable' => FALSE, 'size_filtro' => 8, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 10, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                           'calcolato22' => array('tipo' => 'calcolato', 'etichetta' => 'Qt.&agrave; in giacenza', 'size_filtro' => 8, 'vert_larghezza' => 0, 'formula' => 'somma_giacenza_articoli(id)', 'ricalcolo' => FALSE),
                                           'descrizione' => array('tipo' => 'text', 'etichetta' => 'Descrizione articolo', 'editable' => FALSE, 'size_filtro' => 22, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 30, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                           'calcolato26' => array('tipo' => 'calcolato', 'etichetta' => 'Descrizione prodotto', 'size_filtro' => 20, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('ordart', 'descprodo', id)", 'ricalcolo' => FALSE),
                                           'calcolato70' => array('tipo' => 'calcolato', 'etichetta' => 'Giacenza netta prod.', 'size_filtro' => 14, 'vert_larghezza' => 0, 'formula' => "giacenza_articoli(id_prodotto, 'NEG')", 'ricalcolo' => FALSE),
                                           'calcolato58' => array('tipo' => 'calcolato', 'etichetta' => 'Fornitore', 'size_filtro' => 18, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'fornitore', id)", 'ricalcolo' => FALSE),
                                           'prezzo' => array('tipo' => 'text', 'etichetta' => 'Prezzo&nbsp;&euro;', 'editable' => FALSE, 'size_filtro' => 4, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 8, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')),
                                           'foto' => array('tipo' => 'file', 'etichetta' => 'Immagine', 'editable' => FALSE, 'size_filtro' => 15, 'vert_larghezza' => 10, 'attributi' => array('cartella_upload' => 'upload/foto', 'larghezza_max_miniatura' => 90, 'non_duplicabile' => FALSE)),
                                           'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => FALSE, 'size_filtro' => 28, 'vert_larghezza' => 10, 'attributi' => array('default_value' => '', 'rows' => 2, 'cols' => 40, 'maxlength' => 500))
                                     )
                     );


$tabella['carrellia'] = array('nometabella' => 'carrelli',      // Tabella con i carrelli in attesa di conferma e ovviamente non ritirati
                              'figlia' => $rpt_flag ? array() : ($app_admin ? array('carartatt' => 'Articoli', 'artcarra' => 'Mod. art.', 'comunicazioni' => 'Mess.') : array('carartatt' => 'Articoli', 'comunicazioni' => 'Mess.')),
                              'chiave_padre' => ($rpt_flag ? 'id' : ''),
                              'drag_drop' => FALSE,
                              'nascondi_id' => FALSE,
                              'intestazione' => !$rpt_flag ? 'Carrelli in attesa di conferma' : '',
                              'ordine' => array('id' => 'DESC'),
                              'pulsanti' => array('verticale' => FALSE, 'hidevert' => ($rpt_flag ? TRUE : FALSE), 'chiave_figlia' => ($rpt_flag ? 'id_carrello' : ''), 'add' => FALSE, 'delete' => (!$rpt_flag AND $app_admin), 'filtro' => TRUE, 'paginazione' => 20),
                              'where' => "filtro_carrelli('ATTE',id)".$rpt['filtro_utente'],
                              'campi' => array('calcolato11' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' => 11, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(datainserimento, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE),
                                               'username' => array('tipo' => 'text', 'etichetta' => 'Utente', 'editable' => FALSE, 'size_filtro' => 16, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 20, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                               'gruppo' => array('tipo' => 'select', 'etichetta' => 'Gruppo', 'editable' => FALSE, 'size_filtro' => 10, 'vert_larghezza' => 0, "attributi" => array_merge(array('default_vuoto' => '--------'), $sso_gruppo)),
                                               'calcolato44' => array('tipo' => 'calcolato', 'etichetta' => 'Tot.+iva-sc.&nbsp;&euro;', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'spesa', id)", 'ricalcolo' => FALSE),
                                               'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => (!$rpt_flag AND $app_admin), 'size_filtro' => 36, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 1, 'cols' => 40, 'maxlength' => 500))
                                         )
                        );

if(!$rpt_flag) {  // Aggiunta campi se non in rapporti o admin
     $tabella['carrellia']['campi'] = array_insert_after('gruppo', $tabella['carrellia']['campi'], 'calcolato25', array('tipo' => 'calcolato', 'etichetta' => 'Data e ora ritiro', 'size_filtro' => 13, 'vert_larghezza' => 0, 'formula' => "CONCAT('<INPUT id=\"calslot', id, '\" type=\"button\" class=\"pulsante_file center_elemento_imma\" value=\"', dataritiro, '\" onClick=\"apri_calendario(', id,');\" />')", 'ricalcolo' => FALSE));
     if($app_admin) {
         $tabella['carrellia']['campi'] = array_insert_after('calcolato11', $tabella['carrellia']['campi'], 'calcolato35', array('tipo' => 'calcolato', 'etichetta' => 'Conferma', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => "CONCAT('<INPUT id=\"CFA_',id,'\" class=\"blue-input\" type=\"checkbox\" value=\"0\" onchange=\"conferma_articoli_caratt(this,',id,');\" />')", 'ricalcolo' => FALSE));
         $tabella['carrellia']['campi'] = array_insert_after('calcolato44', $tabella['carrellia']['campi'], 'calcolato50' , array('tipo' => 'calcolato', 'etichetta' => 'Email', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => "CONCAT('<INPUT id=\"mail_', id, '\" type=\"button\" class=\"emaildec center_elemento_imma\" value=\"&#x1F586;\" onClick=\"gestione_messaggio(',id,',\'APRI\');\" />')", 'ricalcolo' => FALSE));
     }
     $tabella['carrellia']['campi'] = array_insert_after(($app_admin ? 'calcolato50' : 'calcolato25'), $tabella['carrellia']['campi'], 'calcolato54' , array('tipo' => 'calcolato', 'etichetta' => 'PDF', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => "CONCAT('<INPUT id=\"stampa_', id, '\" type=\"button\" class=\"emaildec center_elemento_imma\" value=\"&#x1F5B6;\" onClick=\"stampa_carrello(',id,');\" />')", 'ricalcolo' => FALSE));
}

$tabella['artcarra'] = array('nometabella' => 'articoli',      // Visualizzazione degli articoli per la motifica dei carrelli in attesa di conferma
                            'figlia' => array(),
                            'chiave_padre' => '',
                            'drag_drop' => FALSE,
                            'nascondi_id' => FALSE,
                            'intestazione' => '',
                            'ordine' => array('descrizione' => 'ASC'),
                            'pulsanti' => array('verticale' => FALSE, 'hidevert' => FALSE, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => TRUE, 'paginazione' => 30),
                            'where' => '(in_uso IS TRUE) AND id_prodotto > 0 AND (quantita >0 OR id_preventivo IN (SELECT id FROM preventivi WHERE datascadenza > DATE(NOW())))',
                            'campi' => array('calcolato32' => array('tipo' => 'calcolato', 'etichetta' => 'Q.t&agrave;', 'size_filtro' => 4, 'vert_larghezza' => 0, 'formula' => "mod_articoli_carrello(id, %valore%, quantita, 'DIS')", 'ricalcolo' => FALSE),
                                             'calcolato3' => array('tipo' => 'calcolato', 'etichetta' => 'Stato', 'size_filtro' => 8, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('art', 'ordine', id)", 'ricalcolo' => FALSE),
                                             'datainserimento' => array('tipo' => 'date', 'etichetta' => 'Data', 'editable' => FALSE, 'size_filtro' => 20, 'vert_larghezza' => 2, 'attributi' => array('default_value' => '', 'max' => date('Y-m-d'), 'min' => '2022-01-01', 'pattern' => '\d{4}-\d{2}-d{2}', 'error_pattern' => $lang_accettato)),
                                             'quantita' => array('tipo' => 'text', 'etichetta' => 'Q.t&agrave; netta', 'editable' => FALSE, 'size_filtro' => 4, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 5, 'maxlength' => 6, 'pattern' => '', 'error_pattern' => '')),
                                             'cod_barre' => array('tipo' => 'text', 'etichetta' => 'Codice a barre', 'editable' => FALSE, 'size_filtro' => 12, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 14, 'maxlength' => 250, 'pattern' => '', 'error_pattern' => '')),
                                             'descrizione' => array('tipo' => 'text', 'etichetta' => 'Descrizione articolo', 'editable' => FALSE, 'size_filtro' => 34, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 40, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => '')),
                                             'codice' => array('tipo' => 'text', 'etichetta' => 'Codice for.', 'editable' => FALSE, 'size_filtro' => 8, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 10, 'maxlength' => 50, 'pattern' => '', 'error_pattern' => '')),
                                             'prezzo' => array('tipo' => 'text', 'etichetta' => 'Prezzo&nbsp;&euro;', 'editable' => FALSE, 'size_filtro' => 4, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 6, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => '')),
                                             'foto' => array('tipo' => 'file', 'etichetta' => 'Immagine', 'editable' => FALSE, 'size_filtro' => 15, 'vert_larghezza' => 0, 'attributi' => array('cartella_upload' => 'upload/foto', 'larghezza_max_miniatura' => 90, 'non_duplicabile' => FALSE)),
                                             'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => FALSE, 'size_filtro' => 22, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 2, 'cols' => 40, 'maxlength' => 500))
                                        )
                       );


$tabella['carrellic']  = array('nometabella' => 'carrelli',      // Tabella carrelli confermati e non ritirati
                              'figlia' => $rpt_flag ? array() : array('carartconf' => 'Articoli', 'comunicazioni' => 'Mess.'),
                              'chiave_padre' => ($rpt_flag ? 'id' : ''),
                              'drag_drop' => FALSE,
                              'nascondi_id' => FALSE,
                              'intestazione' => !$rpt_flag ? 'Carrelli confermati' : '',
                              'ordine' => array('id' => 'DESC'),
                              'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => ($rpt_flag ? 'id_carrello' : ''), 'add' => FALSE, 'delete' => (!$rpt_flag AND $app_admin), 'filtro' => TRUE, 'paginazione' => 20),
                              'where' =>  "filtro_carrelli('CONF',id)".$rpt['filtro_utente'],
                              'campi' => array('calcolato11' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' => 9, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(datainserimento, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE),
                                               'username' => array('tipo' => 'text', 'etichetta' => 'Utente', 'editable' => FALSE, 'size_filtro' => 16, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 20, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                               'gruppo' => array('tipo' => 'select', 'etichetta' => 'Gruppo', 'editable' => FALSE, 'size_filtro' => 10, 'vert_larghezza' => 0, "attributi" => array_merge(array('default_vuoto' => '--------'), $sso_gruppo)),
                                               'calcolato46' => array('tipo' => 'calcolato', 'etichetta' => 'Tot.+iva-sc.&nbsp;&euro;', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'spesa', id)", 'ricalcolo' => FALSE),
                                               'calcolato55' => array('tipo' => 'calcolato', 'etichetta' => 'PDF', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => "CONCAT('<INPUT id=\"stampac_', id, '\" type=\"button\" class=\"emaildec center_elemento_imma\" value=\"&#x1F5B6;\" onClick=\"stampa_carrello(',id,');\" />')", 'ricalcolo' => FALSE),
                                               'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => (!$rpt_flag AND $app_admin), 'size_filtro' => 36, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 1, 'cols' => 40, 'maxlength' => 500))
                                         )
                        );

if(!$rpt_flag AND $app_admin) {  // Inserimento invio messaggio posta nei carrelli confermati
     $tabella['carrellic']['campi'] = array_insert_after('calcolato46', $tabella['carrellic']['campi'], 'calcolato51', $tabella['carrellia']['campi']['calcolato50']);  // Pulsante stampa carrello
}

$tabella['carrellie']  = array('nometabella' => 'carrelli',      // Tabella carrelli confermati e ritirati
                               'figlia' => array('carart' => 'Articoli', 'comunicazioni' => 'Mess.'),
                               'chiave_padre' => ($rpt_flag ? 'id' : ''),
                               'drag_drop' => FALSE,
                               'nascondi_id' => FALSE,
                               'intestazione' => !$rpt_flag ? 'Carrelli ritirati' : '',
                               'ordine' => array('id' => 'DESC'),
                               'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => ($rpt_flag ? 'id_carrello' : ''), 'add' => FALSE, 'delete' => FALSE, 'filtro' => TRUE, 'paginazione' => 20),
                               'where' => "filtro_carrelli('EVAS',id)".$rpt['filtro_utente'],
                               'campi' => array('calcolato11' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' =>  11, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(datainserimento, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE),
                                                'username' => array('tipo' => 'text', 'etichetta' => 'Utente', 'editable' => FALSE, 'size_filtro' => 16, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 20, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                'gruppo' => array('tipo' => 'select', 'etichetta' => 'Gruppo', 'editable' => FALSE, 'size_filtro' => 10, 'vert_larghezza' => 0, "attributi" => array_merge(array('default_vuoto' => '--------'), $sso_gruppo)),
                                                'calcolato28' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;e&nbsp;ora&nbsp;ritiro', 'size_filtro' => 8, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(dataritiro, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE),
                                                'calcolato47' => array('tipo' => 'calcolato', 'etichetta' => 'Tot.+iva-sc.&nbsp;&euro;', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'spesa', id)", 'ricalcolo' => FALSE),
                                                'calcolato56' => array('tipo' => 'calcolato', 'etichetta' => 'PDF', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => "CONCAT('<INPUT id=\"stampae_', id, '\" type=\"button\" class=\"emaildec center_elemento_imma\" value=\"&#x1F5B6;\" onClick=\"stampa_carrello(',id,');\" />')", 'ricalcolo' => FALSE),
                                                'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => (!$rpt_flag AND $app_admin), 'size_filtro' => 36, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 1, 'cols' => 40, 'maxlength' => 500))
                                          )
                        );

$tabella['carnuovo'] = array('nometabella' => 'carrelli',      // in lavorazione - oppure completati - completati con spese  TABELLA GENERICA - WARNING: non posso caricare due tabelle con lo stesso nome
                             'figlia' => array('carart' => 'Articoli'),
                             'chiave_padre' => '',
                             'drag_drop' => FALSE,
                             'nascondi_id' => FALSE,
                             'intestazione' => '',
                             'ordine' => array(),
                             'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => FALSE, 'paginazione' => 30),
                             'where' => 'id_sessione = '.(isset($_SESSION['DB_SEME']) ? $_SESSION['DB_SEME'] : 6743433333),  // sessione fittizia per sicurezza
                             'campi' => array('calcolato11' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' => 8, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(datainserimento, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE),
                                              'username' => array('tipo' => 'text', 'etichetta' => 'Utente', 'editable' => FALSE, 'size_filtro' => 16, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 20, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                              'gruppo' => array('tipo' => 'select', 'etichetta' => 'Gruppo', 'editable' => TRUE, 'size_filtro' => 10, 'vert_larghezza' => 0, "attributi" => array_merge(array('default_vuoto' => '--------'), $sso_gruppo)),
                                              'calcolato24' => array('tipo' => 'calcolato', 'etichetta' => 'Data e ora ritiro', 'size_filtro' => 8, 'vert_larghezza' => 0, 'formula' => "CONCAT('<INPUT id=\"calslot', id, '\" type=\"button\" class=\"pulsante_file center_elemento_imma\" value=\"', dataritiro, '\" onClick=\"apri_calendario(', id,');\" />')", 'ricalcolo' => FALSE),
                                              'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => TRUE, 'size_filtro' => 38, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 1, 'cols' => 40, 'maxlength' => 500))
                                        )
                        );

$tabella['carartatt'] =  array('nometabella' => 'carrelloart',  // Lista articoli inseriti nel carrello non confermati e non ritirati
                               'figlia' => $rpt_flag ? array('carrellia' => 'Carr.') : array(),
                               'chiave_padre' => $rpt['chiave_carrelloart'],
                               'drag_drop' => FALSE,
                               'nascondi_id' => FALSE,
                               'intestazione' => '',
                               'ordine' => array('calcolato8' => 'ASC'),
                               'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => (!$rpt_flag AND $app_admin), 'filtro' => $rpt_flag, 'paginazione' => 50),
                               'where' => '(NOT confermato AND NOT ritirato AND id_sessione = 0)'.$rpt_where,
                               'campi' => array('calcolato8' => array('tipo' => 'calcolato', 'etichetta' => 'Descrizione articolo', 'size_filtro' => 18, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'descrizione', id_articolo)", 'ricalcolo' => FALSE),
                                                'quantita' => array('tipo' => 'text', 'etichetta' => 'Qt.&agrave; ordinata', 'editable' => FALSE, 'size_filtro' => 3, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 4, 'maxlength' => 5, 'pattern' => '', 'error_pattern' => '')),
                                                'calcolato68' => array('tipo' => 'calcolato', 'etichetta' => 'Prezzo&nbsp;&euro;', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'prezzo', id_articolo)", 'ricalcolo' => FALSE),
                                                'calcolato20' => array('tipo' => 'calcolato', 'etichetta' => 'Nome prodotto', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'prodotto', id_prodotto)", 'ricalcolo' => FALSE),
                                                'calcolato36' => array('tipo' => 'calcolato', 'etichetta' => 'Marca', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'marca', id_articolo)", 'ricalcolo' => FALSE),
                                                'calcolato37' => array('tipo' => 'calcolato', 'etichetta' => 'Fornitore', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'fornitore', id_articolo)", 'ricalcolo' => FALSE),
                                                'calcolato42' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' => 9, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(dataritiro, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE)
                                          )
                          );

if(!isset($_SESSION['DB_SEME'])) {   // Sono in visualizzazione carrelli non in nuovo carrello quindi aggiungo i campi confermato e ritirato
    $tabella['carartatt']['campi']['confermato'] = array('tipo' => 'booleano', 'etichetta' => 'Confermato', 'editable' => (!$rpt_flag AND $app_admin), 'size_filtro' => 0, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '0'));
    $tabella['carartatt']['campi']['ritirato'] = array('tipo' => 'booleano', 'etichetta' => 'Ritirato', 'editable' => (!$rpt_flag AND $app_admin), 'size_filtro' => 0, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '0'));
} // Fine if su aggiunta campi carart

$tabella['carartconf'] =  array('nometabella' => 'carrelloart',      // Lista articoli approvati
                                'figlia' => $rpt_flag ? array('carrellic' => 'Carr.') : array(),
                                'chiave_padre' => $rpt['chiave_carrelloart'],
                                'drag_drop' => FALSE,
                                'nascondi_id' => FALSE,
                                'intestazione' => '',
                                'ordine' => array('calcolato63' => 'ASC'),
                                'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => $rpt_flag, 'paginazione' => 50),
                                'where' => '(confermato AND NOT ritirato AND id_sessione = 0)'.$rpt_where,
                                'campi' => array('calcolato63' => array('tipo' => 'calcolato', 'etichetta' => 'Descrizione articolo', 'size_filtro' => 18, 'vert_larghezza' => 0, 'formula' => "(dammi_campo_articoli('carart', 'descrizione', id_articolo))", 'ricalcolo' => FALSE),
                                                 'quantita' => array('tipo' => 'text', 'etichetta' => 'Qt.&agrave; ordinata', 'editable' => FALSE, 'size_filtro' => 3, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 4, 'maxlength' => 5, 'pattern' => '', 'error_pattern' => '')),
                                                 'calcolato69' => array('tipo' => 'calcolato', 'etichetta' => 'Prezzo&nbsp;&euro;', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "(dammi_campo_articoli('carart', 'prezzo', id_articolo))", 'ricalcolo' => FALSE),
                                                 'calcolato20' => array('tipo' => 'calcolato', 'etichetta' => 'Nome prodotto', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "(dammi_campo_articoli('carart', 'prodotto', id_prodotto))", 'ricalcolo' => FALSE),
                                                 'calcolato38' => array('tipo' => 'calcolato', 'etichetta' => 'Marca', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'marca', id_articolo)", 'ricalcolo' => FALSE),
                                                 'calcolato39' => array('tipo' => 'calcolato', 'etichetta' => 'Fornitore', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'fornitore', id_articolo)", 'ricalcolo' => FALSE),
                                                 'calcolato43' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;approvazione', 'size_filtro' => 10, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(dataritiro, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE)
                                           )
                          );

if(!isset($_SESSION['DB_SEME'])) {   // Sono in visualizzazione carrelli non in nuovo carrello quindi aggiungo i campi confermato e ritirato
    $tabella['carartconf']['campi']['confermato'] = array('tipo' => 'booleano', 'etichetta' => 'Confermato', 'editable' => (!$rpt_flag AND $app_admin), 'size_filtro' => 0, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '0'));
    $tabella['carartconf']['campi']['ritirato'] = array('tipo' => 'booleano', 'etichetta' => 'Ritirato', 'editable' => (!$rpt_flag AND $app_admin), 'size_filtro' => 0, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '0'));
} // Fine if su aggiunta campi carart

// Tabella degli articoli caricati nel carrello - La tengo valida anche per la tabella con i carrelli evasi
$tabella['carart'] =  array('nometabella' => 'carrelloart',      // Articoli confermati e ritirati -- oppure usati nei carrelli nuovi
                            'figlia' => $rpt_flag ? array('carrellie' => 'Carr.') : array(),
                            'chiave_padre' => ($rpt_flag ? $rpt['chiave_carrelloart'] : (isset($_SESSION['DB_SEME']) ? '' : 'id_carrello')),
                            'drag_drop' => FALSE,
                            'nascondi_id' => FALSE,
                            'intestazione' => '',
                            'ordine' => array('calcolato64' => 'ASC'),
                            'pulsanti' => array('verticale' => FALSE, 'hidevert' => $rpt_flag, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => $rpt_flag, 'paginazione' => 50),
                            'where' => isset($_SESSION['DB_SEME']) ? '(id_sessione = '.$_SESSION['DB_SEME'].')' : '(ritirato AND id_sessione = 0)'.$rpt_where,
                            'campi' => array('calcolato64' => array('tipo' => 'calcolato', 'etichetta' => 'Descrizione articolo', 'size_filtro' => 18, 'vert_larghezza' => 0, 'formula' => "(dammi_campo_articoli('carart', 'descrizione', id_articolo))", 'ricalcolo' => FALSE),
                                             'quantita' => array('tipo' => 'text', 'etichetta' => 'Qt&agrave; ordinata', 'editable' => FALSE, 'size_filtro' => 4, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 4, 'maxlength' => 5, 'pattern' => '', 'error_pattern' => '')),
                                             'calcolato66' => array('tipo' => 'calcolato', 'etichetta' => 'Prezzo&nbsp;&euro;', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "(dammi_campo_articoli('carart', 'prezzo', id_articolo))", 'ricalcolo' => FALSE),
                                             'calcolato20' => array('tipo' => 'calcolato', 'etichetta' => 'Nome prodotto', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "(dammi_campo_articoli('carart', 'prodotto', id_prodotto))", 'ricalcolo' => FALSE),
                                             'calcolato67' => array('tipo' => 'calcolato', 'etichetta' => 'Prezzo&nbsp;&euro;', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "(dammi_campo_articoli('carart', 'prezzo', id_articolo))", 'ricalcolo' => FALSE),
                                             'calcolato40' => array('tipo' => 'calcolato', 'etichetta' => 'Marca', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'marca', id_articolo)", 'ricalcolo' => FALSE),
                                             'calcolato41' => array('tipo' => 'calcolato', 'etichetta' => 'Fornitore', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'fornitore', id_articolo)", 'ricalcolo' => FALSE)
                                       )
                       );

if('0000' == $_REQUEST['lista_richieste_tabella']['anno_richiesta'] OR $rpt_flag)
    $tabella['carart']['campi']['calcolato31'] = array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;ritiro', 'size_filtro' => 9, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(dataritiro, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE);

if(!isset($_SESSION['DB_SEME']) AND '0000' != $_REQUEST['lista_richieste_tabella']['anno_richiesta']) {   // Sono in visualizzazione carrelli non in nuovo carrello quindi aggiungo i campi confermato e ritirato
    $tabella['carart']['campi']['confermato'] = array('tipo' => 'booleano', 'etichetta' => 'Confermato', 'editable' => !$rpt_flag, 'size_filtro' => 0, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '0'));
    $tabella['carart']['campi']['ritirato'] = array('tipo' => 'booleano', 'etichetta' => 'Ritirato', 'editable' => !$rpt_flag, 'size_filtro' => 0, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '0'));
} // Fine if su aggiunta campi carart

if($rpt_flag)  {
    $id_carrello_nuovo_campo = array('id_carrello' => array('tipo' => 'text', 'etichetta' => 'ID carrello', 'editable' => FALSE, 'size_filtro' => 3, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 4, 'maxlength' => 5, 'pattern' => '', 'error_pattern' => '')));
    $tabella['carart']['campi'] = $id_carrello_nuovo_campo + $tabella['carart']['campi'];
    $tabella['carartconf']['campi'] = $id_carrello_nuovo_campo + $tabella['carartconf']['campi'];
    $tabella['carartatt']['campi'] = $id_carrello_nuovo_campo + $tabella['carartatt']['campi'];
}

$tabella['prodcar'] =  array('nometabella' => 'prodotti',      // Tabella di selezione dei prodotti dal carrello
                             'figlia' => array('artprod' => 'Articoli'),
                             'chiave_padre' => '',
                             'drag_drop' => FALSE,
                             'nascondi_id' => FALSE,
                             'intestazione' => 'Inserimento della quantit&agrave; di prodotto da caricare nel carrello',
                             'ordine' => array('nome' => 'ASC'),
                             'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => TRUE, 'paginazione' => 30),
                             'where' => 'conta_articoli_attivi(id)',
                             'campi' => array('calcolato45' => array('tipo' => 'calcolato', 'etichetta' => 'Stato', 'size_filtro' => 10, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('art', 'proddep', id)", 'ricalcolo' => FALSE),
                                              'calcolato21' => array('tipo' => 'calcolato', 'etichetta' => 'Giacenza magazz.', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => "giacenza_articoli(id, 'POS')", 'ricalcolo' => FALSE),
                                              'calcolato10' => array('tipo' => 'calcolato', 'etichetta' => 'Quantit&agrave', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => 'campo_selezione_prodotto('.(isset($_SESSION['DB_SEME']) ? $_SESSION['DB_SEME'] : 6743433333).', maxordine, id)', 'ricalcolo' => FALSE),
                                              'nome' => array('tipo' => 'text', 'etichetta' => 'Descrizione prodotto', 'editable' => FALSE, 'size_filtro' => 22, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 30, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                              'ganalitico' => array('tipo' => 'select', 'etichetta' => 'Grado analitico', 'editable' => FALSE, 'size_filtro' => 10, 'vert_larghezza' => 0, "attributi" => genera_select($conn_obj, 'prodotti', 'ganalitico')),
                                              'marca' => array('tipo' => 'select', 'etichetta' => 'Marca', 'editable' => FALSE, 'size_filtro' => 10, 'vert_larghezza' => 0, 'attributi' => genera_select($conn_obj, 'prodotti', 'marca')),
                                              'confezione' => array('tipo' => 'select', 'etichetta' => 'Confezione', 'editable' => FALSE, 'size_filtro' => 10, 'vert_larghezza' => 0, 'attributi' => genera_select($conn_obj, 'prodotti', 'confezione')),
                                              'capacita' => array('tipo' => 'text', 'etichetta' => 'Capacit&agrave;', 'editable' => FALSE, 'size_filtro' => 4, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 10, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')),
                                              'unita_misura' => array('tipo' => 'text', 'etichetta' => 'Unit&agrave; misura', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 4, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')),
                                              'maxordine' => array('tipo' => 'text', 'etichetta' => 'Limite qt.&agrave;', 'editable' => FALSE, 'size_filtro' => 0, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '100', 'size' => 3, 'maxlength' => 4, 'pattern' => '^d{0,4}$', 'error_pattern' => 'Imettere un valore compreso tra 0 e 9999 !')),
                                              'scheda' => array('tipo' => 'file', 'etichetta' => 'Scheda', 'editable' => FALSE, 'size_filtro' => 0, 'vert_larghezza' => 0, 'attributi' => array('cartella_upload' => 'upload/schede', 'larghezza_max_miniatura' => 90, 'non_duplicabile' => FALSE))
                                        )
                       );


$tabella['comunicazioni'] = array('nometabella' => 'comunicazioni',        // Tabella della comunicazioni
                            'figlia' => ($is_home_page ? array('carrcom' => 'Car.') : array()),
                            'chiave_padre' => ($is_home_page ? '' : 'id_carrello'),
                            'drag_drop' => FALSE,
                            'nascondi_id' => FALSE,
                            'intestazione' => ($is_home_page ? 'Messaggi agli utenti' : ''),
                            'ordine' => array('datainserimento' => 'DESC'),
                            'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => ($app_admin && $is_home_page), 'delete' => $app_admin, 'filtro' => $is_home_page, 'paginazione' => 16),
                            'where' => ($app_admin ? '' : 'destinatario LIKE \''.(isset($_SESSION['GAUTH_mail']) ? $_SESSION['GAUTH_mail'] : 'nonesiste@prova.com').'\' OR ISNULL(id_carrello)'),
                            'campi' => array('calcolato52' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' => 9, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(datainserimento, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE),
                                             'destinatario' => array('tipo' => 'text', 'etichetta' => 'Destinatario', 'editable' => FALSE, 'size_filtro' => 27, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 32, 'maxlength' => 150, 'pattern' => '', 'error_pattern' => '')),
                                             'oggetto' => array('tipo' => 'text', 'etichetta' => 'Oggetto', 'editable' => FALSE, 'size_filtro' => 38, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 40, 'maxlength' => 150, 'pattern' => '', 'error_pattern' => '')),
                                             'mailtxt' => array('tipo' => 'textarea', 'etichetta' => 'Messaggio', 'editable' => FALSE, 'size_filtro' => 46, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 4, 'cols' => 50, 'maxlength' => 500))
                                       )
                      );

$tabella['carrcom'] = array('nometabella' => 'carrelli',      // Tabella con i carrelli tutti legata alla tabella comunicazioni
                            'figlia' => array(),
                            'chiave_padre' => 'id',
                            'drag_drop' => FALSE,
                            'nascondi_id' => FALSE,
                            'intestazione' => '',
                            'ordine' => array('id' => 'DESC'),
                            'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => 'id_carrello', 'add' => FALSE, 'delete' => FALSE, 'filtro' => FALSE, 'paginazione' => 20),
                            'where' => '',
                            'campi' => array('calc11' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' => 9, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(datainserimento, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE),
                                             'username' => array('tipo' => 'text', 'etichetta' => 'Utente', 'editable' => FALSE, 'size_filtro' => 16, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 20, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                             'gruppo' => array('tipo' => 'select', 'etichetta' => 'Gruppo', 'editable' => FALSE, 'size_filtro' => 10, 'vert_larghezza' => 0, "attributi" => array_merge(array('default_vuoto' => '--------'), $sso_gruppo)),
                                             'calc28' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;e&nbsp;ora&nbsp;ritiro', 'size_filtro' => 8, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(dataritiro, '%Y-%m-%d %H:%i')", 'ricalcolo' => FALSE),
                                             'calc44' => array('tipo' => 'calcolato', 'etichetta' => 'Tot.+iva-sc.&nbsp;&euro;', 'size_filtro' => 6, 'vert_larghezza' => 0, 'formula' => "dammi_campo_articoli('carart', 'spesa', id)", 'ricalcolo' => FALSE),
                                             'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => FALSE, 'size_filtro' => 36, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 3, 'cols' => 40, 'maxlength' => 500))
                                       )
                        );

// Le seguenti due tabelle forniscono i valori per i menu a tendina
$tabella['menu'] =  array('nometabella' => 'menu',      //  Tabella con l' indicazione delle opzioni disponibili nei campi di scelta; tipo select o calendaario
                          'figlia' => array('vocimenu' => 'Voci'),
                          'chiave_padre' => '',
                          'drag_drop' => FALSE,
                          'nascondi_id' => FALSE,
                          'intestazione' => 'Voci menu a tendina nelle tabelle e calendario',
                          'ordine' => array('tabella' => 'ASC'),
                          'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => $app_admin, 'delete' => $app_admin, 'filtro' => FALSE, 'paginazione' => 20),
                          'where' => '',
                          'campi' => array('tabella' => array('tipo' => 'text', 'etichetta' => 'Nome tabella', 'editable' => $app_super_admin, 'size_filtro' => 20, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 20, 'maxlength' => 100, 'pattern' => '^.{0,100}$', 'error_pattern' => '')),
                                           'nomecampo' => array('tipo' => 'text', 'etichetta' => 'Nome campo', 'editable' => $app_super_admin, 'size_filtro' => 5, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 16, 'maxlength' => 100, 'pattern' => '^.{0,100}$', 'error_pattern' => '')),
                                           'descrizione' => array('tipo' => 'text', 'etichetta' => 'Descrizione campo', 'editable' => $app_admin, 'size_filtro' => 5, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 50, 'maxlength' => 150, 'pattern' => '^.{0,150}$', 'error_pattern' => ''))
                                     )
                     );


$tabella['vocimenu'] =  array('nometabella' => 'vocimenu',      //  Tabella delle voci collegate al menu
                              'figlia' => array(),
                              'chiave_padre' => 'id_menu',
                              'drag_drop' => FALSE,
                              'nascondi_id' => FALSE,
                              'intestazione' => '',
                              'ordine' => array('valore' => 'ASC'),
                              'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => $app_admin, 'delete' => $app_admin, 'filtro' => TRUE, 'paginazione' => 20),
                              'where' => '',
                              'campi' => array('valore' => array('tipo' => 'text', 'etichetta' => 'Codifica valore', 'editable' => $app_admin, 'size_filtro' => 20, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 24, 'maxlength' => 100, 'pattern' => '^.{0,100}$', 'error_pattern' => '')),
                                               'descrizione' => array('tipo' => 'text', 'etichetta' => 'Valore', 'editable' => $app_admin, 'size_filtro' =>34, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 40, 'maxlength' => 100, 'pattern' => '^.{0,100}$', 'error_pattern' => ''))
                                         )
                        );

$tabella['gruppi'] = array('nometabella' => 'gruppi',        // Tabella dei gruppi di utilizzo del gestionale
                            'figlia' => array('utenti' => 'Utenti'),
                            'chiave_padre' => '',
                            'drag_drop' => FALSE,
                            'nascondi_id' => FALSE,
                            'intestazione' => 'Gestione gruppi e utenti',
                            'ordine' => array('nome' => 'ASC'),
                            'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => $app_admin, 'delete' => $app_admin, 'filtro' => TRUE, 'paginazione' => 25),
                            'where' => $rpt['filtro_gruppo_utenti'],
                            'campi' => array('nome' => array('tipo' => 'text', 'etichetta' => 'Nome gruppo', 'editable' => $app_admin, 'size_filtro' => 15, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 20, 'maxlength' => 150, 'pattern' => '', 'error_pattern' => 'Inserire almeno un carattere')),
                                             'responsabile' => array('tipo' => 'text', 'etichetta' => 'Responsabile', 'editable' => $app_admin, 'size_filtro' => 17, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 22, 'maxlength' => 150, 'pattern' => '^.+$', 'error_pattern' => '')),
                                             'emailresp' => array('tipo' => 'text', 'etichetta' => 'Indirizzo e-mail', 'editable' => $app_admin, 'size_filtro' => 23, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 28, 'maxlength' => 150, 'pattern' => EMAIL_PATTERN_REGX, 'error_pattern' => 'Indirizzo email non valido')),
                                             'struttura' => array('tipo' => 'text', 'etichetta' => 'Struttura', 'editable' => $app_admin, 'size_filtro' => 31, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 36, 'maxlength' => 250, 'pattern' => '', 'error_pattern' => '')),
                                             'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => $app_admin, 'size_filtro' => 32, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 1, 'cols' => 36, 'maxlength' => 500))
                                       )
                      );


$tabella['utenti'] =  array('nometabella' => 'utenti',      // Tabella utenti del gestionale
                            'figlia' => array(),
                            'chiave_padre' => 'id_gruppo',
                            'drag_drop' => FALSE,
                            'nascondi_id' => FALSE,
                            'intestazione' => '',
                            'ordine' => array('cognome' => 'ASC'),
                            'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => $app_admin, 'delete' => $app_admin, 'filtro' => FALSE, 'paginazione' => 25),
                            'where' => '',
                            'campi' => array('calcolato1' => array('tipo' => 'calcolato', 'etichetta' => 'Data&nbsp;inserimento', 'size_filtro' => 10, 'vert_larghezza' => 0, 'formula' => "DATE_FORMAT(datainserimento, '%Y-%m-%d')", 'ricalcolo' => FALSE),
                                             'email' => array('tipo' => 'text', 'etichetta' => 'Indirizzo e-mail utente', 'editable' => $app_admin, 'size_filtro' => 22, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 28, 'maxlength' => 150, 'pattern' => EMAIL_PATTERN_REGX, 'error_pattern' => 'Indirizzo email non valido')),
                                             'cognome' => array('tipo' => 'text', 'etichetta' => 'Cognome', 'editable' => $app_admin, 'size_filtro' => 16, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 22, 'maxlength' => 100, 'pattern' => '^.{0,100}$', 'error_pattern' => '')),
                                             'nome' => array('tipo' => 'text', 'etichetta' => 'Nome', 'editable' => $app_admin, 'size_filtro' => 16, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 22, 'maxlength' => 100, 'pattern' => '^.{0,100}$', 'error_pattern' => '')),
                                             'note' => array('tipo' => 'textarea', 'etichetta' => 'Note', 'editable' => $app_admin, 'size_filtro' => 32, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'rows' => 1, 'cols' => 34, 'maxlength' => 500))
                                       )
                          );

if($app_admin AND 'utenti' == $_REQUEST['lista_richieste_tabella']['servizio_richiesto']) {   // Campo password visibile solo agli admin
     $id_carrello_nuovo_campo = array('tipo' => 'text', 'etichetta' => 'Password', 'editable' => TRUE, 'size_filtro' => 7, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 12, 'maxlength' => 50, 'pattern' => '^.{4,50}$', 'error_pattern' => 'Minimo 4 max. 50 caratteri !'));
     $tabella['gruppi']['campi'] = array_insert_after('emailresp', $tabella['gruppi']['campi'], 'password', $id_carrello_nuovo_campo);
     $tabella['utenti']['campi'] = array_insert_after('email', $tabella['utenti']['campi'], 'password', $id_carrello_nuovo_campo);
}  // Fine if password per gli admin

?>