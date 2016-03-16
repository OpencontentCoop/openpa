<?php

class OpenPAStateTools
{
    /**
     * array
     */
    protected $rules = array();


    /**
     * bool
     */
    protected $log;

    /**
     * @var eZCLI
     */
    protected $cli;

    /**
     * @var eZContentObject
     */
    protected $currentObject;

    public function __construct()
    {
        $rules = OpenPAINI::variable( 'ChangeState', 'Rules', array() );

        foreach( $rules as $rule )
        {
            @list( $classIdentifier, $ruleIdentifiers ) = explode( '|', $rule );
            $ruleIdentifiers = explode( ',', $ruleIdentifiers );
            foreach( $ruleIdentifiers as $ruleIdentifier )
            {
                $ruleSettings = OpenPAINI::group( 'ChangeStateRule-' . $ruleIdentifier );
                if ( is_array( $ruleSettings ) )
                {
                    if ( $this->validateRule( $ruleSettings, $classIdentifier ) )
                    {
                        if ( !isset( $this->rules[$classIdentifier] ) )
                        {
                            $this->rules[$classIdentifier] = array();
                        }
                        $this->rules[$classIdentifier][$ruleIdentifier] = $ruleSettings;
                    }
                }
            }
        }

        $this->log = false;
        $this->cli = eZCLI::instance();
    }

