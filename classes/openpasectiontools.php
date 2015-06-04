<?php


class OpenPASectionTools
{
    /**
     * @var int[] class id
     */
    protected $classIdList;

    /**
     * @var int[]
     */
    protected $rootNodeIdList;

    /**
     * @var string[]  class identifier => attribute identifier
     */
    protected $dataTimeAttributeIdentifierList;

    /**
     * array int[]  class identifier => section id
     */
    protected $sectionIdList;

    /**
     * int section id
     */
    protected $defaultSectionId;

    /**
     * int[]  class identifier => seconds
     */
    protected $secondsExpire;

    /**
     * bool|array class => maggiore|minore
     */
    protected $overrideValue;

    /**
     * array bool| array class => attributo|valore
     */
    protected $ignore;

    /**
     * int seconds
     */
    protected $defaultSecondExpire;

    /**
     * int
     */
    protected $now;

    /**
     * @var eZContentObjectTreeNode
     */
    protected $currentRootNode;

    /**
     * string
     */
    protected $currentClassIdentifier;

    protected $currentOverrideValue;

    protected $currentIgnore;

    protected $currentSecondsExpire;

    /**
     * @var eZContentObjectAttribute
     */
    protected $currentUnPublishDateAttribute;

    protected $currentSectionDestinationId;

    /**
     * bool
     */
    protected $log;

    /**
     * @var eZCLI
     */
    protected $cli;

    protected static $changeNodeIds = array();

    public function __construct()
    {
        $this->classIdList = OpenPAINI::variable( 'ChangeSection','ClassList' );
        $this->rootNodeIdList = OpenPAINI::variable( 'ChangeSection','RootNodeList' );
        $this->dataTimeAttributeIdentifierList =  OpenPAINI::variable( 'ChangeSection','DataTime' );
        $this->sectionIdList =  OpenPAINI::variable( 'ChangeSection','ToSection' );
        $this->defaultSectionId =  OpenPAINI::variable( 'ChangeSection','ToSectionDefault', 0 );
        $this->secondsExpire = OpenPAINI::variable( 'ChangeSection','ScadeDopoTotSecondi', 0 );
        $this->overrideValue = OpenPAINI::variable( 'ChangeSection','UsaValore', false );
        $this->ignore = OpenPAINI::variable( 'ChangeSection','Ignora', false );
        $this->defaultSecondExpire = OpenPAINI::variable( 'ChangeSection','ScadeDopoTotSecondiDefault', 0 );
        $this->now = time();

        $this->log = false;
        $this->cli = eZCLI::instance();
    }

    public function setLog( $bool )
    {
        $this->log = $bool;
    }

    public function result()
    {
        return self::$changeNodeIds;
    }

    public function changeAllSubTreeSection()
    {
        $moveToTrashNodes = array();
        foreach( $this->rootNodeIdList as $classIdentifier => $nodeId )
        {
            try
            {
                $this->changeSubTreeSectionForClass( $nodeId, $classIdentifier, $moveToTrashNodes );
            }
            catch( Exception $e )
            {
                if ( $this->log ) $this->cli->error( $e->getMessage() );
            }
        }
        $this->removeNodes( $moveToTrashNodes );
    }

