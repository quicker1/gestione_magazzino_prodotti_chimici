// michele.furlan@unipd.it   22 dicembre 2022

function jqXHRformatErrore(jqXHR, exception) {
    if (jqXHR.status === 0)
        return ('Non connesso.<BR />Prego verificare la connessione di rete.');
    else if (jqXHR.status == 404)
        return ('Pagina non trovata [404]');
    else if (jqXHR.status == 500)
        return ('Errore interno al server [500].');
    else if (exception === 'parsererror')
        return ('JSON richiesta analisi fallita.');
    else if (exception === 'timeout')
        return ('Time out error.');
    else if (exception === 'abort')
        return ('Ajax richiesta abortita.');
    else
        return ('Errore sconosciuto.<BR />' + jqXHR.responseText);
};  // fine function jqXHRformatErrore


jQuery.fn.extend({   // dato l'id nometabellaALIAS-nomecampo-id_riga-azione lo restituisce senza ALIAS
  change_id_alias: function($add_alias) {   // Se $add == true lo aggiunge se manca o se false lo toglie se esiste - viene verificata la presenza dell'oggetto jquery
    var $attr_id = this.attr("id");
    var $new_id = false;

    if($.type($attr_id) !== "string" || $attr_id.split("-").length !== 4 || arguments.length == 0 || $.type($add_alias) !== "boolean")
        return $new_id;   // Errore applicazione metodo

    var $obj_id = $attr_id.split("-");

      if(!$add_alias && $obj_id[0].substr(-5, 5) === "ALIAS") {  // Chiedo di togliere ALIAS
            $obj_id[0] = $obj_id[0].substr(0, $obj_id[0].length -5);
      }
      if($add_alias && $obj_id[0].substr(-5, 5) != "ALIAS") {    // Chiedo di aggiungere ALIAS
            $obj_id[0] += "ALIAS";
      }
      $new_id = $obj_id.join("-");  // Nuovo id
  return $("#" + $new_id).length ? $("#" + $new_id) : false;  // Ritorna l'oggetto jQuery voluto se esiste
  }
}); // Fine jQuery change_id_alias


function AvviaAppTabelle() {  // Inizializza le varibili ambiente - test file di configurazione e compatiblita' del browser
this.inizializzato = false;   // Defaut stato di corretta inizializzazione dell' applicazione
var objapp = this; // Per la visibilita' di inizializzato alle sub funzioni locali
$.ajaxSetup({cache: false});  // No ajax JQuery cache ed eventuale contentType: "application/x-www-form-urlencoded;charset=ISO-8859-1"

this.append_finestra_modale = function () {  // In fase di inizializzazione
$(document.body).append('<!-- Inizio finestra modale --><DIV class="mod_modal"><DIV class="mod_modal-content" tabindex="-1">' +
            '<SPAN class="mod_close-button">&times;</SPAN>' +
            '<DIV id="id_stato_aggioramento" class="div_aggiornamento"></DIV>' +
            '</DIV></DIV><!-- Fine finestra modale -->'
           );

  $(".mod_close-button").click(toggleModal);
  $(window).click(windowOnClick);

};  // fine function append_finestra_modale

this.reset_variabili = function() {
  window.id_div_aperti = undefined; window.id_div_aperti = new Array();  // Deposito id_div con il segno - che dovranno diventare chiusi prima di aprire le tabelle - array associativo
  window.z_index_new_table = undefined; window.z_index_new_table = 5;    // Le tabelle vengono aperte con z-index incrementale
  window.root_div_table = undefined; window.root_div_table = new Array();
  window.div_tag_corpo_tabella = undefined; window.div_tag_corpo_tabella = new Array();  // Per drag drop tabelle memorizzo l'id del DIV che continene ogni singola tabella
} // fine function reset_variabili

this.check_html5 = function() {
var canvas_obj = document.createElement('canvas');
return canvas_obj.getContext ? true : false;
};

this.cerca_chiave_array = function($array, $val) {  // dato l'array associativo $array cerca la chiave che ha il valore $val
  for(var $chiave in $array)
      if($array.hasOwnProperty($chiave))
         if($array[$chiave] == $val)
             return $chiave;

return false;  // Valore $val non trovato
};  // fine function cerca_chiave_array


this.AvviaAppTabelle = function() {  // Costruttore
 // Verifica della presenza di una altra istanza AvviaAppTabelle nel global space windows del browser
 for(let varnome in window)
     if(window.hasOwnProperty(varnome) && window[varnome] instanceof AvviaAppTabelle) {
         alert("Una sola istanza di AvviaAppTabelle e' consentita\n" +
               "La variabile[: " + varnome + "  :] e' gia' istanziata");
     return false;
     }

 // Prima di tutto check se html5 compatibile
 if(!objapp.check_html5()) {
     alert("Il tuo browser non e' HTML5 compatibile, la app non puo' essere inizializzata");
     return false;
 }

// Attacco l'evento resize windows per chiudere tabelle figlie aperte
 $(window).resize(function() {
     elimina_tabelle_aperte("Tpadrefittizia-Tfigliafittizia-00-veditabella");
 });

// Reset variabili globali
 if(typeof root_div_table === 'undefined') { // Prevengo ulteriori inizializzazioni
    objapp.reset_variabili();
    objapp.append_finestra_modale();     // Necessaria per gli avvisi utente
// Test della configurazione
    $.ajaxSetup({async: false});         // Comunicazione sincrona per permettere l'attach degli eventi
    var jqXHR = $.post("./checkconfig.php", {'tbl_azione': 'verificaapp'}, function(data) {
                     if($.trim(data).length > 2)     // Errore di configurazione
                        stato_aggiornamento(false, data);
                     else
                        objapp.inizializzato = true;
                }).fail(function(xhr, err) {
                        stato_aggiornamento(false, $(xhr.responseText).filter('title').get(0) + "<BR />" + jqXHRformatErrore(xhr, err));
                });
    $.ajaxSetup({async: true});
 } // fine if
}();  // fine function costruttore AvviaAppTabelle


this.CaricaTabella = function(dom_elem, tbl_nome) { // La funzione carica la tabella root in tbl_index dell'array root_div_table nell'elemento dom_elem e la inserisce nel dom_elem
  if(objapp.inizializzato) {
    if(!$("#" + dom_elem).length) {
        alert("Il nodo (ID) di costruzione della tabella non e' valido !\n\nNome nodo: " + dom_elem);
        return false;
    }
    if(-1 != root_div_table.indexOf(tbl_nome)) {
        alert("Tabella: " + tbl_nome + " gia' caricata nel div: " + dom_elem);
        return false;
    }

    root_div_table.push(tbl_nome);  // Elenco gli id dei DIV dove le tabelle padri verranno aperte: DIV id="tbl_nome_" + nome tabella padre
    div_tag_corpo_tabella[tbl_nome] = $("#" + dom_elem);  // Memorizzo il JQuery object del DIV che contiene ciascuna tabella master caricata

    if($("#tbl_nome_" + tbl_nome).length)     // reset tabella se aperta e sue variabili
         $("#tbl_nome_" + tbl_nome).remove(); // rimuove l'oggetto div che contiene le tabelle e le tabelle medesime

     $("#" + dom_elem).empty().append("<DIV style='z-index:" + (++z_index_new_table) + ";display:none;' id='tbl_nome_" + tbl_nome + "'></DIV>");
     vedi_tabella(tbl_nome);
     $("#tbl_nome_" + tbl_nome).fadeIn('fast');  // Dopo il caricamento mostro la tabella
  }  // fine if inizializzato
  else  {
     alert("Inizializzare prima correttamente l'applicazione !");
     return false;
  }
return true;
};  // Fine metodo carica tabella

this.ScaricaTabella = function(tbl) {     // In tbl nome tabella master
 // cerco in root_div_table il valore della chiave che corrisponde a tbl
 var chiave = root_div_table.indexOf(tbl);
   if(-1 != chiave) {
        // Devo prima chiudere tutti i div aperti
        for(var i in window.id_div_aperti)
              elimina_tabelle_aperte(i);

        div_tag_corpo_tabella[tbl].empty(); // Svuoto il contenitore della tabella master
        delete div_tag_corpo_tabella[tbl];  // Elimino l'oggetto dall'array
        root_div_table.splice(chiave, 1);   // tolgo l'elemento tbl dalla root table
        return true;
   }
   else {
       alert("La tabella: " + tbl + " non e' presente nel DOM del documento");
       return false;
   }
};  // Fine metodo scarica tabella

this.Reset = function() {  // elimina tutte le tabelle aperte e azzera le variabili
   root_div_table.forEach(function(item, indice) {
                        $("#tbl_nome_" + item).remove();
   });
   objapp.reset_variabili();
}; // Fine function reset

this.IsTblLoad = function(tbl) {  // verifica se tbl e' caricata
     return ($.type(tbl) === "string" && $.type(div_tag_corpo_tabella[tbl]) === "object") ? true : false;
}; // fine metodo IsTblLoad

}  // fine function AvviaAppTabelle


