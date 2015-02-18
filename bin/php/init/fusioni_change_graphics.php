<?php

if ( !$reload )
{    
    $identifier = OpenPABase::getCurrentSiteaccessIdentifier();
    $db = eZDB::instance();
    
    $data = array();
    
    $string = "http://up.opencontent.it/html/grafici/openpafusioni/grafico_popolazione/";
    $replace = "http://up.opencontent.it/grafici/{$identifier}/grafico_popolazione/";
    $queryReplace = "UPDATE ezcontentobject_attribute SET data_text = replace( data_text, '$string', '$replace' ) WHERE data_text LIKE '%$string%'";    
    $row = $db->query( $queryReplace );
    $data[] = $replace;
    
    $string = "http://up.opencontent.it/Highcharts-4.0.4/fusioni/combo/servizi_openpafusioni.html";
    $replace = "http://up.opencontent.it/Highcharts-4.0.4/fusioni/combo/servizi_{$identifier}.htm";
    $queryReplace = "UPDATE ezcontentobject_attribute SET data_text = replace( data_text, '$string', '$replace' ) WHERE data_text LIKE '%$string%'";    
    $row = $db->query( $queryReplace );
    $data[] = $replace;
    
    $string = "http://up.opencontent.it/Highcharts-4.0.4/fusioni/combo/sportivi_openpafusioni.htm";
    $replace = "http://up.opencontent.it/Highcharts-4.0.4/fusioni/combo/sportivi_{$identifier}.htm";
    $queryReplace = "UPDATE ezcontentobject_attribute SET data_text = replace( data_text, '$string', '$replace' ) WHERE data_text LIKE '%$string%'";
    $row = $db->query( $queryReplace );
    $data[] = $replace;
    
    $string = "http://up.opencontent.it/Highcharts-4.0.4/fusioni/area-stacked/contributi_openpafusioni.htm";
    $replace = "http://up.opencontent.it/Highcharts-4.0.4/fusioni/area-stacked/contributi_{$identifier}.htm";
    $queryReplace = "UPDATE ezcontentobject_attribute SET data_text = replace( data_text, '$string', '$replace' ) WHERE data_text LIKE '%$string%'";
    $row = $db->query( $queryReplace );
    $data[] = $replace;
    
    $string = "http://timemapper.okfnlabs.org/ocfnardelli/storia-openpafusioni?embed=1";
    $replace = "http://timemapper.okfnlabs.org/ocfnardelli/storia-{$identifier}?embed=1";
    $queryReplace = "UPDATE ezcontentobject_attribute SET data_text = replace( data_text, '$string', '$replace' ) WHERE data_text LIKE '%$string%'";
    $row = $db->query( $queryReplace );
    $data[] = $replace;        
    
    $mail = new eZMail();                                
    $mail->setSender( eZINI::instance()->variable( 'MailSettings', 'AdminEmail' ) );            
    $mail->setReceiver( 'francesco.nardelli@opencontent.it' );
    $mail->addCc( 'lr@opencontent.it' );
    $mail->setSubject( "Promemoria grafici per sito {$identifier}" );
    $mail->setBody( "Ciao questo è un promemoria: devi implementare i seguenti grafici: \n" . implode( "\n", $data ) );
    eZMailTransport::send( $mail );
}