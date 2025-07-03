<?PHP   // michele.furlan@unipd.it   11 aprile 2024
require_once('./config.php');
// ricevo in $_REQUEST['tbl_id_div'] in forma $tabella_richiesta."-".$campo."-".$row["id"]."-listafile'
$str = '';

if(isset($_REQUEST['tbl_id_div'])) {
  $obj = explode('-', $_REQUEST['tbl_id_div']);   // tbl_id_div lo ricevo dallo script sopra nel campo data: {} in $obj[1]  nome del campo in tabella
  $nome_tabella_nel_config = check_alias_table($obj[0]) ? check_alias_table($obj[0]) : $obj[0];  // in obj[0] nome tabella in obj[1] nome del campo
   if(count($obj) > 1 AND isset($tabella[$nome_tabella_nel_config]['campi'][$obj[1]]['attributi']['cartella_upload'])) {
        $cartella_upload_result = $tabella[$nome_tabella_nel_config]['campi'][$obj[1]]['attributi']['cartella_upload'];
        $cartella_upload = dirname($_SERVER['SCRIPT_FILENAME']).(DIRECTORY_SEPARATOR).$cartella_upload_result;  // $cartella_upload serve solo per l'upload dei file
   }
}
else
  return '<BR />Errore setting mancante o autorizzazione negata';

function emissione_form() {
?>
<SCRIPT type="text/javascript" src="./js/jquery.form.min.js"></SCRIPT>
<SCRIPT type="text/javascript">
 function test_file_presente(obj) {   // impedisco l' invio a vuoto di file non esistente
     if(($(obj).val().length >2)) {   // Il nome file almeno di due caratteri
         // Validazione lato client della dimensione massima consentita del file
         if($.isNumeric(obj.files[0].size) && obj.files[0].size >= <?PHP echo MASSIMA_DIMENSIONE_FILE; ?>) {
               alert("Il file ha dimensione di " + Math.floor(obj.files[0].size/Math.pow(2,20))
                     + "MB che supera il limite\nmassimo impostato nella configurazione di: "
                     + <?PHP echo "'".human_filesize(MASSIMA_DIMENSIONE_FILE, 0)."'"; ?> +"\n\nINVIO FALLITO !" );
               return false;
         }
     $('#id_invio_form_upload_file').removeAttr('disabled');
	 $('#id_invio_form_upload_file').css('visibility', 'visible');
	 if($('.div_contenitore_upload_testo').length) $('.div_contenitore_upload_testo').empty();
     }
 } // Fine function test_file_presente()

function upload_file_progressbar() {
 var bar = $('#bar1');
 var percent = $('#percent1');

  $('#myForm').ajaxForm({
     beforeSubmit: function() {
      $("#progress_div").css("display", "block");
      var percentVal = '0%';
      bar.width(percentVal);
      percent.html(percentVal);
    },

    data: {tbl_id_div: <?PHP echo "'".$_REQUEST['tbl_id_div']."'"; ?>, 'lista_richieste_tabella':parametri_selezione_menu()},

    uploadProgress: function(event, position, total, percentComplete) {
      var percentVal = percentComplete + '%';
      bar.width(percentVal);
      percent.html(percentVal);
    },

    success: function() {
      var percentVal = '100%';
      bar.width(percentVal);
      percent.html(percentVal);
    },

    complete: function(xhr) {
      if(xhr.responseText) {
          $("#output_file").html(xhr.responseText);
          upload_file(<?PHP echo "'".$_REQUEST['tbl_id_div']."'"; ?>, "uploadedfile", xhr.responseText);
      }
    }
  });   // fine ajaxForm
} // fine upload_file_progressbar()

</SCRIPT>

<STYLE type="text/css" media="all">
#myForm  {
  text-align: center;
  display: block;
  margin: 20px auto;
  background: #eee;
  border-radius: 10px;
  padding: 15px;
}
.progress {
  display: none;
  position: relative;
  width: 100%;
  border: 1px solid #ddd;
  padding: 1px;
  border-radius: 3px;
}
.bar {
  background-color: #94F594;
  width: 0%;
  height: 20px;
  border-radius: 3px;
}
.percent {
  position: absolute;
  display: inline-block;
  top: 3px;
  left: 45%;
}

#id_invio_form_upload_file {
  visibility: hidden;	
}

</STYLE>