function stato_aggiornamento(stato, err) {  // Stato true per abilitare false diversamente - in err eventuale errore da segnalare all'utente
// Negli eventi post sincroni avviso l'utente dell'attesa
$('#id_stato_aggioramento').empty();
  if(stato) {
     toggleModal();
     $('#id_stato_aggioramento').html(err.length > 1 ? err : 'ATTENDERE AGGIORNAMENTO IN CORSO...');
     $.ajaxSetup({async: false});  // Comunicazione sincrona per permettere l'attach degli eventi
  }
  else {
       $.ajaxSetup({async: true});   // Ripristino comunicazione ajax asincrona
         if(!stato && $.trim(err).length > 2) {   // Se la finestra modale e' chiusa la devo aprire per visualizzare l'errore
             if($("DIV[class='mod_modal']").css('visibility') == 'hidden')
                 toggleModal();
             $('#id_stato_aggioramento').html('ERRORE tipo: <BR />' + err);
         }
         else {
             if($("DIV[class='mod_modal mod_show-modal']").css('visibility') == 'visible')  // Nasconde la finestra modale
                 toggleModal();
         }
   }  // Fine else
} // Fine funzione stato aggiornamento


function togli_alias(tbl) {  // controlla se alias e lo toglie
  if(tbl.substr(-5, 5) === "ALIAS")
     return tbl.substr(0, tbl.length -5);
  else
     return tbl;
} // fine function togli_alias


function is_alias_table(tbl) {  // controlla se alias e lo toglie
  if(tbl.substr(-5, 5) === "ALIAS")
     return true;
  else
     return false;
} // fine function is_alias_table


Array.prototype.GetMatchArray = function(valore) {   // Se figlia true check della tabella padre
var indice = 0;

    while(indice < this.length) {
        if((new RegExp("^(" + valore + "){1}(\-){1}[a-zA-Z0-9_]+(\-){1}")).test(this[indice]))
            return ++indice;
        indice++;
    }
return -1;
}  // Fine GetMatchArray


