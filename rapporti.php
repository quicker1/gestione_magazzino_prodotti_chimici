<?PHP  // michele.furlan@unipd.it   31 gennaio 2024

  if(!isset($_REQUEST['tbl_nome']))
      die('<SPAN class="classe_label">Manca la variabile _REQUEST[\'tbl_nome\']</SPAN>');

  switch($_REQUEST['tbl_nome']) {  // nome della tabella rapporto richiesta - segue la generazione della tabella temporanea avente nome presente in rapporti.php
     case 'spesaxgruppo' : {
         if(1 <> @$conn_obj->query_count('SELECT spesa_totale_x_gruppo('.$rpt['rapporti'][0].','.$rpt['rapporti'][1].')'))
             die('ERRORE in azione: '.$_REQUEST['tbl_nome']);
     break;
     }  // fine case 'spesaxgruppo'
     case 'spesadettaglio' : {
         if(1 <> @$conn_obj->query_count('SELECT spesa_x_gruppo_dettaglio('.$rpt['rapporti'][0].','.$rpt['rapporti'][1].')'))
             die('ERRORE in azione: '.$_REQUEST['tbl_nome']);
     break;
     }  // fine case 'spesadettaglio'
     case 'artdeposito' : {
         if(1 <> @$conn_obj->query_count('SELECT articoli_in_deposito('.$rpt['rapporti'][0].','.$rpt['rapporti'][1].')'))   // 1 == OK : 0 == errore
             die('ERRORE in azione: '.$_REQUEST['tbl_nome']);
     break;
     }  // fine case 'artdeposito'
     case 'proddeposito' : {
         if(1 <> @$conn_obj->query_count('SELECT prodotti_in_deposito('.$rpt['rapporti'][0].','.$rpt['rapporti'][1].')'))
             die('ERRORE in azione: '.$_REQUEST['tbl_nome']);
     break;
     }  // fine case 'proddeposito'
     case 'artvalore' : {
         if(1 <> @$conn_obj->query_count('SELECT articoli_x_valore('.$rpt['rapporti'][0].','.$rpt['rapporti'][1].')'))
             die('ERRORE in azione: '.$_REQUEST['tbl_nome']);
     break;
     }  // fine case 'artvalore'
     default : {
             die('<SPAN class="classe_label">RAPPORTO NON GESTITO</SPAN>');
     }
  }   // fine switch $_REQUEST['tbl_nome']

// Tabella con la spesa per gruppo degli articoli ritirati, e' una tabella temporanea.
$tabella['spesaxgruppo'] =  array('nometabella' => 'spesaxgruppo',    // Rapporto con le spese dei carrelli per gruppo
                                  'figlia' => array(),
                                  'chiave_padre' => '',
                                  'drag_drop' => FALSE,
                                  'nascondi_id' => FALSE,
                                  'intestazione' => 'Spese acquisto articoli per gruppo dal: '.($rpt_flag ? strftime('%d-%B-%Y', $rpt['rapporti'][0]) : '--NO DATA--').' al: '.($rpt_flag ? strftime('%d-%B-%Y', ($rpt['rapporti'][1] - SECONDI_AGGIUNTIVI)) : '--NO DATA--'),
                                  'ordine' => array('gruppo' => 'ASC'),
                                  'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => TRUE, 'paginazione' => 30),
                                  'where' => $rpt['filtro_gruppo'],
                                  'campi' => array('gruppo' => array('tipo' => 'text', 'etichetta' => 'Gruppo', 'editable' => FALSE, 'size_filtro' => 20, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 24, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                   'totale' => array('tipo' => 'text', 'etichetta' => 'Totale', 'editable' => FALSE, 'size_filtro' => 12, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 10, 'maxlength' => 20, 'pattern' => '', 'error_pattern' => '')),
                                                   'ivasc' => array('tipo' => 'text', 'etichetta' => 'Totale + Iva + Sconto', 'editable' => FALSE, 'size_filtro' => 12, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 10, 'maxlength' => 20, 'pattern' => '', 'error_pattern' => '')),
                                                   'calcolato75' => array('tipo' => 'calcolato', 'etichetta' => 'GFX', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => "CONCAT('<INPUT id=\"graphgroup_', id, '\" type=\"button\" class=\"emaildec center_elemento_imma\" value=\"&#x1F4C8;\" onClick=\"genera_grafico_js(\'',gruppo,'\',\'GROUP\');\" />')", 'ricalcolo' => FALSE)
                                             )
                            );