<DIV class="div_contenitore_upload_form"> <!-- inizio div contenitore upload_form -->
<SPAN>Dimensione massima del file per l'upload:&nbsp;<B><?PHP echo human_filesize(MASSIMA_DIMENSIONE_FILE, 0); ?></B></SPAN>
<FORM action="./fileupload.php" id="myForm" name="frmupload" method="post" enctype="multipart/form-data">
 <INPUT type="hidden" name="MAX_FILE_SIZE" value="<?php echo MASSIMA_DIMENSIONE_FILE; ?>" />
 <BR />Invia questo file: <INPUT class="pulsante" type="file" id="upload_file" name="upload_file" onchange="test_file_presente(this);" />
 <INPUT id="id_invio_form_upload_file" disabled="disabled" class="pulsante" type="submit" name='submit_file' value="&#10003;&nbsp;INVIA&nbsp;IL&nbsp;FILE" onclick="upload_file_progressbar();" />
</FORM>
<div class='progress' id="progress_div">
<div class='bar' id='bar1'></div>
<div class='percent' id='percent1'>0%</div>
</div>
<div id='output_file'></div>
</DIV> <!-- fine div contenitore upload_form -->

<?PHP
}  // Fine function emissione_form

function human_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

if(!function_exists('mime_content_type'))  { // Mime Type Checker
 function mime_content_type($filename, $mode=0) {
    // mode 0 = full check
    // mode 1 = extension check only
    $mime_types = array(
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/msword',
        'xlsx' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.ms-powerpoint',
        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    $ext = strtolower(array_pop(explode('.',$filename)));

    if (function_exists('finfo_open') && $mode==0) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mimetype;

    } elseif (array_key_exists($ext, $mime_types)) {
        return $mime_types[$ext];
    } else {
        return 'application/octet-stream';
    }
 } // fine function mime_content_type
} // fine function !function_exists('mime

function get_mime_type($file) {
      if(function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file);
        finfo_close($finfo);
      }
      else {
        $mimetype = mime_content_type($file);
      }
      if(empty($mimetype)) $mimetype = 'application/octet-stream';
      return $mimetype;
}  // Fine function  get_mime_type


function deliverFile($file) {   // Invio del file su richieta download

 if (file_exists($file) && ($filehandle = fopen($file, 'rb'))) {

    @header('Last-Modified: '.date('r', filectime($file)));
    @header('Content-Description: File Transfer');
    //Get file type and set it as Content Type
    @header('Content-Type: '.get_mime_type($file));

    //Use Content-Disposition: attachment to specify the filename
    @header('Content-Disposition: attachment; filename="'.basename($file).'"');
    @header('Content-Transfer-Encoding: binary');

    //No cache
    @header('Expires: 0');
    @header('Cache-Control: must-revalidate');
    @header('Pragma: public');

    //Define file size
    @header('Content-Length: '.filesize($file));
    fpassthru($filehandle);
    fclose($filehandle);
 }
 else
     header('HTTP/1.0 404 Not Found');
 exit();
}  // fine function deliverFile

function foldersize($dirname) {
    if (!is_dir($dirname) || !is_readable($dirname)) {
        return false;
    }
    $dirname_stack[] = $dirname;
    $size = 0;
    do {
        $dirname = array_shift($dirname_stack);
        $handle = opendir($dirname);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && is_readable($dirname.(DIRECTORY_SEPARATOR).$file)) {
                if (is_dir($dirname.(DIRECTORY_SEPARATOR).$file)) {
                    $dirname_stack[] = $dirname.(DIRECTORY_SEPARATOR).$file;
                }
                $size += filesize($dirname.(DIRECTORY_SEPARATOR).$file);
            }
        }
        closedir($handle);
    } while (count($dirname_stack) > 0);
return $size;
}  // fine function foldersize()

function cleanData(&$str) {
  $str = preg_replace("/\t/", "\\t", $str); // escape tab
  $str = preg_replace("/\r?\n/", "\\n", $str);    // escape new lines

  if($str == 't') $str = 'TRUE'; // converte 't' e 'f' in boolean
  if($str == 'f') $str = 'FALSE';

  // force certain number/date formats to be imported as strings
  if(preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str)) {
      $str = "'$str";
  }
  // escape fields that include double quotes
  if(strstr($str, '"'))
      $str = '"' . str_replace('"', '""', $str) . '"';
}