function elimina_tabelle_aperte(id_div) {  // Prima di aprire nuove tabelle elimino quelle aperte non figlie di quella gia' aperta
// ricevo in id_div il nome del div con tabella da aprire e analizzo se ci sono gia' tabelle aperte con lo stesso nome- forma id_div oggetti-tabellapadre-2-veditabella
var obj = id_div.split('-')[0];            // Nome tabella - comprese le ALIAS table
var id_div_out = '';
var indice = Math.max(1, window.id_div_aperti.GetMatchArray(obj));

  while(indice <= window.id_div_aperti.length) {
        if(window.id_div_aperti[window.id_div_aperti.length -1].split('-')[0] == id_div.split('-')[1])
              break;  // Non posso eliminare la tabella padre
        id_div_out = window.id_div_aperti.pop();
        obj = id_div_out.split('-')[0];
        z_index_new_table--;             // Abbasso lo z-index per l'apertura delle nuove tabelle
        $('#tbl_nome_' + obj).remove();  // Rimuovo l'elemento dal DOM del documento
  }  // FINE while

  if(id_div_out) {
      $('#' + id_div_out).off().html(is_alias_table(id_div_out.split('-')[0]) ? '+' : '&#x1F50E;').click(function(event) {
           event.stopImmediatePropagation();
           apri_tabella(this.id);
      });
  }
} // fine function elimina_tabelle_aperte


function download_excel_table(id_tbl) {   // $tabella_richiesta-inviatabellainexcel
 var obj = id_tbl.split("-");   // nome tabella in obj[0]
 downloadFile("./fileupload.php?tbl_id_div=" + obj[0] + "&tipoazione=downloadxls&servizio_richiesto_xls="+$.data(window, 'servizio_richiesto')+"&anno_richiesta_xls="+$.data(window, 'anno_richiesta'));
} // fine funzione download_excel_table


function setting_tabella(tbl, id, tipo) {   // restituisco l'oggetto ricercato in id_div dati tabella interessata in tipo - ordine - filtro - pagina
var val = "";    // Valore di ritorno - "" default

 switch (tipo) {
   case "ordine":   // Recupero i valori dei pulsanti di ordinamento
   // Nell'attributo alt ho il valore attuale di id_ordine - l attributo alt e' sempre presente
      $("INPUT[type='button'][id^='" + tbl + "'][id$='-ordinacampo']").each(function() {
         if($.type($(this).attr("alt")) == "string" && $(this).attr("alt").length > 2) {
             val = {id_ordine: $(this).attr("id"), valore: $(this).attr("alt")};
             return false; // esco dalla funzione each - ho trovato il campo con il valore da preservare
         }
      });  // fine ciclo sui campi ordine

   break;
   case "filtro":  // Recupero i valori dei campi filtro
   // $tabella_richiesta."-".$campo."-".$id_tabella_padre."-filtracampo";
      $("INPUT[type='text'][id^='" + tbl + "'][id$='" + id + "-filtracampo']").each(function() {
         if($.trim($(this).val()).length > 0) {
            val = {id_filtro: $(this).attr("id"), valore: $.trim($(this).val())};
            return false; // esco dalla funzione each - ho trovato il campo il valore da preservare
         }
      });  // fine ciclo sui campi filtro

   break;
   case "pagina":  // Recupero i valori dei campi filtro
      val = $("INPUT[type='text'][id^='" + tbl + "-campolibero-']");  // $tabella_richiesta-campolibero
      val = (val.length && $.isNumeric(val.attr("alt"))) ? val.attr("alt") : "prima";

   break;
   default:
      val = "";
   break;
 }  // fine switch tipo

return val;
}  // fine function valore_setting_tabella


function paginatore_tabella(id_div) {  // Nella tabella corrente si sposta del numero di pagine richieste
//id_div = nometabella-npag-idpadre-paginazione oppure solo un numero
var obj = id_div.split("-");
var ordine = "";

    if(obj[1] == "campolibero" && (!$.isNumeric($('#' + id_div).val()) || $('#' + id_div).val() < 1)) {
       alert("Inserire un valore numerico positivo >= 1");
       $('#' + id_div).val($('#' + id_div).attr("alt"));   // undo del valore
       return false;   // non eseguo la paginazione perche' valore pagina nel campo libero inconsistente
    }
    if(!isNaN(parseInt($('#' + id_div).val(), 10)))        // Il parametro e' quello centrale a campo libero
        obj[1] = parseInt($('#' + id_div).val(), 10);

vedi_tabella(obj[0], obj[2], obj[1], setting_tabella(obj[0], obj[2], "filtro"), setting_tabella(obj[0], obj[2], "ordine"));
}  // fine function paginatore_tabella


function test_se_regex(str) {  // verifica la regolarita di un espressione regolare
var isValid = true;
   try {
       new RegExp(str);
   } catch(e) {
       isValid = false;
   }
return isValid;
} // fine function test_se_regex


