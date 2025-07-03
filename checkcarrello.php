<?PHP   // michele.furlan@unipd.it   22 gennaio 2024
/*
https://phppot.com/php/web-push-notifications-php/
enable this if you want to make only one call and not repeated calls automatically
pushNotify();
https://developer.mozilla.org/en-US/docs/Web/API/Notification/actions
https://stackoverflow.com/questions/35695067/how-to-increase-chrome-push-notification-visibility-time
https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events/Using_server-sent_events
*/

include ('./conn.php');

if($_SERVER['SERVER_NAME'] == 'wwwdisc.chimica.unipd.it')
   $conn_obj = new Connessione('localhost', 'approvvigionamento', 'apptabelle', 'apptab2019');     // Creazione della obj connessione -- HOSTNAME - database - username - password 
else
   $conn_obj = new Connessione('localhost', 'magazzino', 'michele', 'quicker');     // Creazione della obj connessione -- HOSTNAME - database - username - password

$result = $conn_obj->sql_query('SELECT username, IFNULL(UNIX_TIMESTAMP(MAX(datainserimento)), 0) AS TEMPO FROM carrelli WHERE id_sessione = 0 LIMIT 1');

if(!$conn_obj->errore) {
    $row = $result->fetch_row();
    $unixt = $row[1];
    $nome_utente = $unixt ? $row[0] : 'nessun_carrello_nuovo';
    mysqli_free_result($result);

// if there is anything to notify, then return the response with data for
// push notification else just exit the code
   $webNotificationPayload['title'] = 'E\' stato inserito un nuovo carrello.';
   $webNotificationPayload['body'] = 'Da: '.$nome_utente;
   $webNotificationPayload['idn'] =  $unixt;
}

else {
   $webNotificationPayload['title'] = 'Errore di estrazione nuovi carrelli.';
   $webNotificationPayload['body'] = 'ADMIN approvvigionamento';
   $webNotificationPayload['idn'] = 0;
}

$webNotificationPayload['icon'] = 'https://wwwdisc.chimica.unipd.it/approvvigionamento/imma/logo_notifica.png';
$webNotificationPayload['url'] = 'https://wwwdisc.chimica.unipd.it/approvvigionamento';

echo json_encode($webNotificationPayload);
exit();
?>