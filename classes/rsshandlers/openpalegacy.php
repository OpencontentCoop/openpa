<?php

class OpenPALegacyRSSHandler extends LegacyRSSHandler
{

    /**
     * @var eZRSSExport
     */
    protected $rssExport;

    protected $rssExportItems;

    function __construct( eZRSSExport $rssExport )
    {
        $this->rssExport = $rssExport;
        $this->rssExportItems = eZRSSExportItem::fetchFilteredList( array(
            'rssexport_id'  => $this->rssExport->attribute( 'id' ),
            'status'        => $this->rssExport->attribute( 'status' )
        ) );
    }

    function getAttributeMappings( eZContentObjectTreeNode $node = null )
    {
        $titleFields = (array) eZINI::instance( 'ocrss.ini' )->variable( 'FeedSettings', 'title' );
        $descriptionFields = (array) eZINI::instance( 'ocrss.ini' )->variable( 'FeedSettings', 'description' );
        $contentFields = (array) eZINI::instance( 'ocrss.ini' )->variable( 'FeedSettings', 'content' );
        $categoryFields = (array) eZINI::instance( 'ocrss.ini' )->variable( 'FeedSettings', 'category' );
        $enclosureFields = (array) eZINI::instance( 'ocrss.ini' )->variable( 'FeedSettings', 'enclosure' );

        $attributeMappings = eZRSSExportItem::getAttributeMappings( $this->rssExportItems );
        foreach ( $attributeMappings as $attributeMapping )
        {
            /** @var eZRSSExportItem $item */
            $item = $attributeMapping[0];
            array_unshift( $titleFields, $item->attribute( 'title' ) );
            array_unshift( $descriptionFields, $item->attribute( 'description' ) );
            array_unshift( $contentFields, $item->attribute( 'description' ) );
            array_unshift( $categoryFields, $item->attribute( 'category' ) );
            array_unshift( $enclosureFields, $item->attribute( 'enclosure' ) );
        }

        $dataMap = $node->attribute( 'data_map' );
        return array(
            'title' => $this->selectField( $titleFields, $dataMap ),
            'description' => $this->selectField( $descriptionFields, $dataMap ),
            'content' => $this->selectField( $contentFields, $dataMap ),
            'category' => $this->selectField( $categoryFields, $dataMap ),
            'enclosure' => $this->selectField( $enclosureFields, $dataMap )
        );
    }

    /**
     * @return eZContentObjectTreeNode[]
     */
    function getNodes()
    {
        $objectListFilter = $this->rssExport->getObjectListFilter();
        $classes = array();
        $subtree = array();
        foreach( $this->rssExportItems as $rssSource )
        {
            $parentNode = OpenPABase::fetchNode( $rssSource->SourceNodeID );
            $handler = OpenPAObjectHandler::instanceFromObject( $parentNode );
            if ( $handler instanceof OpenPAObjectHandler )
            {
                $virtualParameters = $handler->attribute( 'content_virtual' )->attribute( 'folder' );
                if ( $virtualParameters )
                {
                    $classes = array_merge( $classes, $virtualParameters['classes'] );
                    $subtree = array_merge( $subtree, $virtualParameters['subtree'] );
                }
                else
                {
                    $classes[] = eZContentClass::classIdentifierByID( $rssSource->ClassID );
                    $subtree[] = $parentNode->attribute('node_id');
                }
            }
        }
        $result = array();
        if ( isset( $subtree ) && isset( $classes ) )
        {
            $params = array(
                'SearchSubTreeArray' => array_unique( $subtree ),
                'SearchOffset' => 0,
                'SearchLimit' => $objectListFilter['number_of_objects'],
                'SearchContentClassID' => array_unique( $classes ),
                'SortBy' => array( 'published' => 'desc' )
            );
            $search = OpenPaFunctionCollection::search( $params );

            $result = $search['SearchResult'];
        }
        return $result;
    }

    /**
     * @return string
     */
    function getFeedTitle()
    {
        return $this->rssExport->attribute( 'title' );
    }

    /**
     * @return string
     */
    function getFeedAccessUrl()
    {
        return $this->rssExport->attribute( 'url' );
    }

    /**
     * @return string
     */
    function getFeedDescription()
    {
        return $this->rssExport->attribute( 'description' );
    }

    /**
     * @return string
     */
    function getFeedImageUrl()
    {
        return $this->rssExport->fetchImageURL();
    }

    function cacheKey()
    {
        return $this->rssExport->attribute( 'access_url' );
    }
}