if(isset($_REQUEST['submit_file'])) {
  if(!isset($_FILES['upload_file']['name']) OR strlen($_FILES['upload_file']['name']) <2)
     exit();  // Interrompo perche' l'inoltro e' vuoto - non e' stato selezionato il file

  if(!$tabella[$nome_tabella_nel_config]['campi'][$obj[1]]['editable'])  // Verifica editabilita campo file in tabella
     die('<H3>Errore di invio file. Permessi insufficienti per l\'utente</H3><BR />');

  $_FILES['upload_file']['name'] = strtolower(preg_replace('/\s+/', '_', clean_dato($_FILES['upload_file']['name'])));  // normalizzo senza spazi il nuovo file e lettere minuscole per gestione filesystem Linux

     if(file_exists($cartella_upload.(DIRECTORY_SEPARATOR).$_FILES['upload_file']['name']) AND $tabella[$nome_tabella_nel_config]['campi'][$obj[1]]['attributi']['non_duplicabile'])
         $str = '<H3>Il file '.$_FILES['upload_file']['name'].'&nbsp; &egrave; gi&agrave; stato caricato !</H3>';
     else    {
       if(file_exists($cartella_upload.(DIRECTORY_SEPARATOR).$_FILES['upload_file']['name'])) {
           $nome_file_save = pathinfo($_FILES['upload_file']['name'])['filename'];   // Solo il nome senza estensione
           $estensione_file_save = pathinfo($_FILES['upload_file']['name'])['extension'];
           $_FILES['upload_file']['name'] = $nome_file_save;
// Il nome file in corso di upload potrebbe avere prima dell'estensione il nome finale tipo _2020 che si sovrappone al progressivo; devo preservare tale appendice _XXXX numerica
// $progressivo_file = preg_match ("/_\d{1,50}$/", $nome_file_save, $str);  // In str l'array con il match
           $progressivo_file = '_0';
           $nome_file_save .= $progressivo_file;   // Solo il nome

           while(file_exists($cartella_upload.(DIRECTORY_SEPARATOR).$nome_file_save.'.'.$estensione_file_save)) {  // provo a inserire file con numerazione progressiva
                 $progressivo_file = '_'.((int)substr($progressivo_file, 1) + 1);   // avanzamento fino a trovare il primo nome file _XX non presente nel filesystem
                 $nome_file_save = $_FILES['upload_file']['name'].$progressivo_file;
           }
           $_FILES['upload_file']['name'] = $nome_file_save.'.'.$estensione_file_save;

       }  // Fine if file exist
       if($_FILES['upload_file']['size'] > MASSIMA_DIMENSIONE_FILE) {  // check massima dimensione del file
           $str = '<H3>Il file non &egrave; stato inviato perch&egrave; supera la dimensione massima pari a: '.human_filesize(MASSIMA_DIMENSIONE_FILE, 0).'</H3>';
       }
       else {  // Procedo con il caricamento se ne ho la facolta - devo controllare i permessi
       // Verifica della quota riservata agli upload
          if(QUOTA_MASSIMA > (foldersize($cartella_upload) + $_FILES['upload_file']['size'])) {
              if(UPLOAD_ERR_OK == $_FILES['upload_file']['error'] && is_writable($cartella_upload) && move_uploaded_file($_FILES['upload_file']['tmp_name'], $cartella_upload.(DIRECTORY_SEPARATOR).$_FILES['upload_file']['name'])) { // spostamento avvenuto con successo
                     $conn_obj->sql_command('UPDATE '.$tabella[$nome_tabella_nel_config]['nometabella']." SET $obj[1] = '".addslashes($_FILES['upload_file']['name'])."' WHERE id=$obj[2] LIMIT 1");  // LIMIT 1 per sicurezza
                     $str_image_test = test_is_image($tabella, $nome_tabella_nel_config, $obj[1], $_FILES['upload_file']['name'], FALSE);
                      if(!$str_image_test)  // Devo aggiornare il campo nella tabella che richiama la gestione file - vedi in myapptabelle.js upload_file function
                         $str_image_test = '<INPUT class="pulsante_file center_elemento_imma" type="button" value="'.$_FILES['upload_file']['name'].'" />';
                     $str .= '<SCRIPT type="text/javascript">var nomefilejs=`'.$str_image_test.'`;</SCRIPT>';   // Uso del literals in JS6 per racchiudere caratteri ' " multipli
              }
              else {
                   $str = '<H3>Errore di invio file. Codice: '.$_FILES['upload_file']['error']."</H3><BR />\n";
              }
          } // fine if verifica dimensione cartella di upload
          else
             $str = '<H3>E\' stata superata la quota disco disponibile di <B>'.human_filesize(QUOTA_MASSIMA, 0).'</B>. Avvisare l\'amministratore del sistema: '.(isset($email_super_admin_interventi[0]) ? $email_super_admin_interventi[0] : '')."</H3>\n";
      } // Fine else
   }  // fine else if  verifica esistenza del file gia caricato
  echo $str;
  exit();   // interrompo dopo il submit del file
} // isset($_REQUEST['submit_file'])


