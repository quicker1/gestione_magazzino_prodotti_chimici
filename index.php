<?PHP  // 25 luglio 2024 - michele.furlan@unipd.it
require_once('./config.php');
if(!$enable_sso_auth AND (LOGIN_USER_FORM OR ENABLE_GOOGLE_AUTH)) require_once('./login.php');   // E' richiesto il LOGIN classico basato su DB locale

// Va forzato il protocollo HTTPS altrimenti in produzione non funziona il modulo di autenticazione di google
if(FORZA_PROTOCOLLO_HTTPS AND $_SERVER['SERVER_NAME'] == NOME_SERVER_ESECUZIONE)
  if(isset($_SERVER['REQUEST_SCHEME']) AND $_SERVER['REQUEST_SCHEME'] == 'http') {
     header('Location: '.URI_SERVER_ESECUZIONE);
     exit();
  }
?>
<!DOCTYPE html>
<HTML lang="it" dir="ltr" translate="no">   <!-- 31 gennaio 2024 - michele.furlan@unipd.it -->
<HEAD><TITLE>GESTIONALE MAGAZZINO PRODOTTI CHIMICI</TITLE>
<LINK rel="canonical" href="https://wwwdisc.chimica.unipd.it/michele.furlan/index.php" />
<meta name="google-site-verification" content="MaLt7vfFXA0U4lpt_ucVWYvSAI7JmJ7jGkIbiNvymE0" /> <!-- Google meta tag inserito il 09 giugno 2025 -->
<META http-equiv="Content-Type" content="text/html; charset=utf-8">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache" />
<META HTTP-EQUIV="cache-control" content="no-cache, no-store, must-revalidate" />
<META http-equiv="expires" content="0" />
<META property="og:title" content="SOFTWARE GESTIONALE MAGAZZINO PRODOTTI CHIMICI" />
<META property="og:description" content="Software gestionale prodotti chimici gratuito, senza limiti di tempo ed utilizzo. Scaricalo gratis" />
<META property="og:locale" content="it_IT" />
<META property="og:type" content="website" />
<META property="og:url" content="https://wwwdisc.chimica.unipd.it/michele.furlan" />
<META property="og:site_name" content="MAGAZZINO prodotti chimici" />
<META name="title" content="Gestionale magazzino" />
<META name="description" content="Gestionale gratuito per il magazzino dei prodotti chimici. Codice sorgente gratis scritto in linguaggio PHP, javascript." />
<META name="keywords" content="gestionale magazzino, gestionale open source, software gestione magazzino, software gestionale gratuito, gestionale php in cloud">
<META name="robots" content="index, follow" />
<META name="revisit-after" content="30 days" />
<META name="author" content="michele.furlan@unipd.it" />
<META name="viewport" content="width=device-width, initial-scale=1.0" />
<META name="google-signin-scope" content="profile email" />
<META name="google-signin-client_id" content="<?PHP echo GOOGLE_CLIENT_ID_CODICE; ?>" />
<LINK rel="StyleSheet" href="./style.css?random=<?PHP echo uniqid(); ?>" type="text/css" media="all" />
<LINK rel="icon" type="image/x-icon" href="./imma/favicon.ico" />
<LINK rel="shortcut icon" type="image/x-icon" href="./imma/favicon.ico" />
<SCRIPT type="text/javascript" src="./js/jquery-3.7.1.min.js"></SCRIPT> <!-- Carica libreria JQuery -->

