<?php

class DataHandlerPolitici implements OpenPADataHandlerInterface
{
    public function __construct( array $Params )
    {
        $module = isset( $Params['Module'] ) ? $Params['Module'] : false;
        if ( $module instanceof eZModule )
        {
            $module->setTitle( "Politici" );
        }
    }

    public function getData()
    {
        $data = array();
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
                /** @var eZContentObjectAttribute[] $politicoDataMap */
                $politicoDataMap = $politico->attribute( 'data_map' );

                if ( isset( $politicoDataMap['gruppo_politico'] ) &&
                    $politicoDataMap['gruppo_politico'] instanceof eZContentObjectAttribute &&
                    $politicoDataMap['gruppo_politico']->hasContent() )
                {
                    $detailPolitici = explode( '-', $politicoDataMap['gruppo_politico']->toString() );
                    foreach( $detailPolitici as $detail )
                    {
                        $url = '/api/opendata/v1/content/object/' . $detail;
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
                    /** @var eZContentClassAttribute $reverseOrganoClassAttribute */
                    $reverseOrganoClassAttribute = eZContentClassAttribute::fetch( $reverseOrganoClassAttributeId );
                    /** @var eZContentObject[] $reverseOrgani */
                    foreach( $reverseOrgani as $reverseOrgano )
                    {
                        $name = $reverseOrganoClassAttribute->attribute( 'name' );
                        if ( $name == 'Membri' ) $name = "Membro";
                        $ruolo[] = $name . " di " . $reverseOrgano->attribute( 'name' );
                    }
                }

                $url = '/api/opendata/v1/content/object/' . $politico->attribute( 'id' );

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
        return $data;
    }
}