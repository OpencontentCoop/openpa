<?php
/** @var eZModule $module */
$module = $Params['Module'];
$identifier = $Params['Identifier'];
$data = array();

switch( $identifier )
{
    case 'consiglieri':
        $politicoClassId = eZContentClass::classIDByIdentifier( 'politico' );
        $politici = eZContentObject::fetchSameClassList( $politicoClassId );
        //$search = eZSearch::search(
        //    "Consiglio comunale",
        //    array( 'SearchContentClassID' => 'organo_politico' )
        //);
        //    
        //if ( $search['SearchCount'] > 0 )
        //{
        //    $organoPolitico = $search['SearchResult'][0];
        //    $organoPoliticoDataMap = $organoPolitico->attribute( 'data_map' );            
        //    if ( isset( $organoPoliticoDataMap['membri'] ) &&
        //         $organoPoliticoDataMap['membri'] instanceof eZContentObjectAttribute &&
        //         $organoPoliticoDataMap['membri']->hasContent() )
        //    {
        //        $politici = explode( '-', $organoPoliticoDataMap['membri']->toString() );
        //    }            
        //}
        foreach( $politici as $politico )
        {
            //$politico = eZContentObject::fetch( $id );
            if ( $politico instanceof eZContentObject
                 && $politico->attribute( 'can_read' ) )
            {
                $gruppoPolitico = $lista = $parte = $ruolo = array();
                $politicoDataMap = $politico->attribute( 'data_map' );

                if ( isset( $politicoDataMap['gruppo_politico'] ) &&
                     $politicoDataMap['gruppo_politico'] instanceof eZContentObjectAttribute &&
                     $politicoDataMap['gruppo_politico']->hasContent() )
                {
                    $detailPolitici = explode( '-', $politicoDataMap['gruppo_politico']->toString() );
                    foreach( $detailPolitici as $detail )
                    {
                        $url = '/api/opendata/v1/content/object/' . $detail;
                        eZURI::transformURI( $url, false, 'full' );
                        $gruppoPolitico[] = $url;
                    }
                }
                
                if ( isset( $politicoDataMap['lista_elettorale'] ) &&
                     $politicoDataMap['lista_elettorale'] instanceof eZContentObjectAttribute &&
                     $politicoDataMap['lista_elettorale']->hasContent() )
                {
                    $detailPolitici = explode( '-', $politicoDataMap['lista_elettorale']->toString() );
                    foreach( $detailPolitici as $detail )
                    {
                        $url = '/api/opendata/v1/content/object/' . $detail;
                        eZURI::transformURI( $url, false, 'full' );
                        $lista[] = $url;
                    }
                }
                
                if ( isset( $politicoDataMap['maggioranza_minoranza'] ) &&
                     $politicoDataMap['maggioranza_minoranza'] instanceof eZContentObjectAttribute &&
                     $politicoDataMap['maggioranza_minoranza']->hasContent() )
                {
                    $detailPolitici = explode( '-', $politicoDataMap['maggioranza_minoranza']->toString() );
                    foreach( $detailPolitici as $detail )
                    {
                        $url = '/api/opendata/v1/content/object/' . $detail;
                        eZURI::transformURI( $url, false, 'full' );
                        $parte[] = $url;
                    }
                }

                if ( isset( $politicoDataMap['ruolo'] ) &&
                     $politicoDataMap['ruolo'] instanceof eZContentObjectAttribute &&
                     $politicoDataMap['ruolo']->hasContent() )
                {
                    $ruolo[] = $politicoDataMap['ruolo']->toString();
                }

                if ( isset( $politicoDataMap['ruolo2'] ) &&
                     $politicoDataMap['ruolo2'] instanceof eZContentObjectAttribute &&
                     $politicoDataMap['ruolo2']->hasContent() )
                {
                    $ruolo[] = $politicoDataMap['ruolo2']->toString();
                }
                
                $classAttribute = "organo_politico/membri";
                $attributeID = eZContentObjectTreeNode::classAttributeIDByIdentifier( $classAttribute );
                $reverseOrganiPolitici = $politico->reverseRelatedObjectList( false, $attributeID, true );
                foreach( $reverseOrganiPolitici as $reverseOrganoClassAttributeId => $reverseOrgani )
                {
                    $reverseOrganoClassAttribute = eZContentClassAttribute::fetch( $reverseOrganoClassAttributeId );
                    foreach( $reverseOrgani as $reverseOrgano )
                    {
                        $name = $reverseOrganoClassAttribute->attribute( 'name' );
                        if ( $name == 'Membri' ) $name = "Membro";
                        $ruolo[] = $name . " di " . $reverseOrgano->attribute( 'name' );
                    }
                }

                $url = '/api/opendata/v1/content/object/' . $politico->attribute( 'id' );
                eZURI::transformURI( $url, false, 'full' );
                
                $politicoData = array(
                    'id' => $politico->attribute( 'id' ),
                    'api' => $url,
                    'nominativo' => $politico->attribute( 'name' ),
                    'ruolo' => $ruolo,
                    'gruppo_politico' => $gruppoPolitico,
                    'lista' => $lista,
                    'parte' => $parte
                );
                $data[] = $politicoData;
            }
        }
        break;
}

header('Content-Type: application/json');
echo json_encode( $data );
eZExecution::cleanExit();