<?PHP if(!LOGIN_USER_FORM AND !$enable_sso_auth) {
?>
  <SCRIPT src="https://accounts.google.com/gsi/client" async defer></SCRIPT>
<?PHP }
if($enable_sso_auth) {   // Verifica sessione SSO
?>
<SCRIPT type="text/javascript" src="./js/myapptabelle.js?random=<?PHP echo uniqid(); ?>"></SCRIPT> <!-- Carica  lo script di gestione della app ed evito il caching con query string variabile-->
<SCRIPT type="text/javascript">

var archivio_intervalli = [];
// Solo dopo il caricamento completo della pagina si possono caricare le tabelle
$(document).ready(function() {

$(window).on('beforeunload', function(e) {  // Suggerisco di non manovrare con il back del browser
                e.preventDefault();
                e.returnValue = "Non usare i pulsanti del browser per la navigazione del sito !\Fare il logout per uscire dalla pagina.";
          });

if($(window).width() < screen.width/2) {
  try {
     window.moveTo(0, 0);  // Massimizzo screen
     window.resizeTo(screen.width, screen.height);
  }
  catch(e) {
     $.noop();
  }
}

<?PHP   // Emissione script x logout solo se in server produzione
if(isset($_SESSION['GAUTH_mail'])) {  // produzione logo uscita -- esiste solo se in server di produzione
?>

 window.signOut_google = function($g) {
    if($g)
      setTimeout(function(){location.href = "https://www.google.com/accounts/Logout?continue=https://appengine.google.com/_ah/logout?continue=https://wwwdisc.chimica.unipd.it/michele.furlan"; }, 1000);
    else
      location.href = './index.php';
 };  // fine signOut_google()

<?PHP
}  // Fine if emissione script se logout
?>

window.objTabelle = new AvviaAppTabelle(); // Crea l''oggetto AvviaAppTabelle (variabile globale)
  if(objTabelle.inizializzato) {           // Solo se inizializzazione riuscita carico le tabelle
       smista_opzione();
  }
}); // fine document ready


function parametri_selezione_menu() {  // Raggruppamento dati - parametro_selezione e' usato in myapptabelle.js in update_campi, vedi_tabella ecc...
  return {'tipo_richiesta':$.data(window, 'tipo_richiesta'),
          'servizio_richiesto':$.data(window, 'servizio_richiesto'),
          'anno_richiesta':$.data(window, 'anno_richiesta')};
} // fine function parametri_selezione_menu()


function stampa_richiesta(val) {
   downloadFile('./creapdf.php?' + val);
return false;
}  // fine function stampa_richiesta()


function decoratore_email() {
<?PHP
  if(isset($_SESSION['GAUTH_mail'])) {  // produzione evidenziazione riga -- esiste solo se in server di produzione con autenticazione google gmail
?>
  $("TD:contains('" + <?PHP echo "'".$sso_appusername."'"; ?> + "')").parent('TR').css('background-color', '#E9F5DB');
<?PHP
  }  // fine if decoratore email
?>
return true;
} // fine function decoratore_email

function smista_opzione(obj){  // In base alla situazione delle tre select decide la visualizzazione nel panello di destra
  objTabelle.Reset();       // Pulizia di tutti gli oggetti tabella creati
  archivio_intervalli.map((a) => {clearInterval(a);arr = [];});  // Rimozione di tutti gli eventi scatenati da SetInterval

  if(obj) {
     $("DIV.item_menu[data-ser='" + $(obj).data('ser') + "']").removeClass('item_click');  // Prima rimuovo tutte le classi item_click dai menu
     $(obj).addClass('item_click');  // Poi evidenzio l'elemento cliccato
  }

// Tutti i seguenti valori in $.data sono indispensabili ed usati nella myapptabelle.js

  $.data(window, 'servizio_richiesto',  $("DIV.item_click[data-ser='servizio_richiesto']").data('sc'));  // Opzione usata in vedi_tabella - default HOME
  $.data(window, 'tipo_richiesta',  $("DIV.item_click[data-ser='tipo_report']").data('sc'));
  $.data(window, 'anno_richiesta', '9999');     // Opzione usata nella generazione dei rapporti
  $('#corpo_tabella_richiesta').html('');       // Svuoto DIV
  $('#corpo_tabella_listarichieste').html('');  // Svuoto DIV
  $('#corpo_tabella_listaterza').html('');      // Svuoto DIV

  if ($('#div_blocco_report').is(':visible'))
      $('#div_blocco_report').hide();

  switch ($.data(window, 'servizio_richiesto')) {
  case 'HOME' :  {  // Home page default
      $('#corpo_tabella_richiesta').html('<DIV class="div_home">Selezionare nei menu laterali la <B>Visualizzazione</B></DIV>' +
      '<BR /><SPAN class="class_data">&nbsp;Download installazione:&nbsp;<A href="./gestione_magazzino.zip" target="_new">gestione_magazzino.zip</A>&nbsp;&nbsp;Manuale:&nbsp;<A href="./documentazione/manuale.pdf" target="_new">manuale.pdf</A>&nbsp;</SPAN>');
      objTabelle.CaricaTabella('corpo_tabella_listarichieste', 'comunicazioni');
      $('#corpo_tabella_listaterza').load('./crud.php', {'tbl_azione': 'veditabella', 'servizio_richiesto': 'HOME'}, function(responseTxt, statusTxt, xhr){
          if(statusTxt == "error")
             stato_aggiornamento(false, xhr.status + ':<BR />' + xhr.statusText);
      });
  break;
  }  // Vado in HOME page di default

  case 'carrelli' : {  // Ho tre tabella in successione nella stessa pagina
        $.data(window, 'servizio_richiesto', 'carrelli');
          if(objTabelle.CaricaTabella('corpo_tabella_richiesta', 'carrellia'))
               if(objTabelle.CaricaTabella('corpo_tabella_listarichieste', 'carrellic')) {
                   $.data(window, 'anno_richiesta', '0000');   // Flag per non edit dei campi articoli ritirati
                   objTabelle.CaricaTabella('corpo_tabella_listaterza', 'carrellie');
               }
  break;
  }  // Fine else if carrelli
  case 'utenti' : {   // Gestione utenti/gruppi
        objTabelle.CaricaTabella('corpo_tabella_richiesta', 'gruppi');       // L' altra il tariffario da compilare
  break;
  }
  case 'fornitori' : {
        objTabelle.CaricaTabella('corpo_tabella_richiesta', 'forni');
  break;
  }
  case 'prodotti' : {
        if(objTabelle.CaricaTabella('corpo_tabella_richiesta', 'prod')) {
            $.data(window, 'anno_richiesta', '0001');
            objTabelle.CaricaTabella('corpo_tabella_listarichieste', 'artprod');
            $.data(window, 'anno_richiesta', '9999');
        }
  break;
  }
  case 'ordini' : {
        if(objTabelle.CaricaTabella('corpo_tabella_richiesta', 'ordini'))
            objTabelle.CaricaTabella('corpo_tabella_listarichieste', 'ordini_e');
  break;
  }
  case 'voci' : {
        objTabelle.CaricaTabella('corpo_tabella_richiesta', 'menu');
  break;
  }
  case 'nuovocarrello' : {
    stato_aggiornamento(true, '');   // Avviso utente attessa e async ajax false
    $.post('./crud.php', {'tbl_nome':'carnuovo', 'tbl_azione':'nuovocarrello'}, function(data) {
         stato_aggiornamento(false, '');  // Chiude la finestra modale
    }).done(function(dati, stato_ritorno, jqXHRinfo) {  // In dati valore di ritorno della richiesta
      if(dati == 'OK')
          if(objTabelle.CaricaTabella('corpo_tabella_richiesta', 'carnuovo')) {
              if(objTabelle.CaricaTabella('corpo_tabella_listarichieste', 'prodcar')) {
                 archivio_intervalli.push(setInterval(function() {
                 let currentValue = $('#timer_1m').val();
                     if (currentValue > 2) {
                         $('#timer_1m').val(currentValue - 1);
                         $('#meter_id1').html($('#timer_1m').val() + '&nbsp;minuti');
                     } else if (currentValue == 2) {
                        archivio_intervalli.map((a) => {clearInterval(a);arr = [];});
                        $('#meter_id1').html(60 + '&nbsp;secondi');
                            archivio_intervalli.push(setInterval(function() {
                            let currentSeconds = parseInt($('#meter_id1').html());
                            if (currentSeconds > 0) {
                                 $('#timer_1m').val(parseFloat(1 + currentSeconds/60).toFixed(2));
                                 $('#meter_id1').html(currentSeconds - 1 + '&nbsp;secondi');
                            } else {
                                 archivio_intervalli.map((a) => {clearInterval(a);arr = [];});
                                 $('#timer_1m').val(0);
                                 $('#meter_id1').html('BYE');
                                 annulla_formazione_carrello();
                                 alert("TIMEOUT\nE' necessario compilare un nuovo carrello !");
                                 $("DIV[data-sc='HOME']").trigger('click');   // Torno in home
                                 smista_opzione();
                            }
                        }, 1000));
                     }
                   }, 60*1000));  // Ogni minuto c'e' un scatto della barra meter
              } // Fine if carico tabella scelta prodotti  'prodcar'
          } // Fine if carico tabella 'carnuovo'
      else
          alert("Errore nella creazione del nuovo carrello !");
    });
  break;
  }
  case 'rapporti' : {
      if ($("#div_blocco_report").is(":hidden"))
           $("#div_blocco_report").show();
      $('#corpo_tabella_richiesta').empty().html('<FORM onSubmit="return false;">'+
        '<LABEL class="classe_label">DAL:&nbsp;</LABEL><INPUT id="data_inizio_r" class="class_data" type="date" min="2022-01-01" value="'+getCurrentDate()+'" required />&nbsp;'+
        '<LABEL class="classe_label">AL:&nbsp;</LABEL><INPUT id="data_fine_r" class="class_data" type="date" min="2022-01-01" value="'+getCurrentDate()+'" required />'+
        '<LABEL class="classe_label">&nbsp;</LABEL><INPUT type="button" class="pulsante" value="&#x1F4CA;&nbsp;&nbsp;GENERA REPORT" onclick="genera_rapporto();" />'+
        '</FORM><DIV class="div_home">Selezionare il <B>TIPO REPORT</B>, impostare la data iniziale e finale ed infine cliccare: <B>GENERA REPORT</B></DIV>');
  break;
  }
  default:
     $.noop();
  } // Fine case

decoratore_email();
return false;  // stop bubbling event
}  // fine function  smista_opzione()


function aggiorna_elenco_articoli(id, limite, obj) {   // modifico la lista degli articoli nel carrello in base all'id prodotto e quantita' richiesta
    if(isNaN($(obj).val())) {
       alert('Immettere una quantita numerica !');
       return false;
    }
    if($(obj).val() > limite) {  // Non posso superare il limite fissato per ordinare il prodotto
         alert("La quantit\u00E0 di prodotto desiderata supera il limite consentito pari a: " + limite + "\nVerr\u00E0 imposta la quantit\u00E0 limite.");
         $(obj).val(limite);
    }
    stato_aggiornamento(true, '');   // Avviso utente attessa e async ajax false
    $.post('./crud.php', {'tbl_nome':'carart', 'tbl_azione':'modcarrello', 'id_record':id, 'valore': $(obj).val()}, function(data) {
         stato_aggiornamento(false, '');  // Chiude la finestra modale
         if(isNaN(data))
             alert("Errore:\n" + data + "\nmancata formazione degli articoli nel carrello");
    }).done(function() {    // Apertura dell'elenco articoli immessi nel nuovo carrello
        apri_tabella('carart-carnuovo-' + $(obj).attr('data-idcar') + '-veditabella');
    });
}  // Fine function aggiorna_elenco_articoli


function conferma_valido_carrello() {   // Verifico anche la presenza di articoli nel nuovo carrello
let valido = true;

// Obbligo di selezione del gruppo
  if($("SELECT[id^='carnuovo-gruppo-'][id$='-edit']").val().length < 2) {
       alert('Selezionare il gruppo di appartenenza !');
       $("SELECT[id^='carnuovo-gruppo-'][id$='-edit']").focus().select();
  return false;
  }

  if(isNaN(cToUnixTime($("INPUT[id^='calslot']").val()))) { // Controllo validita slot prenotazione
       alert('Selezionare data e ora di ritiro del carrello !');
  return false;
  }

   stato_aggiornamento(true, '');   // Avviso utente attessa e async ajax false
     $.post('./crud.php', {'tbl_nome':'carnuovo', 'tbl_azione':'verificacarrello'}, function(data) {
         stato_aggiornamento(false, '');  // Chiude la finestra modale
         if(0 == data) {
             alert('Inserire almeno un prodotto nel carrello !');
             valido = false;
         }
         else {
            if(!confirm("Confermi l\'invio del carrello ?"))
            valido = false;
         }

     }).done(function() {
     });

return valido;
}   // fine function conferma_valido_carrello


function conferma_formazione_carrello() {  // Procede alle verifiche prima di salvare il nuovo carrello

  if(conferma_valido_carrello()) {
     stato_aggiornamento(true, '');   // Avviso utente attessa e async ajax false
     $.post('./crud.php', {'tbl_nome':'carnuovo', 'tbl_azione':'confermacarrello'}, function(data) {
         stato_aggiornamento(false, '');  // Chiude la finestra modale
         if(0 == data)
             alert('Errore accettazione del carrello');
         else
             $("DIV[data-sc='carrelli']").trigger('click');   // Vado alla lista dei carrelli
     }).done(function() {
     });
  }  // fine if conferma invio carrello ?

return false;
}  // Fine conferma_formazione_carrello


function annulla_formazione_carrello() {
  stato_aggiornamento(true, '');   // Avviso utente attessa e async ajax false
    $.post('./crud.php', {'tbl_nome':'carart', 'tbl_azione':'annullacarrello'}, function(data) {
         stato_aggiornamento(false, '');  // Chiude la finestra modale
         if(0 == data)
             alert('Errore di annullamento del carrello !');
         else {
             $("DIV[data-sc='HOME']").trigger('click');   // Vado in HOME eseguendo il trigger il click sulla corrispondente voce
         }
    }).done(function() {
    });
}  // Fine annulla_formazione_carrello


function aggiorna_carrello_inattesa(id_art, id_car, valore) {  // Aggiorna la quantita di una specifica quantita di articolo presente in un carrello con articoli in attesa di conferma
 stato_aggiornamento(true, '');   // Avviso utente attessa e async ajax false

 $.post('./crud.php', {'tbl_nome':'artcarra', 'tbl_azione':'modcinattesa', 'id_art':id_art, 'id_car':id_car, 'valore':valore}, function(data) {
         stato_aggiornamento(false, '');  // Chiude la finestra modale
         if(0 != data)
             alert("Errore:\n" + data + "\nmancata modifica degli articoli nel carrello.");
    }).done(function() {
    });

return false;
}  // Fine aggiorna_carrello_inattesa


function stampa_carrello(id) {  // Genera il PDF con le informazioni riguardanti il carrello (id == id_carrello)
   stampa_richiesta('tipo=carrello&tbl_id=' + id);
}   // fine function gestione_messaggio


function apri_calendario(id) {   // Carica il calendario per la scelta dello slot di ritiro
toggleModal();  // Apro/chiudo la finestra modale
    $('#id_stato_aggioramento').empty().load('./crud.php', {'tbl_nome':'carart', 'tbl_id_div':id, 'tbl_azione':'apricalendario'}, function(responseTxt, statusTxt, xhr){
        if(statusTxt == 'error')
           stato_aggiornamento(false, xhr.status + ':<BR />' + xhr.statusText);
        else {
           $.noop();
        }
    });  // Fine function load calendario
}  // Fine function apri_calendario


function aggiorna_prenotazione(id, vdata) {  // riceve l'id dell nuovo carrello e la nuova data in formato UNIX_TIMESTAMP -> secondi da 1-1-1970
    stato_aggiornamento(true, '');   // Avviso utente attessa e async ajax false
    $.post('./crud.php', {'tbl_nome':'carnuovo', 'tbl_azione':'aggiornadataslot', 'id_record':id, 'valore':vdata}, function(data) {
         if(0 == data)
             alert('Errore prenotazione dello slot di ritiro del carrello');
         else
             $('#calslot' + id).val(unixTimestampToDateTime(vdata));     // Cambio data nel pulsante a riprova dell' avvenuta prenotazione
    }).done(function() {
        stato_aggiornamento(false, '');  // Chiude la finestra modale
    });
} // fine function aggiorna_prenotazione


function create_Date(date_string) {   // Verifica se una stringa e' una data regolare in formato 'AAAA-MM-GG'
  const regexp = /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/;
  const regexp2 = /^(\d{0,13})?$/; // {0,13} instead of {13}
  let date;
  switch (typeof date_string) {
    case "string":
      if (regexp2.test(date_string)) {
        date = true; // Date constructor need milliseconds
    break;       // You forget this break
      }
      if (regexp.test(date_string))
        date = true;
      else
        date = false;
    break;
    case "undefined":
      date = false;
    break;
    default:
      date = false;
  }
return date;
}


function unixTimestampToDateTime(unixTimestamp) {  // Crea un nuovo oggetto Data basato sul timestamp UNIX
  const date = new Date(unixTimestamp * 1000);     // Moltiplica per 1000 per convertire in millisecondi
  // Estrai i componenti della data
  const anno = date.getFullYear();
  const mese = String(date.getMonth() + 1).padStart(2, '0'); // Aggiungi 1 perché i mesi iniziano da 0
  const giorno = String(date.getDate()).padStart(2, '0');
  const ore = String(date.getHours()).padStart(2, '0');
  const minuti = String(date.getMinutes()).padStart(2, '0');
  const secondi = String(date.getSeconds()).padStart(2, '0');
  // Crea la stringa nel formato desiderato
  const dataOraString = `${anno}-${mese}-${giorno} ${ore}:${minuti}:${secondi}`;
return dataOraString;
}  // Fine function unixTimestampToDateTime


function cToUnixTime(dateString) {  // Converte il formato data AAAA-MM-GG in uno UNIXTIMESTAMP
   const date = new Date(dateString);
   const unixTimestamp = Math.floor(date.getTime() / 1000);
return unixTimestamp;
}  // Fine function cToUnixTime


function getCurrentDate() {  // dalla data corrente restituisce AAAA-MM-GG
   const date = new Date();
   const day = String(date.getDate()).padStart(2, '0');
   const month = String(date.getMonth() + 1).padStart(2, '0'); // getMonth() restituisce i mesi da 0 (gennaio) a 11 (dicembre)
   const year = date.getFullYear();

return `${year}-${month}-${day}`;
}  // fine function getCurrentDate

// Inizio sezione dedicata solo all'ADMIN +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

// SEZIONE gestione notifica nuovo carrello ------------------------------
$.data(window, 'notifica_browser', false);
$.data(window, 'u_timestamp', 0);  // Setup unixtimestamp del carrello iniziale

// following makes an AJAX call to PHP to get notification in secondi 1000 * XX
   setInterval(function() { pushNotify(); }, 1000 * <?PHP echo TIMEOUT_CHECK_NUOVO_CARRELLO; ?>);

function pushNotify() {
    if (!('Notification' in window)) {  // checking if the user's browser supports web push Notification
        alert('Il tuo browser non supporta le notifiche');
    }
    if (Notification.permission !== 'granted')
        Notification.requestPermission();
    else {
        if(!$.data(window, 'notifica_browser')) {
            $.ajax({
            url: 'checkcarrello.php',
            type: 'POST',
            success: function(data, textStatus, jqXHR) {
                // if PHP call returns data process it and show notification
                // if nothing returns then it means no notification available for now
                if ($.trim(data)) {
                    var data = $.parseJSON(data);
                    if(0 != $.data(window, 'u_timestamp') && data.idn > $.data(window, 'u_timestamp')) {   // Il primo check non deve aprire la notifica ma settare lo u_timestamp
                            const notifica_HR = createNotification(data.title, data.icon, data.body, data.url);  // Crea la notifica
                            $.data(window, 'notifica_browser', true);
                    }
                     $.data(window, 'u_timestamp', data.idn);
                }  // Fine if trim.data
            },
            error: function(jqXHR, textStatus, errorThrown) { }
            });
       } // Fine if notifica chiusa
    }  // Fine else notifica accettata
};

function createNotification(title, icon, body, url) {
    const notification = new Notification(title, {
        icon: icon,
        body: body,
        requireInteraction: true,
        duration: (1000 * 60 * 120)     // chiude automaticamente la notifica del browser web dopo 2 ore
    });
 // URL che deve essere aperto facendo clic sulla notifica
 // infine tutto si riduce a fare clic e visitare correttamente
    notification.onclick = function(event) {
        event.preventDefault();
        $("DIV[data-sc='carrelli']").trigger('click');   // Vado alla lista dei carrelli
    };
    notification.onclose = function() {
        $.data(window, 'notifica_browser', false);
        smista_opzione();
    };

return notification;
};

// Fine SEZIONE gestione notifica ------------------------------------

function carica_quantita(id) {  // Riceve l'id sull'evento onchange dal campo di input della quantita di articolo arrivata nell'ordine di carico
 // Verifica delle quantita'

 if(!$('#ordart-quantita-' + id + '-edit').length || !$('#QT19_' + id).length) {
     alert('Elementi mancanti nell applicazione !');
     return false;
 }

 if($('#ordart-quantita-' + id + '-edit').val() != $('#QT19_' + id).val()) {
    alert("Le quantit\u00E0 ordinata e caricata devono corrispondere !\nSe \u00E8 arrivata una quantit\u00E0 di articolo inferiore diminuire la quantit\u00E0 ordinata.");
    return false;
 }

 stato_aggiornamento(true, '');   // Avviso utente attessa e async ajax false
    $.post('./crud.php', {'tbl_nome':'ordart', 'tbl_azione':'caricaquantita', 'id_record':id, 'valore': $('#QT19_' +id).val()}, function(data) {
         stato_aggiornamento(false, '');  // Chiude la finestra modale
         if(1 == data)
             alert("Errore:\n" + data + "\nmancato caricamento della quantit\u00E0 ricevuta");
    }).done(function() {
    });
}  // fine function carica_quantita


function conferma_articoli_caratt(obj, val) { // riceve l'oggetto checkbox di input ed il numero corrispondente all'id del carrello
    if($(obj).is(':checked'))
       $(obj).prop('disabled', true);

    stato_aggiornamento(true, '');   // Avviso utente attesa e async ajax false
    $.post('./crud.php', {'tbl_nome':'carrellia', 'tbl_azione':'confermacar_a', 'id_record':val, 'valore':($(obj).is(':checked') ? 1 : 0)}, function(data) {
         stato_aggiornamento(false, '');  // Chiude la finestra modale
         if(isNaN(data))
             alert("Errore:\n" + data + "\nmancata conferma degli articoli nel carrello !");
    }).done(function() {
         smista_opzione();
    });
return false;  // stop bubbling event
}  // fine function conferma_articoli_caratt


function associaid(id, valore, tipo) {
  if(tipo == 'PROD') {   // Associazione del prodotto all'articolo
    stato_aggiornamento(true, '');   // Avviso utente attessa e async ajax false
    $.post('./crud.php', {'tbl_nome':'articoli', 'tbl_azione':'associa', 'id_record':id, 'valore': valore}, function(data) {
         stato_aggiornamento(false, '');  // Chiude la finestra modale
         if(isNaN(data))
             alert("Errore:\n" + data + "\nmancata associazione del prodotto");
    }).done(function() {

 // Aggiornamento del campo nome prodotto nella tabella con l'elenco degli articoli inseriti nel preventivo
      if($("TD[id='art-calcolato6-" + id + "-campocalcolato']").length) {
            $("TD[id='art-calcolato6-" + id + "-campocalcolato']").empty().load('./crud.php', {'id_input': 'art-calcolato6-' + id + '-campocalcolato', 'tbl_azione': 'calcola'}, function(responseTxt, statusTxt, xhr){
               if(statusTxt == 'error') {
                   stato_aggiornamento(false, xhr.status + ':<BR />' + xhr.statusText);
               }
            });  // fine load
      } // Fine if aggiorna campo calcolato
    });  // Fine DONE
  }  // Fine if PROD

  if(tipo == 'ART') {   // Associazione dell' articolo al carrello ordini
    // in questo caso valore contine l'ID del controllo checkbox e' devo fare due operazione (se selezionato inserisco l'articolo nel carrello - altrimenti viceversa
    stato_aggiornamento(true, '');   // Avviso utente attessa e async ajax false
    $.post('./crud.php', {'tbl_nome':'ordineart', 'tbl_azione': ($('#' + valore).is(':checked') ? 'associa' : 'dissocia'), 'id_record':id, 'valore': valore.split('-')[1]}, function(data) {
         stato_aggiornamento(false, '');  // Chiude la finestra modale
         if(isNaN(data))
             alert("Errore:\n" + data + "\nmancata dissociazione dell\' articolo all\' ordine");
    }).done(function() {
    });
  }  // Fine if ART
} // Fine function associaid()


function gestione_messaggio(id, tipo) {   // puo' essere APRI o INVIA nel tipo
    if(!$('#form_invio_msg').length) $('#id_stato_aggioramento').empty();  // Svuoto il contenuto del DIV che contiene il FORM

    if(tipo == 'INVIA') {
      let uscita = false;
      $("#form_invio_msg").find('[pattern]').filter(function() {
          if(!uscita && $.type($(this).attr('pattern')) === 'string' && $(this).attr('pattern').length > 0 && test_se_regex($(this).attr('pattern'))) {    // patternMismatch HTML 5
               if(($(this).prop('nodeName') != 'TEXTAREA' && $(this).prop('validity').patternMismatch) || ($(this).prop('nodeName') == 'TEXTAREA' && $(this).val().length < 4)) {
                   alert($(this).attr('title'));
                   uscita = true;
               }
          }  // fine if test()
      });
      if(uscita)
        return false;
    }  // Fine if INVIA

    toggleModal();  // Apro o chiudo la finestra modale
    $('#id_stato_aggioramento').load('./crud.php', {'tbl_nome':tipo, 'tbl_id_div':id, 'tbl_azione':'emessaggio', dati: ($('#form_invio_msg').length ? $('#form_invio_msg').serialize() : '')}, function(responseTxt, statusTxt, xhr){
        if(statusTxt == 'error')  // Errore server o protocollo AJAX
           stato_aggiornamento(false, xhr.status + ':<BR />' + xhr.statusText);
        else {
           if(tipo == 'INVIA') {
              if(responseTxt == 0) // Errore
                 alert('Errore di invio del messaggio !');
              if(responseTxt.length > 2)
                 alert(responseTxt);  // Emissione della conferma dell'invio
           }  // Fine if 'INVIA'
        }
    });  // Fine function load calendario

return false;
}  // fine function gestione_messaggio


function valida_date_rapporti() {  // Prima di chiedere il rapporto valido le date selezionate  - usata da genera rapporto e genera_grafico
  if(!create_Date($('#data_inizio_r').val()) || !create_Date($('#data_fine_r').val())) {
      alert('Intervallo date non valido !');
  return false;
  }
  if(cToUnixTime($('#data_inizio_r').val()) > cToUnixTime($('#data_fine_r').val())) {
      alert('La data di partenza deve essere minore o uguale alla data di fine !');
  return false;
  }
  else {
      $.data(window, 'anno_richiesta', cToUnixTime($('#data_inizio_r').val()) + '_' + cToUnixTime($('#data_fine_r').val()));
  return true;
  }
}   // fine function valida_date_rapporti


function genera_rapporto() {   // Genera i rapporti relativi ai consumi ed alle spese sostenute per la contabilita'
  if(valida_date_rapporti()) {
       if(objTabelle.IsTblLoad($.data(window, 'tipo_richiesta')))
           objTabelle.ScaricaTabella($.data(window, 'tipo_richiesta'));
       objTabelle.CaricaTabella('corpo_tabella_listarichieste', $.data(window, 'tipo_richiesta'));
  }
return false;
}  // Fine function genera rapporto


function genera_grafico_js(id, tipo) {   // Genero nella finestra modale con il grafico
if(!valida_date_rapporti())
    return false;

toggleModal();  // Apro/chiudo la finestra modale
    $('#id_stato_aggioramento').empty().load('./crud.php', {'tbl_nome':tipo, 'tbl_id_div':id, 'tbl_azione':'gengrafico', 'lista_richieste_tabella':parametri_selezione_menu()}, function(responseTxt, statusTxt, xhr){
        if(statusTxt == 'error')
           stato_aggiornamento(false, xhr.status + ':<BR />' + xhr.statusText);
        else {
           $.noop();
        }
    });  // Fine function load calendario
}  // Fine function apri_calendario


function stampa_ordine(id) {  // Genera il PDF con le informazioni riguardanti l' ordine (id == id_ordine)
   stampa_richiesta('tipo=ordine&tbl_id=' + id);
}   // fine function gestione_messaggio


function stampa_prev(id) {  // Genera il PDF con le informazioni riguardanti il carrello (id == id_preventivo)
   stampa_richiesta('tipo=preventivo&tbl_id=' + id);
}   // fine function stampa_prev


jQuery.cachedScript = function(url, options ) {
 // Allow user to set any option except for dataType, cache, and url
  options = $.extend( options || {}, {
    dataType: "script",
    cache: true,
    url: url
  });
// Use $.ajax() since it is more flexible than $.getScript
// Return the jqXHR object so we can chain callbacks
return jQuery.ajax( options );
};

// Fine funzioni dedicate all'ADMIN ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

</SCRIPT>

<?PHP
}  // fine if verifica sessione SSO
?>