function update_campo(id_val) { // Si limita a vedere la tabella padre o figlia che sia
var obj = id_val.split("-");    // nometabella-nomecampo-id_riga-azione
var valore_campo = $("#" + id_val).val();
var j_obj = null;

// Prima di aggiornare il dato verifico se coerente con il pattern nel caso di campi input di tipo testo
  if($.type($("#" + id_val).attr("pattern")) === "string" && $.type($("#" + id_val).attr("title")) === "string") {
      if($("#" + id_val).attr("pattern").length) {  // Se esite un contenuto nel pattern testo l'espressione regolare
         if(test_se_regex($("#" + id_val).attr("pattern"))) {
            if($("#" + id_val).prop("validity").patternMismatch) {  // patternMismatch HTML 5
               alert("Errore tipo dato nel campo " + obj[1] + ":\n" + $("#" + id_val).attr("title"));
               return false;
            }  // fine if test()
         }
         else {
            alert("Formato dell'espressione regolare non valido !\n" + $("#" + id_val).attr("pattern"));
            return false;
         }
      } // Fine if ("pattern").length
  }  // fine if pattern == string

  j_obj = is_alias_table(obj[0]) ? $("#" + id_val).change_id_alias(false) : $("#" + id_val).change_id_alias(true);

  if($.type(j_obj) !== "boolean")  {
      j_obj.val(valore_campo);   // Set del valore gemello in tabella tabulare se esiste
      if(j_obj.attr("type") == "checkbox")
           j_obj.prop('checked', valore_campo == 1 ? true : false);
  }  // Fine if aggiornamento campo tabella associata tabulare o ALIAS

// Potrebbe essere una tabulare e devo aggiornare il campo nella verticale alias se esso esiste
  $.post("./crud.php", {'tbl_azione': 'edit', 'id_input': id_val, 'valore': valore_campo, 'lista_richieste_tabella':parametri_selezione_menu()}, function(data) {
        if($.trim(data).length > 2)
            alert("Errore in aggiornamento del campo:\n" + "record id_div: " +  id_val + "\nTipo: " + data);
  }).done(function() {  // Eseguo al termine dell'aggiornamento del campo
// Se nella stessa riga sono presenti campi calcolati li devo aggiornare - anche nella alias table e viceversa
// Se la tabella tabulare e' aperta aggiorno i calcolati in essa e dopo nella tabulare
    if($("#tbl_nome_" + togli_alias(obj[0]) + "ALIAS").length)  { // Significa che la alias table e' aperta e posso aggiornare tutti i calcolati sulla alias
        $("TD[id^='" + togli_alias(obj[0]) + "ALIAS" + "-'][id$='-" + obj[2] + "-campocalcolato'][data-ricalcolo='SI']").each(function() {  // Ciclo su tutti i campi calcolati aventi medesimo ID
          $(this).empty().load("./crud.php", {'id_input': $(this).attr("id"), 'tbl_azione': "calcola"}, function(responseTxt, statusTxt, xhr){
             if(statusTxt == "error") {
                 stato_aggiornamento(false, xhr.status + ":<BR />" + xhr.statusText);
             }
             else {
                 var obj_id_tab = $(this).change_id_alias(false);  // elimino tabALIAS
                  if($.type(obj_id_tab) !== "boolean")
                      obj_id_tab.empty().html($(this).html());     // Set del valore calcolato gemello in tabella tabulare
             }
          });  // Fine load crud.php
        });  // fine each TD verticale
    } // Fine IF TD alias
    else { // Aggiornamento solo in tabulare unica aperta
        $("TD[id^='" + obj[0] + "-'][id$='-" + obj[2] + "-campocalcolato'][data-ricalcolo='SI']").each(function() {  // Ciclo su tutti i campi calcolati aventi medesimo ID
          $(this).empty().load("./crud.php", {'id_input': $(this).attr("id"), 'tbl_azione': 'calcola'}, function(responseTxt, statusTxt, xhr){
              if(statusTxt == "error") {
                   stato_aggiornamento(false, xhr.status + ":<BR />" + xhr.statusText);
              }
          });  // fine load
        }); // Fine each TD tabulare
    }  // Fine else solo tabulare
  }).fail(function(xhr, err) {
            stato_aggiornamento(false, $(xhr.responseText).filter('title').get(0) + "<BR />" + jqXHRformatErrore(xhr, err));
  }); // Fine su $.post()
} // fine function update_campo


