<?PHP   // michele.furlan@unipd.it - 30 gennaio 2024

class Login_User_Class {

static public function genera_login($fail = FALSE, $esito_recupero_pwd = '') {
static $html_login = '<DIV class="google_conternitore" style="text-align:center;">  <!-- Inizio div google contenitore o autenticazione con username o password -->';

 if(SITO_IN_MANUTENZIONE) {
    $html_login .= '<H1>SITO IN MANUTENZIONE</H1>';
 }
 elseif(LOGIN_USER_FORM) {

$html_login .= <<<HTMLE
<STYLE type="text/css">
TABLE.login_table {font-family:Verdana,San-serif;font-size:10pt;color:#000000;background-color:#9696A6;margin-left:auto;margin-right:auto;
        border-style:solid;border-color:#484F59;border-width:2px;border-collapse:separate;border-spacing:2px;
       -webkit-border-radius:12px;-moz-border-radius:12px;
        border-radius:12px;-webkit-box-shadow:4px 4px 5px 0px rgba(82,78,75,1);
       -moz-box-shadow:4px 4px 5px 0px rgba(82,78,75,1);box-shadow:4px 4px 5px 0px rgba(82,78,75,1);
}

TABLE.login_table TD {font-size:12pt;background-color:#FEFEFF;min-width:50px;padding:8px;border-style:solid;font-family:Arial;}
TABLE.login_table CAPTION {white-space:normal;padding:10px;}
TABLE.login_table TH {background-color:#FEFEFF;padding:8px;}
P.p_centro {text-align:center;}
.c_N  {font-family:Tahoma,San-serif;border-radius:10%;box-shadow: 0 0 3px gray;
       font-size:14pt;text-align:center;color:#000000;border-style:outset;
       border-color:#00f000;border-width:4px;background-color:#dfffdf; }


</STYLE>
  <h3 align="center">Autenticazione utente</h3>
  <FORM id="frm_form_login" method="post" action="./index.php" target="_top">
   <table class="login_table">%FALLITA%
   <tr><td align="right">&nbsp;&nbsp;<b>username</b>:&nbsp;<input class="blue-input" type="email" size="40" id="frm_email_login" name="frm_email_login" maxlength="100" value="" required placeholder="nome.cognome@xxxxunipd.it" title="Email" /></td></tr>
   <tr><td align="right">&nbsp;&nbsp;<b>password</b>:&nbsp;<input class="blue-input" type="password" size="40" id="frm_password_login" name="frm_password_login" maxlength="20" value="" pattern="^.{4,50}$" required placeholder="da 4 a 50 caratteri" title="Password" />
   <input type="hidden" id="frm_requestpwd_login" name="frm_requestpwd_login" value="FALSE" /></td></tr>
   <tr><td align="center"><INPUT id="frm_form_submit" type="submit" class="c_N" value="&nbsp;&nbsp;L O G I N&nbsp;&nbsp;" /></td></tr>
  </table></FORM>
<BR /><BR />
<P class="p_centro">Password smarrita ?&nbsp;&nbsp;<BUTTON class="c_N" onclick="$('#frm_requestpwd_login').val('TRUE');$('#frm_password_login').val('TEST');$('#frm_form_login').submit();">&nbsp;&nbsp;RICHIEDI LA PASSWORD&nbsp;&nbsp;</BUTTON></P>
\n
HTMLE;

$html_login = str_replace('%FALLITA%', ($esito_recupero_pwd ? '<CAPTION>'.$esito_recupero_pwd.'</CAPTION>' : ($fail ? '<CAPTION>Autenticazione fallita !</CAPTION>' : '')), $html_login);
 }

else {
$html_login .= <<<HTMLG
<SCRIPT type="text/javascript">
 function verifica_tocken_backend(risposta) {
    $("BODY").append('<DIV class="lds-spinner"><DIV></DIV><DIV></DIV><DIV></DIV><DIV></DIV><DIV></DIV><DIV></DIV><DIV></DIV><DIV></DIV><DIV></DIV><DIV></DIV><DIV></DIV><DIV></DIV></DIV>');
      $('#successo_1').toggleClass('courier_class pulsante_ord').html('&nbsp;&nbsp;Verifica account in corso....attendere qualche secondo&nbsp;&nbsp;');
       var jqXHR = $.post('./oauth2/check_oauth.php', {'id_token' : risposta.credential}, function(data) {
                       $("DIV").remove(".lds-spinner");
                       err = data;
       }).done(function(dati, stato_ritorno, jqXHRinfo) {
       if(dati == 'NO' ) {  // Impostare una pagina di ritorno dopo l'alert del fallimento
               alert('Autenticazione backend fallita !');
               location.href = 'https://wwwdisc.chimica.unipd.it/approvvigionamento/';
       }
       else if(dati == 'UN') {
               alert('Sono ammessi solo utenti nel dominio email UNIPD.IT');
               location.href = 'https://www.google.com/accounts/Logout';  // Per evitare il loop forzo il logout
       }  // No UNIPD user
       else
             var link_ticket = $("<A id='link_ticket' href='" + encodeURI("https://wwwdisc.chimica.unipd.it/approvvigionamento/index.php?GAUTH_fullname=" + dati) + "' target='_top'></A>");
             $('BODY').append(link_ticket);
             setTimeout(function() {\$('#link_ticket').get(0).click();}, 200); // #top per aprire sopra iframe se presente
       }).fail(function(xhr, err) {  // ajax fallito
               alert('Errore autenticazione tipo: ' + err);
       });

 }  // Fine function verifica_tocken_backend
</SCRIPT>

  <H3 style="font-family:Verdana;">Richiesta autenticazione SSO via Gmail, o SPID via UNIPD.IT</H3>
  <H4>(sono autorizzati solo gli indirizzi e-mail nel dominio UNIPD.IT)</H4><BR />
<!-- Inizio GOOGLE BOTTONE -->
  <DIV id="g_id_onload" data-client_id="569528394920-v95ge5735cva7sgoesk0a4vi61jr71ns.apps.googleusercontent.com" data-context="use" data-ux_mode="popup" data-callback="verifica_tocken_backend" data-nonce="" data-auto_select="true" data-close_on_tap_outside="false">
  </DIV>
  <DIV id="successo_1" name="successo_1" style="padding:15px;" class="g_id_signin" data-type="standard" data-shape="rectangular" data-theme="filled_blue" data-text="signin_with" data-size="large" data-locale="en" data-logo_alignment="left" data-width="300">
  </DIV>
<!-- Fine div GOOGLE BOTTONE -->
\n
HTMLG;
 }

return $html_login.'</DIV> <!-- fine div google contenitore -->';
}  // Fine function genera_login

} // Fine classe login
?>