if(isset($_REQUEST['tbl_id_div']) AND isset($_REQUEST['tipoazione']) AND $_REQUEST['tipoazione'] == 'downloadxls') {
 $output = '';

  if(null === ini_get('upload_tmp_dir') || 0 == strlen(ini_get('upload_tmp_dir')) || !is_writable(ini_get('upload_tmp_dir'))) {   // Verifico presenza cartella temporanea per scrittura file xls
     if(!is_writable(dirname(__FILE__)))
        die('Directory dell\'applicazione non scrivibile: '.dirname(__FILE__));
     else
        $output = dirname(__FILE__);   // Imposto come cartella scrivibile la stessa cartella di esecuzione dell'applicazione
  }  // Fine if check directory temporanea
  else
     $output = ini_get('upload_tmp_dir');

  if(in_array($_REQUEST['tbl_id_div'], $rpt['tabelle'])) {  // Occorre generare le tabelle dei rapporti perche' sono tabelle temporanee non presenti nel DB
       $_REQUEST['tbl_nome'] = $_REQUEST['tbl_id_div'];     // Vedi switch in rapporti.php
       require_once('./rapporti.php');
  }

 $filecsvnome = $output.(DIRECTORY_SEPARATOR).'fileexcel'.'_'.$tabella[$_REQUEST['tbl_id_div']]['nometabella'].'.xls';  // Percorso assoluto
 if(file_exists($filecsvnome)) unlink($filecsvnome);  // Cancello il file xls presistente

  if(!$handle = fopen($filecsvnome, 'w'))
      die('Non si riesce ad aprire il file: '.$filecsvnome.' in directory: '.ini_get('upload_tmp_dir'));
  else
      $output = '';

  if(!$app_admin)   // Solo gli admin possono scaricare gli excel
       $result = &$conn_obj->sql_query("SELECT CONCAT('NON_HAI_I_PERMESSI_DI_ADMIN') AS DIRITTI_INSUFFICIENTI");  // & passaggio per riferimento == puntatore
  else
       $result = &$conn_obj->sql_query('SELECT * FROM '.$tabella[$_REQUEST['tbl_id_div']]['nometabella'].' ORDER BY ID');

   $flag = false;
   if($result)
      while($row = $result->fetch_assoc()) {
         if(!$flag) { // Prima riga con nomi delle colonne
             $output .= implode("\t", array_keys($row)) . "\r\n";
             $flag = true;
         }
       array_walk($row, 'cleanData');
       $output .= implode("\t", array_values($row)) . "\r\n";
     } // Fine while
   else
      $output = 'Errore query: '.$conn_obj->errore;

    mysqli_free_result($result);
    fwrite($handle, $output);
    fclose($handle);
    deliverFile($filecsvnome);

exit();
} // Fine if processo invio file CSV

