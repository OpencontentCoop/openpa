<?php

class OpenPAAppSectionHelper
{
    private static $_instance;
    
    protected $rootNode;
    
    const ROOT_CLASSIDENTIFIER = "apps_container";
    
    const NAVIGATION_IDENTIFIER = "ezappsnavigationpart";
    const SECTION_IDENTIFIER = "apps";
    const SECTION_NAME = "Apps";

    protected function __construct()
    {
        $this->bc();
    }

    protected function bc()
    {
        $section = eZSection::fetchByIdentifier( 'apps', false );
        if ( isset( $section['id'] ) && $section['name'] != self::SECTION_NAME )
        {
            $section = eZSection::fetch( $section['id'] );
            if ( $section instanceof eZSection )
            {
                $section->setAttribute( 'name', self::SECTION_NAME );
                $section->store();
            }
        }
        $root = $this->rootNode( false );
        if ( $root instanceof eZContentObjectTreeNode )
        {
            if (strpos( strtolower( $root->attribute( 'name' ) ), 'mobile' ) !== false )
            {
                $params = array(
                    'attributes' => array(
                        'titolo' => 'Applicazioni'
                    )
                );

                eZContentFunctions::updateAndPublishObject(
                    $root->attribute( 'object' ),
                    $params
                );
            }

            $remote = self::appsContainerRemoteId();
            if ( $remote !== $root->attribute( 'object' )->attribute( 'remote_id' ) )
            {
                $root->attribute( 'object' )->setAttribute( 'remote_id', $remote );
                $root->attribute( 'object' )->store();
            }
        }
    }

    /**
     * @return OpenPAAppSectionHelper
     */
    public static function instance()
    {
        if ( !self::$_instance instanceof OpenPAAppSectionHelper )
        {
            self::$_instance = new OpenPAAppSectionHelper();
        }
        return self::$_instance;
    }

    /**
     * @param bool $createIfNotExists
     *
     * @return eZContentObjectTreeNode
     * @throws Exception
     */
    public function rootNode( $createIfNotExists = true )
    {
        if ( $this->rootNode === null )
        {
            /*
            $params = array( 'Depth' => 1,
                             'DepthOperator' => 'eq',
                             'ClassFilterType' => 'include',
                             'ClassFilterArray' => array( self::ROOT_CLASSIDENTIFIER ),
                             'Limitation' => array(),
                             'Limit' => 1 );
    
            if ( eZContentObjectTreeNode::subTreeCountByNodeID( $params, 1 ) )
            {
                $rootChildren = eZContentObjectTreeNode::subTreeByNodeID( $params, 1 );
                $this->rootNode = $rootChildren[0];
            }
            */
            $root = eZContentObject::fetchByRemoteID( self::appsContainerRemoteId() );
            if ( $root instanceof eZContentObject )
            {
                if ( $root->attribute( 'main_node' ) instanceof eZContentObjectTreeNode )
                {
                    $this->rootNode = $root->attribute( 'main_node' );
                }
                else
                {
                    throw new Exception( "Problem with object {$root->attribute( 'id' )}: no main node found" );
                }
            }
            elseif( $createIfNotExists )
            {
                $this->rootNode = self::createRootNode();
            }
        }
        return $this->rootNode;
    }

    /**
     * @return eZContentObjectTreeNode
     * @throws Exception
     */
    protected static function createRootNode()
    {
        OpenPAClassTools::installClasses( array( self::ROOT_CLASSIDENTIFIER ) );

        $section = OpenPABase::initSection(
            self::SECTION_NAME,
            self::SECTION_IDENTIFIER,
            self::NAVIGATION_IDENTIFIER
        );
        
        $params = array(
            'parent_node_id' => 1,
            'section_id' => $section->attribute( 'id' ),
            'remote_id' => self::appsContainerRemoteId(),
            'class_identifier' => self::ROOT_CLASSIDENTIFIER,
            'attributes' => array(
                'titolo' => 'Applicazioni'
            )
        );

        /** @var eZContentObject $contentObject */
        $contentObject = eZContentFunctions::createAndPublishObject( $params );
        if( !$contentObject instanceof eZContentObject )
        {
            throw new Exception( 'Failed creating Apps root node' );
        }
        return $contentObject->attribute( 'main_node' );
    }

    public static function appsContainerRemoteId()
    {
        return OpenPABase::getCurrentSiteaccessIdentifier() . '_' . self::SECTION_IDENTIFIER;
    }
    
}
    