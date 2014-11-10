<?php

class ObjectHandlerServiceContentVirtual extends ObjectHandlerServiceBase
{
    const SORT_FIELD_PRIORITY = 'extra_priority_si';

    function run()
    {
        $this->fnData['folder'] = 'isVirtualFolder';
        $this->fnData['calendar'] = 'isVirtualCalendar';
    }

    protected function isVirtualFolder()
    {
        $data = false;
        if ( isset( $this->container->attributesHandlers['classi_filtro'] )
             && $this->container->attributesHandlers['classi_filtro']->attribute( 'contentobject_attribute' )->attribute( 'has_content' ) )
        {
            $classes = $this->classFilter( $this->container->attributesHandlers['classi_filtro']->attribute( 'contentobject_attribute' ) );
            $subtree = $this->subTree( $this->container->attributesHandlers['subfolders']->attribute( 'contentobject_attribute' ) );
            $sort = $this->sortBy();
            if ( $classes )
            {
                $data = array( 'classes' => $classes, 'subtree' => $subtree, 'sort' => $sort );
            }
        }
        return $data;
    }

    protected function sortBy()
    {
        $data = array();
        if ( $this->container->getContentNode() instanceof eZContentObjectTreeNode )
        {
            $sortFieldID = $this->container->getContentNode()->attribute( 'sort_field' );
            $sortOrder = $this->container->getContentNode()->attribute( 'sort_order' );

            switch ( $sortFieldID )
            {
                default:
                    $sortField = 'score';
                    eZDebug::writeWarning( 'Unknown sort field ID: ' . $sortFieldID, __METHOD__ );
                    break;

                case eZContentObjectTreeNode::SORT_FIELD_PATH:
                    $sortField = 'path';
                    break;

                case eZContentObjectTreeNode::SORT_FIELD_PUBLISHED:
                    $sortField = 'published';
                    break;

                case eZContentObjectTreeNode::SORT_FIELD_MODIFIED:
                    $sortField = 'modified';
                    break;

                case eZContentObjectTreeNode::SORT_FIELD_SECTION:
                    $sortField = 'section_id';
                    break;

                case eZContentObjectTreeNode::SORT_FIELD_DEPTH:
                    $sortField = eZSolr::getMetaFieldName( 'depth', 'sort' );
                    break;

                case eZContentObjectTreeNode::SORT_FIELD_CLASS_IDENTIFIER:
                    $sortField = 'class_identifier';
                    break;

                case eZContentObjectTreeNode::SORT_FIELD_CLASS_NAME:
                    $sortField = 'class_name';
                    break;

                case eZContentObjectTreeNode::SORT_FIELD_PRIORITY:
                    $sortField = self::SORT_FIELD_PRIORITY;
                    break;

                case eZContentObjectTreeNode::SORT_FIELD_NAME:
                    $sortField = 'name';
                    break;

                case eZContentObjectTreeNode::SORT_FIELD_MODIFIED_SUBNODE:
                    $sortField = 'modified';
                    break;

                case eZContentObjectTreeNode::SORT_FIELD_NODE_ID:
                    $sortField = eZSolr::getMetaFieldName( 'node_id', 'sort' );;
                    break;

                case eZContentObjectTreeNode::SORT_FIELD_CONTENTOBJECT_ID:
                    $sortField = eZSolr::getMetaFieldName( 'contentobject_id', 'sort' );;
                    break;
            }
            $data = array( $sortField => $sortOrder == eZContentObjectTreeNode::SORT_ORDER_ASC ? 'asc' : 'desc' );
        }
        return $data;
    }

    protected function subTree( eZContentObjectAttribute $attribute )
    {
        $subtree = array();
        if ( $attribute instanceof eZContentObjectAttribute )
        {
            $relations = $attribute->content();
            foreach( $relations['relation_list'] as $relation )
            {
                $subtree[] = $relation['node_id'];
            }
            if ( empty( $subtree ) )
            {
                $subtree = array( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );
            }
        }
        return $subtree;
    }

    protected function classFilter( eZContentObjectAttribute $attribute )
    {
        $classes = false;
        if ( $attribute instanceof eZContentObjectAttribute )
        {
            $string = $attribute->toString();
            $array = explode( ',', $string );
            $classes = array_map( 'trim', $array ); //@todo check if exists?
        }
        return $classes;
    }

    protected function isVirtualCalendar()
    {
        $data = false;
        if ( isset( $this->container->attributesHandlers['subtree_array'] )
             && $this->container->attributesHandlers['subtree_array']->attribute( 'has_content' ) )
        {
            $subtree = $this->subTree( $this->container->attributesHandlers['subtree_array']->attribute( 'contentobject_attribute' ) );
            if ( count( $subtree ) > 0 )
            {
                $data = array( 'subtree' => $subtree );
            }
        }
        return $data;
    }
}