// $tabella_richiesta."-".$campo."-".$row["id"]."-listafile'  in tbl_id_div
if(isset($_REQUEST['tbl_id_div'])) {   // ho ricevuto il comando di gestione file - tipo azioni tipo == listafile - uploadedfile - removefile - sendfile
  $is_alias_table = check_alias_table($obj[0]) ? true : false;
  $obj_norm = $nome_tabella_nel_config.'-'.$obj[1].'-'.$obj[2].'-'.$obj[3]; // Per aggiornare il campo in tabella
  $result = $conn_obj->sql_query('SELECT '.$obj[1].' FROM '.$tabella[$nome_tabella_nel_config]['nometabella'].' WHERE id='.$obj[2].' LIMIT 1');
  $nomefile = $result->fetch_assoc();
  $nomefile = stripslashes(trim($nomefile[$obj[1]]));
  $nomefile_full = $cartella_upload.(DIRECTORY_SEPARATOR).$nomefile;
  $nomefile_status_span = '<INPUT class="pulsante_file center_elemento_imma" type="button" value="&#x1F4CE;&nbsp;UPLOAD FILE" />';   // Valore default se manca il file
  mysqli_free_result($result);
    if(strlen($nomefile)) {
       if(file_exists($nomefile_full)) {  // test se il file esiste nel file system
          if(isset($_REQUEST['tipoazione']) AND $_REQUEST['tipoazione'] == 'sendfile')  // E' stato chiesto di ricevere il file
              deliverFile($nomefile_full);  // Content-Encoding: gzip - Content-Encoding: compress - Content-Encoding: deflate - Content-Encoding: identity - Content-Encoding: br
          elseif(isset($_REQUEST['tipoazione']) AND $_REQUEST['tipoazione'] == 'removefile')  {   // Rimozione del file
               if(!$tabella[$nome_tabella_nel_config]['campi'][$obj[1]]['editable'])  // Verifica editabilita campo file in tabella
                   $str .= '<H3>Errore di rimozione del file: '.$nomefile.'. Permessi insufficienti per l\'utente</H3><BR />';
               elseif(unlink($nomefile_full)) {
                  // Rimuovo anche il path del file dal database
                   $conn_obj->sql_command('UPDATE '.$tabella[$nome_tabella_nel_config]['nometabella'].' SET '.$obj[1]." = '' WHERE id=".$obj[2].' LIMIT 1');     // LIMIT 1 per sicurezza
                   $str .= 'Il file <B>'.$nomefile.'</B> &egrave; stato rimosso !';
                   $str .= emissione_form();
               }
               else
                   $str .= '<BR />Non &egrave; stato possibile rimuovere il file: '.$nomefile;   // La gestione dell'errore e' demandata in myapptabelle.js nella function upload_file
          }
          else {
             // Genero la maschera di eliminazione del file con indicate in titolo le dimensioni ed il tipo file
              if($_REQUEST['tipoazione'] == 'uploadedfile')
                  $str .= '<H3>Il file: '.$nomefile.' &egrave; stato correttamente inviato</H3>';
              $str .= "<P>Nome file: $nomefile<BR />tipo: ".mime_content_type($nomefile_full).'<BR />dimensione: '.human_filesize(filesize($nomefile_full), 2)."</P>\n";
            // Genero il pulsante di download
              $str .= '<INPUT id="'.$_REQUEST['tbl_id_div'].'-sendfile" class="pulsante" type="button" onClick="upload_file(\''.$_REQUEST['tbl_id_div'].'\', \'sendfile\');" value="&#x1F4C1;&nbsp;DOWNLOAD FILE" />';
              if($tabella[$nome_tabella_nel_config]['campi'][$obj[1]]['editable']) // Solo se editabile posso rimuove il file
                  $str .= '&nbsp;&nbsp;<INPUT class="pulsante" type="button" onClick="upload_file(\''.$_REQUEST['tbl_id_div'].'\', \'removefile\');" value="&#x1F5F4;&nbsp;RIMUOVI IL FILE" />'."\n";
              $str .= test_is_image($tabella, $nome_tabella_nel_config, $obj[1], $nomefile, TRUE);  // Mostro l'anteprima immagine estesa
          }
      }  // fine if file_exists
      // Se il file non esiste nel filesytem lo rimuovo e avviso l' utente
      else {
          $conn_obj->sql_command('UPDATE '.$tabella[$nome_tabella_nel_config]['nometabella']." SET $obj[1] = '' WHERE id=$obj[2] LIMIT 1") ;  // LIMIT 1 per sicurezza
          $str .= "Il file <B>$nomefile</B> non esiste nella cartella <B>$cartella_upload</B> pertanto &egrave; stato rimosso dalla tabella <B>".$tabella[$nome_tabella_nel_config]['nometabella'].'</B>'."\n";
          $str .= "<SCRIPT type=\"text/javascript\">\n"
                  ."$('#' + '".$_REQUEST['tbl_id_div']."').html('".$nomefile_status_span."');";
                  if($is_alias_table)
                      $str .= "if($.type($('#".$obj_norm."')) == 'object') $('#".$obj_norm."').html('".$nomefile_status_span."');\n";
          $str .= '</SCRIPT>';
      }
   } // fine if strlen($nomefile)
   else {  // il file non esiste o ne chiedo l'upload compongo per l'acquisizione con la progress bar
      if($tabella[$nome_tabella_nel_config]['campi'][$obj[1]]['editable'] OR (isset($_REQUEST['tipoazione']) AND $_REQUEST['tipoazione'] == 'ultimorecord'))
           emissione_form();
   } // fine else
   echo '<DIV class="div_contenitore_upload_testo">'.$str.'</DIV><SPAN id="nomefile_status_span" style="display:none;">'.$nomefile_status_span.'</SPAN>';  // output
} // fine if isset isset($_REQUEST['tbl_id_div']

?>