function downloadFile(urlToSend) {
stato_aggiornamento(true, 'DOWNLOAD FILE IN CORSO...');
 var req = new XMLHttpRequest();
 var fileName = undefined;
 req.open("GET", urlToSend  + '&rand=' +  Math.floor(100000*Math.random()), true);  // // randomizzo per prevenire il caching nel browser
 req.responseType = "blob";

  req.onload = function (event) {
     stato_aggiornamento(false, '');
     var blob = new Blob([req.response], {type: "application/octet-stream"});
     var disposition = req.getResponseHeader("Content-Disposition");   // nome file in  header http
        if (disposition && disposition.indexOf('attachment') !== -1) {
           var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
           var matches = filenameRegex.exec(disposition);
             if (matches != null && matches[1])
                 fileName = matches[1].replace(/['"]/g, '');
             else
                 fileName = "file_download_binary";
        }
     if($.type(fileName) == "string") {
        var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(link.href);
     }  // fine if test esistenza filename
     else {
         stato_aggiornamento(false, "Non e' stato ricevuto il file per un errore sul server !");
         return false;
     }
   };  // fine onload
   req.send();
   return true;
}


function upload_file(id_div, tipo, msg_err) {   // Apre la gestione del sistema di upload del file
// tipo == listafile - uploadedfile - removefile - sendfile - ultimorecord (uploaded file e' il comando che mostra la schermata dopo un upload avvenuto con successo
var tipo = tipo || "listafile";  // default
var msg_err = msg_err || "";

  if(tipo == 'removefile' && !confirm('Confermi di voler rimuovere il file ?'))
     return false;   // Annullo la rimozione del file

  if(tipo == "listafile" || tipo == "ultimorecord") toggleModal();  // Attivo finestra modale - per uploadedfile e removefile sendfile e' gia' attiva

  if(tipo == "sendfile") {  // URL?var1=value1&var2=value2
    downloadFile("./fileupload.php?tbl_id_div=" + id_div + "&tipoazione=sendfile");
  // Nascondo il pusante di download
     if($("#" +  id_div + "-sendfile").length)
          $("#" +  id_div + "-sendfile").css("display", "none");
  }
  else {
     $("#id_stato_aggioramento").empty().load("./fileupload.php", {'tbl_id_div': id_div, 'tipoazione': tipo, 'lista_richieste_tabella':parametri_selezione_menu()}, function(responseTxt, statusTxt, xhr){
        if(statusTxt == "error")
           stato_aggiornamento(false, xhr.status + ":<BR />" + xhr.statusText);
        else
          if($("#nomefile_status_span").length) {
            var id_norm = is_alias_table(id_div.split("-")[0]) ? $("#" + id_div).change_id_alias(false) : $("#" + id_div).change_id_alias(true);  // Toglie o add alias ID e restituisce oggetto in tabulare se presente
             if(tipo != "listafile" && tipo != "uploadedfile") {    // E stato eseguito un remove file
                 $("#" + id_div).html($("#nomefile_status_span").html());  // aggiorno il valore in tabella con il pulsante input file
                  if($.type(id_norm) != "boolean")  // Conferma esistenza - false == non esiste nel DOM
                      id_norm.html($("#nomefile_status_span").html());
             }
             if(tipo == "uploadedfile") {
                if(typeof(nomefilejs) == "undefined" && msg_err && $("#output_file").length)  // Se c'e' stato un errore in upload del file lo emetto
                   $("#output_file").html(msg_err);
                else if(typeof(nomefilejs) != "undefined")  {   // potrebbe essere una tabALIAS - devo aggiornare entrambe le tabelle
                    $("#" + id_div).html(nomefilejs);
                     if($.type(id_norm) != "boolean")  // Se e' una tabella tab alias devo aggiornare anche la tabella tabulare e viceversa
                         id_norm.html(nomefilejs);     // Set del valore gemello in tabella tabulare se esiste che automaticamente aggiorna il campo
                   nomefilejs = undefined;   // per evitare successivo riutilizzo !
                }
             }  // fine if uploaded file
          }  // fine if test jQuery object
     });  // Fine load
  }
} // fine function upload file


function chiudi_tabella(id_div) {  // Chiude su evento mouse nei segni
  $("#tbl_nome_" + id_div.split("-")[0]).animate({opacity:'toggle', height:'toggle'}, 260, function() {
     elimina_tabelle_aperte(id_div);
     set_elimina_add_record(false);

       $("#" + id_div).mouseup(function(event) {
           event.stopImmediatePropagation();
              $(this).click(function(event) {
                  event.stopImmediatePropagation();
                  apri_tabella(this.id);
              });   // Fine evento click
       });  // fine evento mouseup
  });  // Fine animazione
}  // fine function chiudi_tabella


function set_elimina_add_record(stato) { // disabilito/abilito i pulsanti di nuovo ed eliminazione record - true disabilito <> false riabilito
// aggiungirecord ed eliminarerecord tutti alla fine degli input type button
  $("input[type='button'][id$='-ordinacampo'],input[type='button'][id$='-listafile'],input[type='button'][id$='-aggiungirecord'],input[type='button'][id$='-eliminarecord'],input[id$='-paginazione'],input[id$='-filtracampo'],input[id$='inviatabellainexcel']").each(function(index) {
      if(stato) {
          if($.type($(this).attr('disabled')) != 'string')   // Set dell'attributo se esiste
             $(this).attr('disabled', 'disabled');
      }
      else
          $(this).removeAttr('disabled');
  });
} // fine function set_elimina_add_record


function apri_tabella(id_div) {    // su click apre tabella figlia e crea prima il relativo contenitore
 var obj_div = $("#" + id_div);
 var obj = id_div.split("-");
 var appeso = null;
   obj_div.off();                   // Elimino gli eventi perche' dopo aver aperto la tabella vado in off
   tbl_obj = "tbl_nome_" + obj[0];  // in obj[0] nome della tabella figlia da aprire - c'e' sempre la root table corrispondente
   elimina_tabelle_aperte(id_div);  // Se ci sono tabelle gia' aperte con lo stesso nome le elimino comprese le tabelle padri
   set_elimina_add_record(true);    // disabilito i pulsanti padre di aggiunta ed eliminazione record

   $("#tbl_nome_" + obj[1]).append("<DIV style=\"position:absolute;display:none;opacity:0\" id='" + tbl_obj + "'></DIV>");  // contenitore tabella un contenitore per ogni riga che deve essere univoco
   appeso = $("#" + tbl_obj);
   appeso.css("z-index", ++z_index_new_table); // +z-index per apertura tabelle successive figlie in primo piano

 var tx = obj_div.position().top + parseInt(obj_div.css("height"), 10);
 var sx = obj_div.position().left + parseInt(obj_div.css("width"), 10);
   appeso.offset({top:tx,left:sx});  // Posiziono l'elemento nuovo contenitore della tabella
   window.id_div_aperti.push(id_div);       // Salvo id div con - per trasformali in + se apro tabelle
   vedi_tabella(obj[0], obj[2]);     // e nel contempo attacca gli eventi di apertura tabelle figlie

   appeso.animate({opacity: "+="+1, height: 'toggle'}, 320,  // Effetto animazione tendina a discesa scroll screen
        function() {
           try {  // try perche' non supportate forse da tutti i browser le property
               if(($(document).scrollTop() + $(window).height()) < ($(this).offset().top + $(this).height()))
                   $('html, body').animate({'scrollTop': "+="+($(this).offset().top + $(this).height() - $(document).scrollTop() - $(window).height())}, "slow");
           }  // fine try
           catch(err) {
                   $.noop();
           }
         }  // Fine function scrool document
   ); // Fine animazione

   obj_div.html('&#x1F86C;').mouseup(function(event) {   // Cambio nel segno meno e collegamento evento mouseup '--' originale
          event.stopImmediatePropagation();
       // Elimino gli eventi perche' dopo aver aperto la tabella vado in off
          $(this).off().click(function(event) {
              event.stopImmediatePropagation();
              chiudi_tabella(this.id);
          });
   });
} // fine apri_tabella


function attacca_eventi() {  // su tutti id con azione edit - veditabella DA ELIMINARE
// Attacco l'evento vedi tabella sui DIV con contenuto segno +
  $("DIV").each(function(index) {
  // Nel caso di ultima tabella non esiste la croce di espansione tabella figlia - verifico se esiste l'ID
    if($(this).attr("id") !== undefined) {
      var obj = $(this).attr("id").split("-");
      var eventObject = $._data($(this).get(0), 'events');
        if(!($.type(eventObject) != "undefined" && $.type(eventObject.click) != "undefined") && obj[obj.length -1] == "veditabella") {
              $(this).click(function(event) {
                   event.stopImmediatePropagation();
                   apri_tabella(this.id);
              }); // Fine click
        }
    }  // Fine if != undefined
  });  // Fine DIV each

}  // fine funzione attacca_eventi


function vedi_tabella(tbl, id, pagina, filtro, ordine) {  // Si limita a vedere la tabella padre o figlia che sia in id chiave tabella padre (vedi i click su +)
 id = id || 0;           // 0 significa che la tabella che apro non e' figlia di altre
 pagina = pagina || "";  // set default value per la richiesta di pagina nel caso fosse attivo il paginatore
 filtro = filtro || "";
 ordine = ordine || "";

 stato_aggiornamento(true, "");   // Avviso utente attessa e async ajax false
   // Svuoto il div
   $("#tbl_nome_" + tbl).empty().load("./crud.php", {"tbl_azione":"veditabella", "tbl_nome":tbl, "tbl_id":id, "pagina":pagina, "filtro":filtro, "ordine":ordine, "lista_richieste_tabella":parametri_selezione_menu()}, function(responseTxt, statusTxt, xhr){
       if(statusTxt == 'success') {
          stato_aggiornamento(false, '');  // Chiude la finestra modale
       }  // fine if status
       if(statusTxt == 'error')
          stato_aggiornamento(false, xhr.status + ":<BR />" + xhr.statusText);
   });

attacca_eventi(); // solo su tutti i figli del contenitore della nuova tabella creata
} // fine function vedi_tabella


function aggiorna_colonna_ad_record(tbl, id_padre, operazione) {  // in tbl nome della tabella interessata all'operazione A(add) o D(delete)  id_padre - non eseguito per tab alias
// Dopo le operazioni di aggiunta od eliminazione del record devo fare il refresh dello stato nella tabella padre di quella aggiornata
var tbl_padre = window.id_div_aperti.GetMatchArray(tbl);
var n_record_pregresso = 0;
   if(-1 != tbl_padre) {   // ci sono tabelle padri da aggiornare e ne traggo il nome

      tbl_padre = window.id_div_aperti[tbl_padre -1].split("-")[1];
      var obj = $("TD[data-alt|='" + tbl_padre + "'][data-alt$='-" + id_padre + "-eliminarecord']");
      var obj_record = $("TR[id^='" + tbl + "-numerorecord']");   // Array con tutte le righe TR -  l' attributo record identifica il tipo di riga che contiene il record

        if(obj.find("B").length && $.isNumeric(obj.find("B").html()))
           n_record_pregresso = parseInt(obj.find("B").html(), 10);
        n_record_pregresso += operazione == "add" ? 1 : -1;

      if(obj.length && obj_record.length) {  // solo se esiste il tag id in TD posso gestire l'eliminazione record e l'indicatore tabella figlia
         obj.empty();
         obj_record = obj_record.attr("id").split("-")[2];     // Nel terzo array c'e' il numero dei record
           if(n_record_pregresso < 1 && "true" == obj.attr("data-nodel"))  // posso abilitare il segno elimina esite un solo record che e' quella della TR intestazione
               obj.html("<INPUT class=\"pulsante_x\" id=\"" + obj.attr('data-alt') + "\" type=\"button\" value=\"&#128465;\" onClick=\"elimina_record(this.id);\" title=\"ELIMINA IL RECORD\" />");
           else  // Ci sono dei record
               obj.html("SUB:&nbsp;<B>" + n_record_pregresso + "</B>");
      } // fine if verifica se oggetto
   } // fine if -1
} // fine function aggiorna_colonna_ad_record


function elimina_record(id_del) {  // Riceve l'id del record da cancellare - nometabella-id_tabella_padre-id_record_da_eliminare_comando
if(!confirm("Vuoi eliminare il record selezionato: " + id_del + " ?"))   // conferma di cancellazione
    return false;

var obj = id_del.split("-");
var err = "";
var obj_pagina = null;

  stato_aggiornamento(true, "");  // eliminazione sincrona
var jqXHR = $.post("./crud.php", {"tbl_azione" : "eliminarerecord", tbl_nome: obj[0], tbl_id: obj[2], "lista_richieste_tabella":parametri_selezione_menu()}, function(data) {
                       err = data;
            }).done(function(dati, stato_ritorno, jqXHRinfo) {
                stato_aggiornamento(false, "");
                   if(0 == dati.search('CANCELLATO')) { // Operazione di cancellazione esiguita con successo
                       vedi_tabella(obj[0], obj[1], setting_tabella(obj[0], obj[1], "pagina"));   // in obj[1] ci deve essere l'id della tabella padre !
                       aggiorna_colonna_ad_record(obj[0], obj[1], "del");
                   }
                alert(dati);
            }).fail(function(xhr, err) {  // ajax fallito
                    stato_aggiornamento(false, jqXHRformatErrore(xhr, err));
            });
} // fine function elimina_record


function add_record(id_new) {  // Aggiunta nuovo record nella tabella richiesta
var obj = id_new.split("-");   // id_new in forma tabella-NONE-id_tabellapadre-azione

  stato_aggiornamento(true, "");
    $.post("./crud.php", {"tbl_azione": "aggiungirecord", "tbl_nome": obj[0], "tbl_id": obj[2], "lista_richieste_tabella":parametri_selezione_menu()}, function(data) {
          if($.trim(data).length > 2)  // errore server side
              alert("ERRORE in aggiunta record tabella:\n" + obj[0] + "\nID record: " + obj[2]);
          else {
              stato_aggiornamento(false, "");  // Chiudo finestra modale
              vedi_tabella(obj[0], obj[2], 'ultimaedit');
              aggiorna_colonna_ad_record(obj[0], obj[2], "add");
          }
    }).fail(function(xhr, err) {  // ajax errore
              stato_aggiornamento(false, jqXHRformatErrore(xhr, err));
    });  // Fine $.post
}  // fine function add_record


function filtro_campo(id_div) {   // Riceve l'id con il campo da filtrare
var obj = id_div.split("-");
var filtro = {"id_filtro": id_div, "valore": $.trim($('#' + id_div).val())};

    vedi_tabella(obj[0], obj[2], setting_tabella(obj[0], obj[2], "pagina"), filtro, setting_tabella(obj[0], obj[2], "ordine"));  // posiziono al primo record dell'eventuale paginazione
}  // fine function filtro_campo


function ordina_campo(id_div) {  // riceve in id_div comando di ordinazione del campo
// in id_div tabella_richiesta-campo-id_tabella_padre-(ASC o DESC)-ordinacampo;
var obj = id_div.split("-");
var ordine = {id_ordine: id_div, valore: obj[3]};
// devo preservare la paginazione che mantengo inviando il numero di pagina
   vedi_tabella(obj[0], obj[2], setting_tabella(obj[0], obj[2], "pagina"), setting_tabella(obj[0], obj[2], "filtro"), ordine);  // posiziono al primo record dell'eventuale paginazione
}  // fine function ordina_campo


function tabella_drag_drop(stato, obj) {  //  se stato == false intercetto l'evento click altrimenti l'evento mousemove
var x_prev = false;
var y_prev = x_prev;

    $(document).off("mousemove");
    if(stato) {
        obj = div_tag_corpo_tabella[obj.split("-")[0]];  // Recupero l'obj JQuery dove e' stata caricata la tabella master
        $(document).on("mousemove", function(event) {
            event.stopImmediatePropagation();
            if(!x_prev) {
                 x_prev = event.pageX;
                 y_prev = event.pageY;
            }
            else {  // Sposto il DIV
                 obj.offset({top: obj.position().top + event.pageY - y_prev, left: obj.position().left + event.pageX - x_prev});
                 x_prev = event.pageX;
                 y_prev = event.pageY;
            }
       });
   }
   else {
        x_prev = false;
        y_prev = x_prev;
   }
} // fine function tabella_drag_drop


function match_campi_editabili_drag_drop(obj, x, y) {  // obj == oggetto tipo JQuery - tag TD ricevuto su mouseup sul medesimo
// In obj id del tag TD che contiene il valore da copiare - in x == left y == top posizione del mouse rilasciato su schermo
// Possono essere copiati i campi origine e destinazione di tipo identico e compatibili, es. text con text - textarea con textarea ecc...
var dest_x = 0;
var dest_y = 0;
var jq_id_sorgente;
var jq_td_destinazione = false;
var z_index_prev = 0;
var val_sorgente = "";      // dato contenuto in obj
var val_destinazione = "";  // Per preservare il dato destinazione in caso di errore per il ripristino

// Criteri per il drag and drop. I campi sorgente select and date possono andare su tutti ma non confliggere tra loro
// I campi booleani e file non sono soggetti a drag and drop

 function get_current_td_zindex(obj_td) {  // Dato l'id del TD risale al DIV che ha la tabella che lo contiene e ne restituisce lo z-index
     dest_x = $("#tbl_nome_" + obj_td.attr("id").split("-")[1]);
     if(dest_x.length && $.isNumeric(dest_x.css("z-index")))
        return parseInt(dest_x.css("z-index"), 10);
     else
        return 1;  // z-index fittizio
 } // fine function get_current_td_zindex

 $("TD[id^='text-'][id$='-edit'],TD[id^='date-'][id$='-edit'],TD[id^='textarea-'][id$='-edit']").each(function() {   // Raccolgo i TD destinazione candidati e individuo quello con z-index maggiore
    if($(this).attr("id") == $(obj).attr("id"))  // Escludo su la copia su se stesso
        return false;   // esco dal ciclo each di jquery
    else {
        dest_x = $(this).offset().left;
        dest_y = $(this).offset().top;
           if((x >= dest_x && x <= (dest_x + $(this).innerWidth())) && (y >= dest_y && y <= (dest_y + $(this).innerHeight()))) { // Prima coordinata x soddisfatta
               if($.type(jq_td_destinazione) === "object" && (z_index_prev < get_current_td_zindex($(this))))   // Devo selezionare l'elemento con z-index piu' elevato cio' in primo piano
                   jq_td_destinazione = $(this);
               if($.type(jq_td_destinazione) === "boolean") {  // Primo setting del valore jq_td_destinazione
                   jq_td_destinazione = $(this);
                   z_index_prev = get_current_td_zindex(jq_td_destinazione);
               }
           }  // fine if coordinate mouse dentro TD
    }  // fine else esclusione se stesso
 }); // fine match TD destinazione

 if($.type(jq_td_destinazione) === "object") {  // Se ho trovato il TD destinazione procedo con la copia dei dati
 // Il campo destinazione di tipo date accetta come sorgente solo il date
    dest_x = obj.attr("id").split("-");
    jq_id_sorgente = Array.from(dest_x);  // Clonazione array
    jq_id_sorgente.shift();
    jq_id_sorgente = $("#" + jq_id_sorgente.join("-")) ;  // Ottengo l' id dell'input sorgente - oggetto jQuery

      if("campocalcolato" == dest_x[dest_x.length -1] || "noedit" == dest_x[0])
           val_sorgente = obj.html();
      else
           val_sorgente = jq_id_sorgente.val();

    dest_y = jq_td_destinazione.attr("id").split("-");
    jq_td_destinazione = Array.from(dest_y);      // Clonazione array
    jq_td_destinazione.shift();
    jq_td_destinazione = $("#" + jq_td_destinazione.join("-"));  // Nuovo oggetto jQuery
    val_destinazione = jq_td_destinazione.val();  // Preservo il valore di destinazione

      if(dest_y[0] == "date")  {  // Il campo destinazione di tipo date accetta come sorgente solo il date
          if(dest_x[0] == "date")
              jq_td_destinazione.val(val_sorgente);
      }
      else {
           if($.isNumeric(jq_td_destinazione.attr("maxlength")) && jq_td_destinazione.attr("maxlength") < val_sorgente.length)
              val_sorgente = val_sorgente.substring(0, jq_td_destinazione.attr("maxlength"));  // solo la lunghezza ammessa in destinazione
           jq_td_destinazione.val(val_sorgente);
      }
// Se si e' verificato un pattern error ripristino il dato originale nella destinazione
      if($.type(jq_td_destinazione.attr("pattern")) !== "undefined" && jq_td_destinazione.prop("validity").patternMismatch) {
          alert("Tipo di dato non consentito nel pattern di destinazione: " + jq_td_destinazione.attr("pattern"));
          jq_td_destinazione.val(val_destinazione);   // Ripristino del valore originale
      }
      else
          update_campo(jq_td_destinazione.attr("id"));
 }  // Fine if match TD destinazione

} // fine funzione match_campi_editabili_drag_drop


function td_drag_drop(stato, obj) {  //  se stato == false intercetto l'evento mouse down altrimenti l'evento mouseup
var x_prev = false;
var y_prev = x_prev;

   obj = $("#" + obj);  // Converto in oggetto jQuery
   $(document).off("mousemove");
    if(stato) {
        $(document).on("mousemove", function(event) {
            event.stopImmediatePropagation();
            var posizione = obj.offset();
            if(!x_prev) {
                 obj.data("posizione", {sinistra: posizione.left, alto: posizione.top});  // Conservo i valori iniziali
                   if($.type(obj.attr("class")) !== "undefined")
                       obj.data("classe_originale", obj.attr("class")); // Preservo la classe originale degli stili applicati all'elemento TD
                 x_prev = event.pageX;
                 y_prev = event.pageY;
            }
            else {  // Sposto il tag TD
                 if(!obj.hasClass("td_drag_drop_classe")) {
                     obj.attr("class", "td_drag_drop_classe");
                 }
                 obj.offset({top: posizione.top + event.pageY - y_prev, left: posizione.left + event.pageX - x_prev});
                 x_prev = event.pageX;
                 y_prev = event.pageY;
                 obj.data("mouse", {mouse_x: x_prev, mouse_y: y_prev});
            }
       });
    }
    else {  // evento mouse up o mouse leave - ripristino la posizione iniziale
          with(obj) {
              if(typeof data("posizione") !== "undefined" && typeof data("mouse") !== "undefined" && !is(':animated')) {  // !is(':animated') perche' serve preventire il rientro nell'animazione su mouseleave prima che essa sia terminata
                   match_campi_editabili_drag_drop(obj, data("mouse").mouse_x, data("mouse").mouse_y);  // Cerca il target dove copiare i dati
                   x_prev = false;
                   y_prev = x_prev;  // animazione di rientro in sede del tag TD
                   animate({'top': "-=" + (offset().top - data("posizione").alto) + "px", 'left': "-=" + (offset().left - data("posizione").sinistra) + "px", opacity: 1, borderWidth: "0px"}, 650, "swing", function() {
                          with($(this)) {
                             if($.type(data("posizione")) !== "undefined") {
                                 removeClass("td_drag_drop_classe").removeAttr("class").removeAttr("style");
                                 addClass(data("classe_originale")); // Ripristino la classe originale
                                 removeData(); // Rimuove tutti i dati memorizzati nell'oggetto TD
                             }
                          } // Fine width
                   });  // Fine function animate
              }
          } // Fine with
    }  // Fine else
} // fine function td_drag_drop

// Di seguito funzioni per finestra modale
function toggleModal() {
   $('.mod_modal').toggleClass('mod_show-modal');
}

function windowOnClick(event) {
   event.stopPropagation();
   if (event.target.className == $('.mod_modal').attr('class')) {
        toggleModal();
        return true;
   }

   if($("DIV.mod_modal").length && 1 != $("DIV.mod_modal").find(event.target).length && window.id_div_aperti.length && window.id_div_aperti.length > 0) {
      if(event.target.tagName != "INPUT" && event.target.tagName != "BUTTON" && 1 != $("#" + "tbl_nome_" + window.id_div_aperti[window.id_div_aperti.length -1].split("-")[0]).find(event.target).length) {
          elimina_tabelle_aperte("Tpadrefittizia-Tfigliafittizia-00-veditabella"); // Su click nella finestra body principale chiudo le tabelle aperte
          set_elimina_add_record(false);
      }
   }
}  // fine function windowOnClick


function validateEmail($email) {
var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;

return emailReg.test($email);
}  // fine function validateEmail


function DataOraCorrente() {
  let oggi = new Date();
  let giorno = oggi.getDate();
  let mese = oggi.getMonth() + 1;
  let anno = oggi.getFullYear();
  let ore = oggi.getHours();
  let minuti = oggi.getMinutes();
  let secondi = oggi.getSeconds();
// Aggiungi uno zero iniziale se il numero è inferiore a 10
    giorno = giorno < 10 ? '0' + giorno : giorno;
  mese = mese < 10 ? '0' + mese : mese;
  ore = ore < 10 ? '0' + ore : ore;
  minuti = minuti < 10 ? '0' + minuti : minuti;
  secondi = secondi < 10 ? '0' + secondi : secondi;
// Formatta la data e l'ora nel formato desiderato (es. 01/01/2024 12:00:00)
return giorno + '/' + mese + '/' + anno + ' ' + ore + ':' + minuti + ':' + secondi;
}  // Fine function DataOraCorrente()