</HEAD>
<BODY translate="no">

<DIV class="div_riga" style="background-color:white;"> <!-- inizio div riga intestazione -->
  <DIV class="div_colonna_sx"><IMG src="./imma/logo_ticket.jpg" alt="LOGO_TICKET_DISC" border="0" /></DIV>
  <DIV class="div_colonna_cx"><H1 id="titolo_h1">GESTIONALE MAGAZZINO PRODOTTI CHIMICI</H1></DIV>

<?PHP
if(isset($_SESSION['GAUTH_mail'])) {  // produzione logo uscita -- esiste solo se in server di produzione con autenticazione google gmail
?>
<!-- produzione logo -->
<DIV class="div_colonna_logout">
<DIV id="esci_id_signout">
  <SPAN class="user_cognome_span"><?PHP echo $flag_tipo_utente_autenticato.'<BR />'.$sso_appticket_cognome; ?></SPAN><IMG style="float:right;" src="./imma/sign-out.png" onclick="<?PHP echo $sso_appticket_logout; ?>" width="80" height="80" alt="log_out" />
</DIV></DIV>
<!-- fine produzione logo -->
<?PHP
} // fine if produzione logo uscita
?>

</DIV> <!-- fine div riga intestazione -->

<?PHP
if($enable_sso_auth) {
?>

<DIV class="div_riga"> <!-- inizio div riga parte body -->
<DIV class="div_colonna_sx"> <!-- inizio div colonna menu -->
<BR /><P class="classe_tag_p">Visualizzazione:</P>
<DIV class="blocco_menu"> <!-- Inizio DIV blocco visualizzazione -->
  <DIV class="item_menu item_click" data-sc="HOME" data-ser="servizio_richiesto" onclick="smista_opzione(this);">==<B>></B>&nbsp;HOME&nbsp;PAGE&nbsp;<B><</B>==</DIV>
  <DIV class="item_menu" data-sc="carrelli" data-ser="servizio_richiesto" onclick="smista_opzione(this);">Lista carrelli</DIV>
  <DIV class="item_menu" data-sc="nuovocarrello" data-ser="servizio_richiesto" onclick="smista_opzione(this);">Nuovo carrello</DIV>
  <DIV class="item_menu" data-sc="prodotti" data-ser="servizio_richiesto" onclick="smista_opzione(this);">Prodotti e articoli</DIV>
  <?PHP if($app_admin) { // Solo gli admin ?>
     <DIV class="item_menu" data-sc="fornitori" data-ser="servizio_richiesto" onclick="smista_opzione(this);">Fornitori e preventivi</DIV>
     <DIV class="item_menu" data-sc="ordini" data-ser="servizio_richiesto" onclick="smista_opzione(this);">Ordini e carico</DIV>
     <DIV class="item_menu" data-sc="voci" data-ser="servizio_richiesto" onclick="smista_opzione(this);">Voci menu</DIV>
  <?PHP } // Fine if $app_admin ?>
  <DIV class="item_menu" data-sc="utenti" data-ser="servizio_richiesto" onclick="smista_opzione(this);">Gruppi e utenti</DIV>
  <DIV class="item_menu" data-sc="rapporti" data-ser="servizio_richiesto" onclick="smista_opzione(this);">Report</DIV>
</DIV> <!-- Fine DIV blocco visualizzazione --><BR /><BR />

<SPAN id="div_blocco_report"> <!-- Inizio blocco classe menu -->
<P class="classe_tag_p">Tipo report:</P>
<DIV class="blocco_menu"> <!-- Inizio blocco classe menu -->
  <DIV class="item_menu item_click" data-sc="spesaxgruppo" data-ser="tipo_report" onclick="smista_opzione(this);">Spese per gruppo</DIV>
  <DIV class="item_menu" data-sc="spesadettaglio" data-ser="tipo_report" onclick="smista_opzione(this);">Spese dettagliate</DIV>
  <?PHP if($app_admin) { // Solo gli admin ?>
     <DIV class="item_menu" data-sc="proddeposito" data-ser="tipo_report" onclick="smista_opzione(this);">Prodotti in deposito</DIV>
     <DIV class="item_menu" data-sc="artdeposito" data-ser="tipo_report" onclick="smista_opzione(this);">Articoli in deposito</DIV>
     <DIV class="item_menu" data-sc="artvalore" data-ser="tipo_report" onclick="smista_opzione(this);">Articoli x valore</DIV>
  <?PHP } // Fine if $app_admin ?>
</DIV> <!-- fine di blocco classe menu -->
</SPAN> <!-- fine span id=div_blocco_report -->

</DIV> <!-- fine div colonna menu sx -->

<DIV class="div_colonna_dx"> <!-- inizio div colonna corpo principale -->
<DIV id="corpo_tabella_richiesta"></DIV> <!-- Finestra centrale di gestione prima -->
<DIV id="corpo_tabella_listarichieste"></DIV> <!-- Finestra centrale di gestione seconda -->
<DIV id="corpo_tabella_listaterza"></DIV> <!-- Finestra centrale di gestione terza -->
</DIV>  <!-- fine div colonna colonna_dx -->
</DIV> <!-- fine div riga -->

<?PHP
 }  // Fine IF body in caso di autenticazione avvenuta con successo o esecuzione in localhost
else {  // Emissione logo google per autenticazione dopo if enable sso
?>
  <BR /><BR /><BR /><BR ><BR />

<?PHP
 echo (Login_User_Class::genera_login((!$enable_sso_auth && isset($_REQUEST['frm_email_login'])), (isset($esito_recupero_pwd) ? $esito_recupero_pwd : '')));  // TRUE = failed login
 }  // Fine else emissione bottone google o se in manutenzione, o autenticazione con username - password
?>

<BR /><BR /><BR /><FOOTER class="footer_informatico"><DIV>Per assistenza tecnica rivolgersi al referente informatico&nbsp;<A href="mailto:nonesiste_email@unipd.it">nonesiste_email@unipd.it</A>&nbsp;&nbsp;&nbsp;telefono&nbsp;+39-049.827.XXXX</DIV></FOOTER>
</BODY>
</HTML>