$tabella['spesadettaglio'] =  array('nometabella' => 'spesadettaglio',  // Spese dettagliate dei carrelli per gruppo
                                    'figlia' => array(),
                                    'chiave_padre' => '',
                                    'drag_drop' => FALSE,
                                    'nascondi_id' => FALSE,
                                    'intestazione' => 'Spese acquisto articoli dettaglio dal: '.($rpt_flag ? strftime('%d-%B-%Y', $rpt['rapporti'][0]) : '--NO DATA--').' al: '.($rpt_flag ? strftime('%d-%B-%Y', ($rpt['rapporti'][1] - SECONDI_AGGIUNTIVI)) : '--NO DATA--'),
                                    'ordine' => array('gruppo' => 'ASC'),
                                    'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => TRUE, 'paginazione' => 30),
                                    'where' => $rpt['filtro_gruppo'],
                                    'campi' => array('gruppo' => array('tipo' => 'text', 'etichetta' => 'Gruppo', 'editable' => FALSE, 'size_filtro' => 20, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 24, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                     'username' => array('tipo' => 'text', 'etichetta' => 'Utente', 'editable' => FALSE, 'size_filtro' => 18, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 24, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                     'id_ca' => array('tipo' => 'text', 'etichetta' => 'ID car.', 'editable' => FALSE, 'size_filtro' => 4, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 6, 'maxlength' => 20, 'pattern' => '', 'error_pattern' => '')),
                                                     'descrizione' => array('tipo' => 'text', 'etichetta' => 'Descrizione articolo', 'editable' => FALSE, 'size_filtro' => 36, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 40, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => '')),
                                                     'quantita' => array('tipo' => 'text', 'etichetta' => 'Qt.&agrave;', 'editable' => FALSE, 'size_filtro' => 3, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 5, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                     'prezzo' => array('tipo' => 'text', 'etichetta' => 'Prezzo&nbsp;&euro;', 'editable' => FALSE, 'size_filtro' => 5, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 8, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')),
                                                     'iva' => array('tipo' => 'text', 'etichetta' => 'Iva&nbsp;%', 'editable' => FALSE, 'size_filtro' => 3, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 4, 'maxlength' => 4, 'pattern' => '', 'error_pattern' => '')),
                                                     'sconto' => array('tipo' => 'text', 'etichetta' => 'Sconto&nbsp;%', 'editable' => FALSE, 'size_filtro' => 3, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 4, 'maxlength' => 4, 'pattern' => '', 'error_pattern' => '')),
                                                     'totale' => array('tipo' => 'text', 'etichetta' => 'Totale&nbsp;&euro;', 'editable' => FALSE, 'size_filtro' => 8, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 10, 'maxlength' => 20, 'pattern' => '', 'error_pattern' => '')),
                                                     'ivasc' => array('tipo' => 'text', 'etichetta' => 'Totale+Iva+Sconto&nbsp;&euro;', 'editable' => FALSE, 'size_filtro' => 12, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 10, 'maxlength' => 20, 'pattern' => '', 'error_pattern' => ''))
                                               )
                            );

$tabella['artdeposito'] =  array('nometabella' => 'artdeposito',    // tabella con gli articoli in deposito
                                 'figlia' => array('carartatt' => '<DIV>Carr.&nbsp;richiesti</DIV>', 'carartconf' => '<DIV>Carr.&nbsp;approvati</DIV>', 'carart' => '<DIV>Carr.&nbsp;ritirati</DIV>', 'ordart' => '<DIV>In ordinazione</DIV>', 'ordart_e' => '<DIV>Gi&agrave;&nbsp;caricati</DIV>'),
                                 'chiave_padre' => '',
                                 'drag_drop' => FALSE,
                                 'nascondi_id' => TRUE,  /* perche' genero un numero casuale quando ritorna ID o la funzione articoli_in_deposito altrimenti non ho id univoco nel gestionale */
                                 'intestazione' => 'Valori articoli in deposito dal: '.($rpt_flag ? strftime('%d-%B-%Y', $rpt['rapporti'][0]) : '--NO DATA--').' al: '.($rpt_flag ? strftime('%d-%B-%Y', ($rpt['rapporti'][1] - SECONDI_AGGIUNTIVI)) : '--NO DATA--'),
                                 'ordine' => array('descrizione' => 'ASC'),
                                 'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => TRUE, 'paginazione' => 30),
                                 'where' => '',
                                 'campi' => array('saldo' => array('tipo' => 'text', 'etichetta' => '<DIV>Saldo</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                  'ordinati' => array('tipo' => 'text', 'etichetta' => '<DIV>Ordinati</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                  'caricati' => array('tipo' => 'text', 'etichetta' => '<DIV>Caricati</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                  'calcolato57' => array('tipo' => 'calcolato', 'etichetta' => '<DIV>Giacenza</DIV>', 'size_filtro' => 2, 'vert_larghezza' => 0, 'formula' => "IF(giacenza < 1, CONCAT('<SPAN class=\"span_esaurito\">',giacenza,'</SPAN>'), giacenza)", 'ricalcolo' => FALSE),
                                                  'richiesti' => array('tipo' => 'text', 'etichetta' => '<DIV>Richiesti</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                  'approvati' => array('tipo' => 'text', 'etichetta' => '<DIV>Approvati</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                  'ritirati' => array('tipo' => 'text', 'etichetta' => '<DIV>Ritirati</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                  'codice' => array('tipo' => 'text', 'etichetta' => '<DIV>Cod. fornitore</DIV>', 'editable' => FALSE, 'size_filtro' => 8, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 10, 'maxlength' => 50, 'pattern' => '', 'error_pattern' => '')),
                                                  'descrizione' => array('tipo' => 'text', 'etichetta' => '<DIV>Desc. articolo</DIV>', 'editable' => FALSE, 'size_filtro' => 34, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 40, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => '')),
                                                  'pmarca' => array('tipo' => 'text', 'etichetta' => '<DIV>Marca</DIV>', 'editable' => FALSE, 'size_filtro' => 24, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 30, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => '')),
                                                  'fornitore' => array('tipo' => 'text', 'etichetta' => '<DIV>Fornitore</DIV>', 'editable' => FALSE, 'size_filtro' => 24, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 30, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => ''))
                                            )
                            );

