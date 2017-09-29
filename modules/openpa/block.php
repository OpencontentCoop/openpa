<?php
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$blockID = $Params['BlockID'];
$view = $Params['View'];
$tpl = eZTemplate::factory();

if ( $http->hasPostVariable( 'BrowseActionName' )
     && $http->postVariable( 'BrowseActionName' ) == 'SelectEzPage' )
{
    $selectBlocks = array();
    $selectedNodeIDArray = $http->postVariable( 'SelectedNodeIDArray' );
    $node = eZContentObjectTreeNode::fetch( $selectedNodeIDArray[0] );
    if ( $node instanceof eZContentObjectTreeNode )
    {
        $dataMap = $node->attribute('data_map');
        foreach( $dataMap as $attribute ){
            if ($attribute->attribute('data_type_string') == 'ezpage'){
                $content = $attribute->attribute('content');
                foreach( $content->attribute('zones') as $zone ){
                    foreach( $zone->attribute('blocks') as $block){
                        $selectBlocks[] = $block;
                    }
                }
            }
        }
        
        $tpl->setVariable( 'select_blocks', $selectBlocks );    
    }    

}elseif ($blockID){
    
    $block = eZPageBlock::fetch( $blockID );
    $blocks = array();
    $ini = eZINI::instance('block.ini');    
    
    foreach($ini->variable('General', 'AllowedTypes') as $type){
        if ($ini->hasGroup($type) && $type == $block->attribute('type')){
            $blocks[$type] = $ini->group($type);
        }
    }
    
    if ($view){
        $block->setAttribute('view', $view);
    }
    
    $tpl->setVariable( 'block', $block );
    $tpl->setVariable( 'blocks', $blocks );
    
}else{
    
    $classes = array();
    $classIds = eZContentClass::fetchIDListContainingDatatype( 'ezpage' );
    if ( count( $classIds ) > 0 )
    {
        foreach( $classIds as $id )
        {
            $class = eZContentClass::fetch( $id );
            $classes[] = $class->attribute( 'identifier' );
        }        
    }
    
    eZContentBrowse::browse(
        array(
            'action_name' => 'SelectEzPage',
            'from_page' => '/openpa/block/',
            'class_array' => $classes,
            'start_node' => eZINI::instance('content.ini')->variable( 'NodeSettings', 'RootNode' ),
            'cancel_page' => '/'
        ),
        $module
    );
}




$Result = array();
$Result['content'] = $tpl->fetch( 'design:openpa/block.tpl' );
$Result['path'] = array( array( 'text' => 'Demo blocchi' , 'url' => false ) );
