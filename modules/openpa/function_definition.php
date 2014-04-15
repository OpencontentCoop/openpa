<?php

$FunctionList = array();
$FunctionList['ruoli'] = array( 'name' => 'ruoli',
                                'operation_types' => array( 'read' ),
                                'call_method' => array( 'include_file' => 'extension/openpa/classes/openpafunctioncollection.php',
                                                        'class' => 'OpenPaFunctionCollection',
                                                        'method' => 'fetchRuoli' ),
                                'parameter_type' => 'standard',
                                'parameters' => array(
                                                       array( 'name' => 'struttura_object_id',
                                                              'type' =>'integer',
                                                              'required' => false,
                                                              'default' => false ),
                                                       array( 'name' => 'dipendente_object_id',
                                                              'type' =>'integer',
                                                              'required' => false,
                                                              'default' => false )
                                                    )
                                );

$FunctionList['footer_links'] = array(  'name' => 'footer_links',
                                'operation_types' => array( 'read' ),
                                'call_method' => array( 'include_file' => 'extension/openpa/classes/openpafunctioncollection.php',
                                                        'class' => 'OpenPaFunctionCollection',
                                                        'method' => 'fetchFooterLinks' ),
                                'parameter_type' => 'standard',
                                'parameters' => array()
                                );

$FunctionList['footer_notes'] = array(  'name' => 'footer_notes',
                                'operation_types' => array( 'read' ),
                                'call_method' => array( 'include_file' => 'extension/openpa/classes/openpafunctioncollection.php',
                                                        'class' => 'OpenPaFunctionCollection',
                                                        'method' => 'fetchFooterNotes' ),
                                'parameter_type' => 'standard',
                                'parameters' => array()
                                );

$FunctionList['aree'] = array(  'name' => 'aree',
                                'operation_types' => array( 'read' ),
                                'call_method' => array( 'include_file' => 'extension/openpa/classes/openpafunctioncollection.php',
                                                        'class' => 'OpenPaFunctionCollection',
                                                        'method' => 'fetchAree' ),
                                'parameter_type' => 'standard',
                                'parameters' => array()
                                );

$FunctionList['servizi'] = array( 'name' => 'servizi',
                                  'operation_types' => array( 'read' ),
                                  'call_method' => array( 'include_file' => 'extension/openpa/classes/openpafunctioncollection.php',
                                                          'class' => 'OpenPaFunctionCollection',
                                                          'method' => 'fetchServizi' ),
                                  'parameter_type' => 'standard',
                                  'parameters' => array()
                                );

$FunctionList['uffici'] = array( 'name' => 'uffici',
                               'operation_types' => array( 'read' ),
                               'call_method' => array( 'include_file' => 'extension/openpa/classes/openpafunctioncollection.php',
                                                        'class' => 'OpenPaFunctionCollection',
                                                        'method' => 'fetchUffici' ),
                               'parameter_type' => 'standard',
                               'parameters' => array()
                                );

$FunctionList['strutture'] = array( 'name' => 'strutture',
                               'operation_types' => array( 'read' ),
                               'call_method' => array( 'include_file' => 'extension/openpa/classes/openpafunctioncollection.php',
                                                        'class' => 'OpenPaFunctionCollection',
                                                        'method' => 'fetchStrutture' ),
                               'parameter_type' => 'standard',
                               'parameters' => array()
                                );

$FunctionList['dipendenti'] = array( 'name' => 'dipendenti',
                               'operation_types' => array( 'read' ),
                               'call_method' => array( 'include_file' => 'extension/openpa/classes/openpafunctioncollection.php',
                                                        'class' => 'OpenPaFunctionCollection',
                                                        'method' => 'fetchDipendenti' ),
                               'parameter_type' => 'standard',
                               'parameters' => array(
                                                       array( 'name' => 'struttura',
                                                              'type' =>'object',
                                                              'required' => false,
                                                              'default' => false ),
                                                       array( 'name' => 'subtree',
                                                              'type' => 'array',
                                                              'required' => false,
                                                              'default' => false )                                                       
                                                    )
                                );

$FunctionList['header_banner_background_style'] = array( 'name' => 'header_image',
                               'operation_types' => array( 'read' ),
                               'call_method' => array( 'include_file' => 'extension/openpa/classes/openpafunctioncollection.php',
                                                        'class' => 'OpenPaFunctionCollection',
                                                        'method' => 'fetchHeaderImageStyle' ),
                               'parameter_type' => 'standard',
                               'parameters' => array()
                                );

$FunctionList['header_logo_background_style'] = array( 'name' => 'header_logo',
                               'operation_types' => array( 'read' ),
                               'call_method' => array( 'include_file' => 'extension/openpa/classes/openpafunctioncollection.php',
                                                        'class' => 'OpenPaFunctionCollection',
                                                        'method' => 'fetchHeaderLogoStyle' ),
                               'parameter_type' => 'standard',
                               'parameters' => array()
                                );

$FunctionList['calendario_eventi'] = array( 'name' => 'eventi',
                               'operation_types' => array( 'read' ),
                               'call_method' => array( 'include_file' => 'extension/openpa/classes/openpafunctioncollection.php',
                                                        'class' => 'OpenPaFunctionCollection',
                                                        'method' => 'fetchCalendarioEventi' ),
                               'parameter_type' => 'standard',
                               'parameters' => array(
                                                       array( 'name' => 'calendar',
                                                              'type' => 'object',
                                                              'required' => true,
                                                              'default' => false ),
                                                       array( 'name' => 'params',
                                                              'type' => 'array',
                                                              'required' => true,
                                                              'default' => array() )
                                                    )
                                );

?>