$tabella['artvalore'] =  array('nometabella' => 'artvalore',    // tabella con gli articoli in deposito comprensiva dei dettagli sui valori economici
                               'figlia' => array('carartatt' => '<DIV>Carr.&nbsp;richiesti</DIV>', 'carartconf' => '<DIV>Carr.&nbsp;approvati</DIV>', 'carart' => '<DIV>Carr.&nbsp;itirati</DIV>'),
                               'chiave_padre' => '',
                               'drag_drop' => FALSE,
                               'nascondi_id' => TRUE,  /* perche' genero un numero casuale quando ritorna ID o la funzione articoli_in_deposito altrimenti non ho id univoco nel gestionale */
                               'intestazione' => 'Valori economici dei saldi in deposito degli articoli dal: '.($rpt_flag ? strftime('%d-%B-%Y', $rpt['rapporti'][0]) : '--NO DATA--').' al: '.($rpt_flag ? strftime('%d-%B-%Y', ($rpt['rapporti'][1] - SECONDI_AGGIUNTIVI)) : '--NO DATA--'),
                               'ordine' => array('descrizione' => 'ASC'),
                               'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => TRUE, 'paginazione' => 30),
                               'where' => '',
                               'campi' => array('saldo' => array('tipo' => 'text', 'etichetta' => '<DIV>Saldo</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                'ordinati' => array('tipo' => 'text', 'etichetta' => '<DIV>Ordinati</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                'caricati' => array('tipo' => 'text', 'etichetta' => '<DIV>Caricati</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                'giacenza' => array('tipo' => 'text', 'etichetta' => '<DIV>Giacenza</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                'prezzo' => array('tipo' => 'text', 'etichetta' => '<DIV>Prezzo</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                'iva' => array('tipo' => 'text', 'etichetta' => '<DIV>Iva&nbsp;%</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                'sconto' => array('tipo' => 'text', 'etichetta' => '<DIV>Sconto&nbsp;%</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                'totale_g' => array('tipo' => 'text', 'etichetta' => '<DIV>Prezzo<BR />*Giacenza</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                'totivasc' => array('tipo' => 'text', 'etichetta' => '<DIV>Prezzo<BR />*Giacenza<BR />*Iva-Sc.</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                'richiesti' => array('tipo' => 'text', 'etichetta' => '<DIV>Richiesti</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                'approvati' => array('tipo' => 'text', 'etichetta' => '<DIV>Approvati</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                'ritirati' => array('tipo' => 'text', 'etichetta' => '<DIV>Ritirati</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 100, 'pattern' => '', 'error_pattern' => '')),
                                                'codice' => array('tipo' => 'text', 'etichetta' => '<DIV>Cod. fornitore</DIV>', 'editable' => FALSE, 'size_filtro' => 8, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 10, 'maxlength' => 50, 'pattern' => '', 'error_pattern' => '')),
                                                'descrizione' => array('tipo' => 'text', 'etichetta' => '<DIV>Desc. articolo</DIV>', 'editable' => FALSE, 'size_filtro' => 34, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 40, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => '')),
                                                'pmarca' => array('tipo' => 'text', 'etichetta' => '<DIV>Marca</DIV>', 'editable' => FALSE, 'size_filtro' => 24, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 30, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => '')),
                                                'fornitore' => array('tipo' => 'text', 'etichetta' => '<DIV>Fornitore</DIV>', 'editable' => FALSE, 'size_filtro' => 24, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 30, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => ''))
                                          )
                            );

