<?PHP   // michele.furlan@unipd.it    03 novembre 2022
// lo schema di autenticazione prevede che via $.post ajax si riceve il token l 'id client e lo user_id
// se dopo la verifica del token lo user id ritornato da gooogle corrisponde allo user id inviato via $.post
// allora si ha la conferma che l'utente e' cio' che dice di essere
// https://gf.dev/secure-cookie-test
// https://geekflare.com/httponly-secure-cookie-apache/
// Esempio PHP https://hotexamples.com/it/examples/-/Google_Client/verifyIdToken/php-google_client-verifyidtoken-method-examples.html
/*  Nel $payload 
array (
  'iss' => 'https://accounts.google.com',
  'nbf' => 1658992572,
  'aud' => '569528394920-v95ge5735cva7sgoesk0a4vi61jr71ns.apps.googleusercontent.com',
  'sub' => '112424741778171297134',
  'hd' => 'unipd.it',
  'email' => 'michele.furlan@unipd.it',
  'email_verified' => true,
  'azp' => '569528394920-v95ge5735cva7sgoesk0a4vi61jr71ns.apps.googleusercontent.com',
  'name' => 'Michele Furlan',
  'picture' => 'https://lh3.googleusercontent.com/a/AItbvmmeNGCYFlnu-tvXy5ZO208cYvFX4rZk7WWQFW6S=s96-c',
  'given_name' => 'Michele',
  'family_name' => 'Furlan',
  'iat' => 1658992872,
  'exp' => 1658996472,
  'jti' => '7256f6d616fa8837945f619bb3ff795783fdfb1d',
)
*/

if(session_status() !== PHP_SESSION_ACTIVE) session_start();
ob_start();
date_default_timezone_set('Europe/Rome');
require_once './google-api-php-client/vendor/autoload.php';   // Necessario per il caricamento della libreria di google di autenticazione
define('GOOGLE_CLIENT_ID_CODICE', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX1ns.apps.googleusercontent.com', TRUE);

function registra_accesso($str) {   // Solo per debug applicazione
   if(is_writable(getcwd())) {
        $handle = @fopen('./lista_autenticazioni.txt', 'a');
        @fwrite($handle, date('Y-F-d H:i:s l').";$str\r\n");
        @fclose($handle);
   } 
}  // fine funzione registra_accesso


function get_client_geo_ip() {
  $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
        $ipaddress = 'UNKNOWN';
    }
  $json = @json_decode(@file_get_contents("http://ipinfo.io/$ipaddress/geo"), true);

return $json ? 'STATO_REGIONE_CITTA: '.$json['country'].'_'.$json['region'].'_'.$json['city'].';' : 'STATO_REGIONE_CITTA: FAILURE;';
} // Fine function get_client_geo_ip


$log_utente = 'REMOTE_ADDRESS: '.$_SERVER['REMOTE_ADDR'].';HTTP_AGENT: '.$_SERVER['HTTP_USER_AGENT'];  // Registrazione testuale dei log utenti

if(isset($_REQUEST['id_token'])) {  // verifica lato server
 
  try {
     $client = new Google_Client(['client_id' => GOOGLE_CLIENT_ID_CODICE]);  // Specify the CLIENT_ID of the app that accesses the backend
// $client->setRedirectUri('https://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
     $payload = $client->verifyIdToken($_REQUEST['id_token']);
  }
  catch(Exception $e) {
      $payload = FALSE;
      $id_c = 'Errore IdToken: '.$e->getMessage();
  }
  if ($payload) {
	  // Verifica se utente @unipd.it
	  if(0 == strncasecmp('unipd.it', substr($payload['email'], -8), strlen('unipd.it'))) {  //  oppure  "unipd.it" == $payload['hd']
		  $log_utente = 'LOGIN_OK;'.$log_utente;
	      $_SESSION['GAUTH_mail'] = $payload['email']; // $_REQUEST['user_email'];   // Imposta la sessione
	      $_SESSION['GAUTH_fullname'] = $payload['name'];
		  $_SESSION['LAST_ACTIVITY'] = time();   // usata per controllare la scadenza della sesssione corrente
	      $id_c = $payload['sub'];
	      echo $_SESSION['GAUTH_fullname'];  // Successo !
	  }
	  else {
		  $id_c = "UN";  // No UNIPD USER
	      echo $id_c;
	  }	  
  } else { // Invalid ID token - fallita varifica del token, o token malformato
     $log_utente = "LOGIN_ERROR;".$log_utente;
	 echo "NO";
  }

 $log_utente = "USER_EMAIL: ".(isset($_SESSION['GAUTH_mail']) ? $_SESSION['GAUTH_mail'] : 'NO_OAUTH2-'.(isset($payload['email']) ? $payload['email'] : "" )).";ID_CLIENT_GOOGLE: $id_c;".$log_utente;
 registra_accesso($log_utente);    // Oppure registra_accesso(get_client_geo_ip().$log_utente);
}  // Fine if id_token

else {
  if(isset($_REQUEST['user_logout_dott']) AND isset($_SESSION['GAUTH_mail']))  { // sono entrato per logout

     registra_accesso('LOGOUT_OK;USER_EMAIL: '.$_SESSION['GAUTH_mail'].';'.$log_utente);
     $cookiesArray = array();
          foreach ($_COOKIE as $key => $val) {   // Eliminazione di tutti i cookie della sessione
              $cookiesArray[] = $key;
          }
          if (!empty($_COOKIE)) {
              foreach ($_COOKIE as $name => $value) {
                  if (in_array($name, $cookiesArray)){
                       continue;
                  }
                  setcookie($name, $value, time() -1);
              }
          }
     session_regenerate_id();
	 session_unset(); // rispettare l'ordine unset e destroy
     session_destroy(); 
  }  // Fine if  $_REQUEST['user_logout_dott'] 
	
  else { 	
	   registra_accesso('RICHIESTA MALFORMATA;'.$log_utente); 
	   echo 'NO'; 
  }	   
}  // Fine else id_token	

ob_end_flush();
?>