    public function changeSubTreeSectionForClass( $subTreeNodeId, $classIdentifier, &$moveToTrashNodes )
    {
        $this->getCurrentRootNode( $subTreeNodeId );
        $this->getCurrentParameters( $classIdentifier );
        $humanSecondsExpire = intval( $this->currentSecondsExpire / 60 / 60 / 24 / 365 );

        if ( $this->log )
        {
            $this->cli->output( "classe: {$this->currentClassIdentifier} ", false );
            $this->cli->output( "subtree: {$this->currentRootNode->attribute( 'node_id' )} ", false );
            $this->cli->output( "attributo: {$this->dataTimeAttributeIdentifierList[$this->currentClassIdentifier]} ", false );
            $this->cli->output( "secondi: {$this->currentSecondsExpire} ($humanSecondsExpire anni) ", false );
            if ( $this->currentOverrideValue )
            {
                $this->cli->output( "usa il valore {$this->currentOverrideValue}" );
            }
            if ( $this->currentIgnore )
            {
                $this->cli->output( "ignora {$this->currentIgnore}" );
            }
            $this->cli->output();
        }

        /** @var eZContentObjectTreeNode[] $nodeArray */
        $nodeArray = $this->currentRootNode->subTree( array(
                'ClassFilterType' => 'include',
                'ClassFilterArray' => array( $this->currentClassIdentifier ),
                'LoadDataMap' => false,
                'Limitation' => array(),
                'AttributeFilter' => array( array( 'section', '!=', $this->currentSectionDestinationId ) )
            )
        );

        $count = count( $nodeArray );
        if ( $count > 0 )
        {
            $progressBar = false;
            if ( $this->log )
            {
                $output = new ezcConsoleOutput();
                $progressBarOptions = array( 'emptyChar' => ' ', 'barChar'  => '=' );
                $progressBar = new ezcConsoleProgressbar( $output, intval( $count ), $progressBarOptions );
                $progressBar->start();
            }

            foreach ( $nodeArray as $currentNode )
            {
                if ( $this->log ) $progressBar->advance();
                $result = $this->changeNodeSection( $currentNode, $moveToTrashNodes );
                if ( $this->log && $result ) $this->cli->output( '*' );
            }

            if ( $this->log ) $progressBar->finish();
        }

        if ( $this->log )
        {
            $this->cli->output();
            $memoryMax = memory_get_peak_usage(); // Result is in bytes
            $memoryMax = round( $memoryMax / 1024 / 1024, 2 ); // Convert in Megabytes
            $this->cli->output( ' Memoria usata: '.$memoryMax.'M' );
        }
    }

    public function test( $currentNode )
    {
        if ( is_numeric( $currentNode ) )
        {
            $currentNode = eZContentObjectTreeNode::fetch( $currentNode );
        }
        if ( $currentNode instanceof eZContentObjectTreeNode )
        {
            $this->getCurrentParameters( $currentNode->attribute( 'class_identifier' ) );
            $this->currentUnPublishDateAttribute = $this->getCurrentUnPublishAttribute( $currentNode );
            $currentObject = $currentNode->attribute( 'object' );
            $handler = OpenPAObjectHandler::instanceFromContentObject( $currentObject );            
            $date = $this->currentUnPublishDateAttribute->content();
            $attributeRetractDate = $date->attribute( 'timestamp' );
            $iniRetractDate = $currentObject->attribute( 'published' ) + $this->currentSecondsExpire;
            $objectRetractDate = $this->getRetractDate( $attributeRetractDate, $iniRetractDate, $this->currentIgnore, $this->currentOverrideValue );
            $this->cli->warning( "Test 1: " . var_export( $objectRetractDate > 0, 1 ) );
            $this->cli->warning( "Test 2: " . var_export( $objectRetractDate < $this->now, 1 ) );
            $this->cli->warning( "Test 3: " . var_export( $currentObject->attribute( 'section_id' ) != $this->currentSectionDestinationId, 1 ) );
            $this->cli->warning( "Test 4: " . var_export( $this->currentSectionDestinationId !== 0, 1 ) );
            $this->cli->warning( "Test 4: " . var_export( $handler->filter( 'change_section', 'run' ) == OpenPAObjectHandler::FILTER_CONTINUE, 1 ) );
        }
    }
    
    public function changeSection( $currentNode )
    {
        if ( is_numeric( $currentNode ) )
        {
            $currentNode = eZContentObjectTreeNode::fetch( $currentNode );
        }
        if ( $currentNode instanceof eZContentObjectTreeNode )
        {
            $this->getCurrentParameters( $currentNode->attribute( 'class_identifier' ) );
            $moveToTrashNodes = array();
            $this->changeNodeSection( $currentNode, $moveToTrashNodes );
            $this->removeNodes( $moveToTrashNodes );
        }
    }

    public function removeNodes( $trashNodes )
    {
        if ( count( $trashNodes ) > 0 )
        {
            if ( $this->log ) $this->cli->output();

            foreach( $trashNodes as $nodeId )
            {
                eZContentObjectTreeNode::removeSubtrees( array( $nodeId ), true );
                if ( $this->log )
                {
                    $memoryMax = memory_get_peak_usage(); // Result is in bytes
                    $memoryMax = round( $memoryMax / 1024 / 1024, 2 ); // Convert in Megabytes
                    $this->cli->output( "Sposto nel cestino il node #$nodeId (" .$memoryMax.'M)' );
                }
            }
        }
    }