    public function changeState( $currentObject )
    {
        if ( is_numeric( $currentObject ) )
        {
            $currentObject = eZContentObject::fetch( $currentObject );
        }
        if ( $currentObject instanceof eZContentObject )
        {
            $this->currentObject = $currentObject;
            $this->changeCurrentObjectState();
        }
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function getState( $identifier )
    {
        @list( $groupIdentifier, $stateIdentifier ) = explode( '.', $identifier );

        $stateObject = null;
        $stateGroup = eZContentObjectStateGroup::fetchByIdentifier( $groupIdentifier );
        if ( $stateGroup instanceof eZContentObjectStateGroup )
        {
            $stateObject = $stateGroup->stateByIdentifier( $stateIdentifier );
        }

        if ( !$stateObject instanceof eZContentObjectState ){
            throw new Exception( "State $identifier not found" );
        }

        return $stateObject;
    }

    public function setLog( $bool )
    {
        $this->log = $bool;
    }

    protected function changeCurrentObjectState()
    {
        if ( $this->log ) $this->cli->notice( "Oggetto {$this->currentObject->attribute( 'id' )} - {$this->currentObject->attribute( 'name' )}" );
        if ( isset( $this->rules[$this->currentObject->attribute( 'class_identifier' )] ) )
        {
            foreach( $this->rules[$this->currentObject->attribute( 'class_identifier' )] as $ruleIdentifier => $ruleSettings )
            {
                $this->runCurrentChangeObjectStateRule( $ruleIdentifier, $ruleSettings );
            }
        }
    }

    protected function runCurrentChangeObjectStateRule( $ruleIdentifier, $ruleSettings )
    {
        // stato corrente
        $currentStateIdentifier = $ruleSettings['CurrentState'];
        try {
            $currentState = $this->getState($currentStateIdentifier);
        } catch (Exception $e) {
            if ( $this->log ) $this->cli->error( "[$ruleIdentifier] " . $e->getMessage() );
            return false;
        }
        if ( !in_array( $currentState->attribute( 'id' ), $this->currentObject->attribute( 'state_id_array' ) ) )
        {
            if ( $this->log ) $this->cli->warning( "[$ruleIdentifier] L'oggetto non è in stato corrente corretto" );
            return false;
        }

        // stato futuro
        $destinationStateIdentifier = $ruleSettings['DestinationState'];
        try {
            $destinationState = $this->getState($destinationStateIdentifier);
        } catch (Exception $e) {
            if ( $this->log ) $this->cli->error( "[$ruleIdentifier] " . $e->getMessage() );
            return false;
        }
        if ( in_array( $destinationState->attribute( 'id' ), $this->currentObject->attribute( 'state_id_array' ) ) )
        {
            if ( $this->log ) $this->cli->warning( "[$ruleIdentifier] L'oggetto è già in stato destinatione corretto" );
            return false;
        }

        // condizioni
        $mustChange = true;
        foreach( $ruleSettings['Conditions'] as $condition ){
            if ( !$this->verifyConditionForCurrentObject( $condition ) ){
                if ( $this->log ) $this->cli->warning( "[$ruleIdentifier] Condizione $condition non superata" );
                $mustChange = false;
            }
        }
        if( $mustChange )
        {
            $this->currentObject->assignState( $destinationState );
            $this->flushCurrentObject();
            if ( $this->log ) $this->cli->notice( "[$ruleIdentifier] Aggiornamento stato" );
            return true;
        }
        return false;
    }

    protected function flushCurrentObject()
    {
        eZContentObject::clearCache( array( $this->currentObject->attribute( 'id' ) ) );
        $this->currentObject = eZContentObject::fetch( $this->currentObject->attribute( 'id' ) );
        eZContentOperationCollection::registerSearchObject( $this->currentObject->attribute( 'id' ) );
        eZContentCacheManager::clearContentCacheIfNeeded( $this->currentObject->attribute( 'id' ) );
    }

    protected function validateRule( $ruleSettings, $classIdentifier )
    {
        $isValid = false;

        $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
        if ( !$classId )
        {
            return false;
        }

        if ( !empty( (array)$ruleSettings['Conditions'] ) )
        {
            $isValid = true;
            foreach( $ruleSettings['Conditions'] as $conditionSetting )
            {
                $condition = $this->parseCondition( $conditionSetting );
                if ( !$condition )
                {
                    $isValid = false;
                }
            }
        }

        return $isValid;
    }

    protected function verifyConditionForCurrentObject( $conditionSetting )
    {
        $parameters = $this->parseCondition( $conditionSetting );
        $attribute = $parameters['attribute'];
        $operator = $parameters['operator'];
        $value = trim( $parameters['value'] );

        if ( !is_string( $value ) || empty( $value ) )
        {
            return false;
        }

        /** @var eZContentObjectAttribute[] $dataMap */
        $dataMap = $this->currentObject->attribute( 'data_map' );
        if ( isset( $dataMap[$attribute] ) && $dataMap[$attribute]->toString() !== '' ){
            return $this->compare( $dataMap[$attribute]->toString(), $operator, $value );
        }

        return false;
    }

    protected function compare( $string, $operator, $value ){

        $now = time();

        if ( $value == 'NOW' ) {
            $value = $now;
        }
        elseif ( $value == 'TODAY' ){
            $value = mktime( 23, 59, 59, date("n", $now ), date( "j", $now ), date( "Y", $now ) );
        }

        switch( $operator ){
            case 'eq':
                return $string == $value;
                break;

            case 'gt':
                return $string > $value;
                break;

            case 'ge':
                return $string >= $value;
                break;

            case 'lt':
                return $string < $value;
                break;

            case 'le':
                return $string <= $value;
                break;
        }

        return false;
    }

    protected function parseCondition( $conditionSetting )
    {
        list( $attributeIdentifier, $operator, $string ) = explode( ';', $conditionSetting );
        if ( !isset( $attributeIdentifier, $operator, $string  ) )
        {
            eZDebug::writeError( "Parametri non sufficienti in $conditionSetting", __METHOD__ );
            return false;
        }

        if ( !in_array( $operator, array( 'eq', 'gt', 'ge', 'lt', 'le' ) ) ){
            eZDebug::writeError( "Operatore $operator non riconosciuto", __METHOD__ );
            return false;
        }

        return array(
            'attribute' => $attributeIdentifier,
            'operator' => $operator,
            'value' => $string
        );
    }
}