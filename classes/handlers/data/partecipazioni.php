<?php

class DataHandlerPartecipazioni implements OpenPADataHandlerInterface
{
    public function __construct( array $Params )
    {
        $module = isset( $Params['Module'] ) ? $Params['Module'] : false;
        if ( $module instanceof eZModule )
        {
            $module->setTitle( "Partecipazioni" );
        }
    }

    public function getData()
    {
        $data = array();
        $enti = array();
        $series = array();
        //http://openpa.opencontent.it/Amministrazione-Trasparente/Enti-controllati/Societa-partecipate
        $treeNode = 'http://openpa.opencontent.it/api/opendata/v1/content/node/912'; 
        $parentObject = OpenPAApiNode::fromLink( $treeNode )->searchLocal();
        
        if ( $parentObject instanceof eZContentObject )
        {
            $params = array(
                'SearchLimit' => 1000,
                'Filter' => null,
                'SearchContentClassID' => array( 'ente_controllato'  ),
                'SearchSubTreeArray' => array( $parentObject->attribute( 'main_node_id' ) ),
                'Limitation' => array()
            );        
            $solrSearch = new eZSolr();
            $search = $solrSearch->search( '', $params );            
            if ( $search['SearchCount'] > 0 )
            {            
                $seriesTemp = array();
                $parentTemp = array();
                foreach( $search['SearchResult'] as $node )
                {
                    $dataMap = $node->attribute( 'data_map' );
                    $piva = isset( $dataMap['piva'] ) && $dataMap['piva']->hasContent() ? $dataMap['piva']->toString() : md5( $node->attribute( 'name' ) );               
                    $partecipazioneArray = isset( $dataMap['partecipazione'] ) ? $dataMap['partecipazione']->content() : array();
                    $partecipazione = $partecipazioneArray[0] == '0' ? 'Diretta' : 'Indiretta';                    
                    if ( $node->attribute( 'parent' ) instanceof eZContentObjectTreeNode )
                    {
                        $enti[$piva] = $node->attribute( 'name' ) . ' (' . $partecipazione . ')';
                        $parentNode = $node->attribute( 'parent' );
                        $seriesTemp[$piva][$parentNode->attribute('name')] = isset( $dataMap['percentuale_partecipazione'] ) ? $dataMap['percentuale_partecipazione']->toString() : 0;
                        $parentTemp[$parentNode->attribute('contentobject_id')] = $parentNode->attribute('name');
                    }                    
                }
                foreach( $seriesTemp as $piva => $values )
                {
                    foreach( $parentTemp as $parent )
                    {
                        if ( isset( $seriesTemp[$piva][$parent] ) )
                        {
                            $series[$parent][] = floatval( $seriesTemp[$piva][$parent] );
                        }
                        else
                        {
                            $series[$parent][] = 0;
                        }
                    }
                }
                $data = array(
                    'enti' => $enti,
                    'series' => $series,                
                );
            }
        }
        
        return $data;
    }
}