    protected function changeNodeSection( eZContentObjectTreeNode $currentNode, &$moveToTrashNodes )
    {
        if ( !isset( self::$changeNodeIds[$this->currentClassIdentifier]  ) )
        {
            self::$changeNodeIds[$this->currentClassIdentifier] = array();
        }
        /** @var eZContentObject $currentObject */
        $currentObject = $currentNode->attribute( 'object' );
        if ( $currentObject instanceof eZContentObject )
        {
            /** @var eZDateTime $date */
            $this->currentUnPublishDateAttribute = $this->getCurrentUnPublishAttribute( $currentNode );
            $date = $this->currentUnPublishDateAttribute->content();

            $attributeRetractDate = $date->attribute( 'timestamp' );
            $iniRetractDate = $currentObject->attribute( 'published' ) + $this->currentSecondsExpire;
            $objectRetractDate = $this->getRetractDate( $attributeRetractDate, $iniRetractDate, $this->currentIgnore, $this->currentOverrideValue );

            $handler = OpenPAObjectHandler::instanceFromContentObject( $currentObject );

            if ( $objectRetractDate > 0
                 && $objectRetractDate < $this->now
                 && $currentObject->attribute( 'section_id' ) != $this->currentSectionDestinationId
                 && $this->currentSectionDestinationId !== 0
                 && $handler->filter( 'change_section', 'run' ) == OpenPAObjectHandler::FILTER_CONTINUE )
            {
                //@todo refactor in service start -> $moveToTrashNodes[] = $handler->filter( 'change_section', 'move_to_trash' )
                $isClone = false;
                if ( class_exists( 'OscuraAttiHandler' ) )
                {
                    if ( OscuraAttiHandler::isPrivacyClonedObject( $currentObject ) )
                    {
                        $moveToTrashNodes[] = $currentObject->attribute( 'main_node_id' );
                        $isClone = true;
                    }
                    elseif ( $clone = OscuraAttiHandler::hasPrivacyClonedObject( $currentObject ) )
                    {
                        $moveToTrashNodes[] = $clone->attribute( 'main_node_id' );
                    }
                }
                if ( $isClone ) return false;
                //@todo refactor in service end

                if ( eZOperationHandler::operationIsAvailable( 'content_updatesection' ) )
                {
                    eZOperationHandler::execute( 'content',
                        'updatesection',
                        array(
                            'node_id' => $currentNode->attribute( 'node_id' ),
                            'selected_section_id' => $this->currentSectionDestinationId ),
                        null,
                        true
                    );
                }
                else
                {
                    eZContentOperationCollection::updateSection( $currentNode->attribute( 'node_id' ), $this->currentSectionDestinationId );
                }
                self::$changeNodeIds[$this->currentClassIdentifier][] = $currentNode->attribute( 'node_id' );                
                $handler->flush();
                return true;
            }
            $handler->flush( false );
        }
        return false;
    }

    protected function getCurrentParameters( $classIdentifier )
    {
        $this->currentClassIdentifier = $classIdentifier;
        $this->currentOverrideValue = $this->getCurrentOverrideValue();
        $this->currentIgnore = $this->getCurrentIgnore();
        $this->currentSecondsExpire = $this->getCurrentSecondExpire();
        $this->currentSectionDestinationId = $this->getCurrentSectionDestinationId();
    }


    protected function getRetractDate( $attributeTimestamp, $iniTimestamp, $ignore, $overrideValue )
    {
        if ( $attributeTimestamp > 0 )
        {
            // fine giornata
            $objectRetractDate = mktime( 23, 59, 59, date("n", $attributeTimestamp ), date( "j", $attributeTimestamp ), date( "Y", $attributeTimestamp ) );
        }
        else
        {
            $objectRetractDate = $iniTimestamp;
        }

        if ( !$ignore )
        {
            if ( $overrideValue && $overrideValue == 'maggiore' )
            {
                if ( $attributeTimestamp > $iniTimestamp )
                {
                    $objectRetractDate = $attributeTimestamp;
                }
                else
                {
                    $objectRetractDate = $iniTimestamp;
                }
            }

            if ( $overrideValue && $overrideValue == 'minore' )
            {
                if ( $attributeTimestamp < $iniTimestamp )
                {
                    $objectRetractDate = $attributeTimestamp;
                }
                else
                {
                    $objectRetractDate = $iniTimestamp;
                }
            }
        }
        elseif ( $ignore == 'attributo' )
        {
            $objectRetractDate = $iniTimestamp;
        }
        elseif ( $ignore == 'secondi' )
        {
            $objectRetractDate = $attributeTimestamp;
        }

        return $objectRetractDate;
    }

