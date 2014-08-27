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
                $gruppoPolitico = $ruolo = array();
                $politicoDataMap = $politico->attribute( 'data_map' );

                if ( isset( $politicoDataMap['"gruppo_politico'] ) &&
                     $politicoDataMap['"gruppo_politico'] instanceof eZContentObjectAttribute &&
                     $politicoDataMap['"gruppo_politico']->hasContent() )
                {
                    $gruppoPolitico = explode( '-', $politicoDataMap['"gruppo_politico']->toString() );
                }

                if ( isset( $politicoDataMap['ruolo'] ) &&
                     $politicoDataMap['ruolo'] instanceof eZContentObjectAttribute &&
                     $politicoDataMap['ruolo']->hasContent() )
                {
                    $ruolo[] = $politicoDataMap['ruolo']->toString();
                }

                if ( isset( $politicoDataMap['"ruolo2'] ) &&
                     $politicoDataMap['"ruolo2'] instanceof eZContentObjectAttribute &&
                     $politicoDataMap['"ruolo2']->hasContent() )
                {
                    $ruolo[] = $politicoDataMap['"ruolo2']->toString();
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

                $politicoData = array(
                    'id' => $politico->attribute( 'id' ),
                    'nominativo' => $politico->attribute( 'name' ),
                    'ruolo' => $ruolo,
                    'gruppo_politico' => $gruppoPolitico
                );
                $data[] = $politicoData;
            }
        }
        break;
}

header('Content-Type: application/json');
echo json_encode( $data );
eZExecution::cleanExit();