<?php

/** @var eZModule $module */
$module = $Params['Module'];
$id = $Params['ID'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$format = 'ez';
$action = false;
if ( isset( $_GET['format'] ) )
{
    $format = $_GET['format'];
}

try
{
    if ( $module->isCurrentAction( 'Install' ) )
    {        
        $tools = new OpenPAClassTools( $id, true );
        $tools->sync();
        $module->redirectTo( '/openpa/class/' . $id );
        return;
    }
    
    $tools = new OpenPAClassTools( $id );
    
    $remote = $tools->getRemote();
    $locale = $tools->getLocale();    
    if ( $remote === null )
    {
        throw new Exception( 'Impossibile trovare la classe remota' );
    }

    if ( $module->isCurrentAction( 'Sync' ) )
    {
        $force = false;
        $removeExtra = false;
        if ( $http->hasPostVariable( 'ForceSync' ) )
        {
            $force = $http->postVariable( 'ForceSync' ) == 1;
        }
        if ( $http->hasPostVariable( 'RemoveExtra' ) )
        {
            $removeExtra = $http->postVariable( 'RemoveExtra' ) == 1;
        }
        $tools->sync( $force, $removeExtra );
        $module->redirectTo( '/openpa/class/' . $id );
        return;
    }

    $tools->compare();
    $result = $tools->getData();
        
    if ( $module->isCurrentAction( 'SyncProperty' ) )
    {
        $tools->syncSingleProperty( $http->variable('SyncPropertyIdentifier') );
        $module->redirectTo( '/openpa/class/' . $id );
        return;
    }
    
    if ( $module->isCurrentAction( 'SyncAttribute' ) )
    {
        $tools->syncSingleAttribute( $http->variable('SyncAttributeIdentifier') );
        $module->redirectTo( '/openpa/class/' . $id );
        return;
    }
    
    if ( $module->isCurrentAction( 'RemoveAttribute' ) )
    {
        $tools->removeSingleAttribute( $http->variable('RemoveAttributeIdentifier') );
        $module->redirectTo( '/openpa/class/' . $id );
        return;
    }
    
    if ( $module->isCurrentAction( 'AddAttribute' ) )
    {
        $tools->addSingleAttribute( $http->variable('AddAttributeIdentifier') );
        $module->redirectTo( '/openpa/class/' . $id );
        return;
    }

    $tpl->setVariable( 'locale', $locale );
    $tpl->setVariable( 'remote', new OpenPAClassDataItem($remote) );
    $tpl->setVariable( 'id', $id );    
    $missingLocale = array();
    foreach( $tools->getData()->missingAttributes as $item )
    {
        $missingLocale[] = new OpenPAClassDataItem( $item );
    }
    $tpl->setVariable( 'missing_in_locale', $missingLocale );
    
    $missingRemote = array();
    foreach( $tools->getData()->extraAttributes as $item )
    {
        $obj = new OpenPAClassDataItem( $item );
        $missingRemote[] = $obj;        
    }
    $tpl->setVariable( 'missing_in_remote', $missingRemote );
    $tpl->setVariable( 'missing_in_remote_details', $tools->getData()->extraDetails );
    
    if ( $tools->getData()->hasError || $tools->getData()->hasWarning || $tools->getData()->hasNotice )
    {
        $tpl->setVariable( 'diff', $tools->getData()->diffAttributes );
        $tpl->setVariable( 'warnings', $tools->getData()->warnings );
        $tpl->setVariable( 'errors', $tools->getData()->errors );
        $tpl->setVariable( 'notices', $tools->getData()->notices );
    }
    else
    {
        $tpl->setVariable( 'diff', array() );   
        $tpl->setVariable( 'errors', array() );           
        $tpl->setVariable( 'warnings', array() );
        $tpl->setVariable( 'notices', array() );
    }
    
    $tpl->setVariable( 'diff_properties', $tools->getData()->diffProperties );
    
}
catch( Exception $e )
{
   eZDebug::writeError( $e->getMessage(), __FILE__ );
   $result = array( 'error' => $e->getMessage() ); 
}

if ( $format == 'json' ) 
{
    header('Content-Type: application/json');
    echo json_encode( $result );    
    eZExecution::cleanExit();
}
else
{
    $tpl->setVariable( 'request_id', $id );
    $tpl->setVariable( 'locale_not_found', empty( $id ) ? false : true );    
    if ( eZContentClass::fetchByIdentifier( $id ) || eZContentClass::fetch( intval( $id ) ) )
    {
        $tpl->setVariable( 'locale_not_found', false );
    }
    $tpl->setVariable( 'data', $result );
    $Result = array();
    $Result['content'] = $tpl->fetch( 'design:openpa/class.tpl' );
    $Result['path'] = array( array( 'text' => 'OpenPA Classi' ,
                                    'url' => false ) );
    
}


class OpenPAClassDataItem
{        
    public $attributes;
    
    public function __construct( $item )
    {        
        $this->fieldsMap = $fieldsMap;
        
        foreach( $item as $property => $value )
        {                        
            if ( $property == 'DataMap' )
            {
                $this->attributes['data_map'] = $this->parseValue($item->DataMap[0]);
            }
            else
            {
                $this->attributes[$property] = $this->parseValue($item->{$property});
            }
        }
    }
        
    private function parseValue( $value )
    {
        if ( is_array( $value ) )
        {
            $data = array();
            foreach( $value as $item )
            {
                $data[] = $this->parseValue( $item );
            }            
        }
        elseif ( is_object( $value ) )
        {
            $data = new OpenPAClassDataItem( $value );
        }
        else
        {
            $data = $value;
        }
        return $data;
    }
    
    public function attributes()
    {        
        return array_keys( $this->attributes );
    }
    
    public function hasAttribute( $name )
    {
        return isset( $this->attributes[$name] );
    }
    
    public function attribute( $name )
    {
        if ( isset( $this->attributes[$name] ) )
        {            
            return $this->attributes[$name]; 
        }
        return false;
        
    }
    
}