    /**
     * @return int
     * @throws Exception
     */
    protected function getCurrentSectionDestinationId()
    {
        if ( isset( $this->sectionIdList[$this->currentClassIdentifier] ) )
        {
            $toSection = $this->sectionIdList[$this->currentClassIdentifier];
        }
        else
        {
            $toSection = $this->defaultSectionId;
        }

        if ( !is_numeric( $toSection ) )
        {
            $sectionObject = eZSection::fetchByIdentifier( $toSection, false );
        }
        else
        {
            $sectionObject = eZSection::fetch( $toSection, false );
        }

        if ( is_array( $sectionObject ) && !empty( $sectionObject ) )
        {
            return $sectionObject['id'];
        }
        throw new Exception( "Section $toSection non trovata" );
    }

     /**
     * @param eZContentObjectTreeNode $currentNode
     * @return eZContentObjectAttribute
     * @throws Exception
     */
    protected function getCurrentUnPublishAttribute( eZContentObjectTreeNode $currentNode )
    {
        if ( isset( $this->dataTimeAttributeIdentifierList[$this->currentClassIdentifier] ) )
        {
            $attributeIdentifier = $this->dataTimeAttributeIdentifierList[$this->currentClassIdentifier];
            $dataMap = $currentNode->attribute( 'data_map' );
            if ( isset( $dataMap[$attributeIdentifier] ) && $dataMap[$attributeIdentifier] instanceof eZContentObjectAttribute )
            {
                return $dataMap[$attributeIdentifier];
            }
            else
            {
                throw new Exception( "Attributo {$this->currentClassIdentifier}/{$this->dataTimeAttributeIdentifierList[$this->currentClassIdentifier]} non trovato");
            }
        }
        throw new Exception( "Attributo non trovato");
    }

    /**
     * @return int
     */
    protected function getCurrentSecondExpire()
    {
        if ( isset( $this->secondsExpire[$this->currentClassIdentifier] ) )
        {
            return $this->secondsExpire[$this->currentClassIdentifier];
        }
        else
        {
            return $this->defaultSecondExpire;
        }
    }

    /**
     * @return bool|string attributo|secondi
     * @throws Exception
     */
    protected function getCurrentIgnore()
    {
        $ignore = false;
        if ( isset( $this->ignore[$this->currentClassIdentifier] ) )
        {
            if ( $this->ignore[$this->currentClassIdentifier] == 'attributo' || $this->ignore[$this->currentClassIdentifier] == 'secondi' )
            {
                $ignore = $this->ignore[$this->currentClassIdentifier];
            }
            else
            {
                throw new Exception( "Valore Ignora errato: " . $this->ignore[$this->currentClassIdentifier] . ". Valori ammessi: attributo secondi" );
            }
        }
        return $ignore;
    }

    /**
     * @return bool| string maggiore|minore
     * @throws Exception
     */
    protected function getCurrentOverrideValue()
    {
        $overrideValue = false;
        if ( isset( $this->overrideValue[$this->currentClassIdentifier] ) )
        {
            if ( $this->overrideValue[$this->currentClassIdentifier] == 'maggiore' || $this->overrideValue[$this->currentClassIdentifier] == 'minore' )
            {
                $overrideValue = $this->overrideValue[$this->currentClassIdentifier];
            }
            else
            {
                throw new Exception( "Valore UsaValore errato: " . $this->overrideValue[$this->currentClassIdentifier] . ". Valori ammessi: maggiore minore" );
            }
        }
        return $overrideValue;
    }

    /**
     * @param int $nodeId
     * @throws Exception
     */
    protected function getCurrentRootNode( $nodeId )
    {
        if ( $nodeId == 'RootNode' )
        {
            $nodeId = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );
        }
        $rootNode = eZContentObjectTreeNode::fetch( $nodeId );
        if ( !$rootNode instanceof eZContentObjectTreeNode )
        {
            throw new Exception( "RootNode {$nodeId} non trovato" );
        }
        $this->currentRootNode = $rootNode;
    }
} 