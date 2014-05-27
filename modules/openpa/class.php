<?php

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
        return $module->redirectTo( '/openpa/class/' . $id );
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
    }
    
    $tools->compare();
    $result = $tools->getData();                
    $tpl->setVariable( 'locale', $locale );    
    $tpl->setVariable( 'id', $id );    
    $missingLocale = array();
    foreach( $tools->getData()->missingAttributes as $item )
    {
        $missingLocale[] = new Item( $item );
    }
    $tpl->setVariable( 'missing_in_locale', $missingLocale );
    
    $missingRemote = array();
    foreach( $tools->getData()->extraAttributes as $item )
    {
        $obj = new Item( $item );
        $missingRemote[] = $obj;        
    }
    $tpl->setVariable( 'missing_in_remote', $missingRemote );
    $tpl->setVariable( 'missing_in_remote_details', $tools->getData()->extraDetails );
    
    if ( $tools->getData()->hasError || $tools->getData()->hasWarning )
    {
        $tpl->setVariable( 'diff', $tools->getData()->diffAttributes );
        $tpl->setVariable( 'warnings', $tools->getData()->warnings );
        $tpl->setVariable( 'errors', $tools->getData()->errors );            
    }
    else
    {
        $tpl->setVariable( 'diff', array() );   
        $tpl->setVariable( 'errors', array() );           
        $tpl->setVariable( 'warnings', array() );  
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


class Item
{    
    protected $item;
    public $attributes;
    function __construct( $item )
    {        
        $this->item = $item;
        foreach( $this->item as $property => $value )
        {
            $this->attributes[$property] = $this->item->{$property};
        }
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
            if ( is_string( $this->attributes[$name] ) )
            {
                return $this->attributes[$name];  
            }
        }
        return false;
        
    }
    
}