$tabella['proddeposito'] =  array('nometabella' => 'proddeposito',    // tabella con gli articoli in deposito - tabella temporanea valida solo per la sessione in corrente
                                  'figlia' => array('carartatt' => '<DIV>Carr.&nbsp;richiesti</DIV>', 'carartconf' => '<DIV>Carr.&nbsp;approvati</DIV>', 'carart' => '<DIV>Carr.&nbsp;ritirati</DIV>', 'ordart' => '<DIV>In ordinazione</DIV>', 'ordart_e' => '<DIV>Gi&agrave;&nbsp;caricati</DIV>', 'artprod' => '<DIV>Articoli</DIV>', 'prod' => '<DIV>Prod. dettagli</DIV>'),
                                  'chiave_padre' => '',
                                  'drag_drop' => FALSE,
                                  'nascondi_id' => FALSE,  /* perche' genero un numero casuale quando ritorna ID o la funzione articoli_in_deposito altrimenti non ho id univoco nel gestionale */
                                  'intestazione' => 'Valori prodotti in deposito dal: '.($rpt_flag ? strftime('%d-%B-%Y', $rpt['rapporti'][0]) : '--NO DATA--').' al: '.($rpt_flag ? strftime('%d-%B-%Y', ($rpt['rapporti'][1] - SECONDI_AGGIUNTIVI)) : '--NO DATA--'),
                                  'ordine' => array('nome' => 'ASC'),
                                  'pulsanti' => array('verticale' => FALSE, 'hidevert' => TRUE, 'chiave_figlia' => '', 'add' => FALSE, 'delete' => FALSE, 'filtro' => TRUE, 'paginazione' => 30),
                                  'where' => '',
                                  'campi' => array('stato' => array('tipo' => 'text', 'etichetta' => '<DIV>Stato</DIV>', 'editable' => FALSE, 'size_filtro' => 10, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 12, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')),
                                                   'saldo' => array('tipo' => 'text', 'etichetta' => '<DIV>Saldo</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')),
                                                   'ordinati' => array('tipo' => 'text', 'etichetta' => '<DIV>Ordinati</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')),
                                                   'caricati' => array('tipo' => 'text', 'etichetta' => '<DIV>Caricati</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')),
                                                   'calcolato59' => array('tipo' => 'calcolato', 'etichetta' => '<DIV>Giacenza netta</DIV>', 'size_filtro' => 2, 'vert_larghezza' => 0, 'formula' => "IF(giacenza < 1, CONCAT('<SPAN class=\"span_esaurito\">',giacenza,'</SPAN>'), giacenza)", 'ricalcolo' => FALSE),
                                                   'giacenzalorda' => array('tipo' => 'text', 'etichetta' => '<DIV>Giacenza lorda</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')),
                                                   'richiesti' => array('tipo' => 'text', 'etichetta' => '<DIV>Richiesti</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')),
                                                   'approvati' => array('tipo' => 'text', 'etichetta' => '<DIV>Approvati</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')),
                                                   'ritirati' => array('tipo' => 'text', 'etichetta' => '<DIV>Ritirati</DIV>', 'editable' => FALSE, 'size_filtro' => 2, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 3, 'maxlength' => 10, 'pattern' => '', 'error_pattern' => '')),
                                                   'nome' => array('tipo' => 'text', 'etichetta' => '<DIV>Nome</DIV>', 'editable' => FALSE, 'size_filtro' => 24, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 30, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => '')),
                                                   'marca' => array('tipo' => 'text', 'etichetta' => '<DIV>Marca</DIV>', 'editable' => FALSE, 'size_filtro' => 24, 'vert_larghezza' => 0, 'attributi' => array('default_value' => '', 'size' => 30, 'maxlength' => 255, 'pattern' => '', 'error_pattern' => '')),
                                                   'calcolato74' => array('tipo' => 'calcolato', 'etichetta' => 'GFX', 'size_filtro' => 0, 'vert_larghezza' => 0, 'formula' => "CONCAT('<INPUT id=\"graphprod_', id, '\" type=\"button\" class=\"emaildec center_elemento_imma\" value=\"&#x1F4C8;\" onClick=\"genera_grafico_js(',id,',\'PROD\');\" />')", 'ricalcolo' => FALSE),
                                                   'calcolato60' => array('tipo' => 'calcolato', 'etichetta' => '<DIV>CHECK</DIV>', 'size_filtro' => 2, 'vert_larghezza' => 0, 'formula' => "IF(qtest <> 0, CONCAT('<SPAN class=\"span_verificare\">',qtest,'</SPAN>'), qtest)", 'ricalcolo' => FALSE)
                                             )
                            );

?>