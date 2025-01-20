<?php

class ObjectHandlerServiceContentLink extends ObjectHandlerServiceBase
{
    protected $isInternal = true;

    protected $isNodeLink = true;

    protected $link;

    function run()
    {
        $this->fnData['link'] = 'getLink';
        $this->fnData['is_internal'] = 'isInternal';
        $this->fnData['target'] = 'getTarget';
        $this->fnData['full_link'] = 'getFullLink';
        $this->fnData['is_node_link'] = 'isNodeLink';
    }

    protected function getFullLink()
    {
        $link = $this->getLink();
        if ($this->isInternal) {
            eZURI::transformURI($link, false, 'full');
        }
        return $link;
    }

    protected function isInternal()
    {
        $this->getLink();
        return $this->isInternal;
    }

    protected function isNodeLink()
    {
        $this->getLink();
        return $this->isNodeLink;
    }

    private function isAreaTematica(): bool
    {
        return (
            $this->container->currentNodeId != $this->container->currentMainNodeId
            && in_array(
                $this->container->getContentNode()->attribute('class_identifier'),
                OpenPAINI::variable('AreeTematiche', 'IdentificatoreAreaTematica', ['area_tematica'])
            ));
    }

    private function hasUrlLocationAttribute(): bool
    {
        return (isset($this->container->attributesHandlers['location'])
            && $this->container->attributesHandlers['location']
                ->attribute('contentobject_attribute')
                ->attribute('data_type_string') == eZURLType::DATA_TYPE_STRING
            && $this->container->attributesHandlers['location']
                ->attribute('contentobject_attribute')->attribute('has_content'));
    }

    private function hasRelatedLocationsAttribute(): bool
    {
        return isset($this->container->attributesHandlers['internal_location'])
            && $this->container->attributesHandlers['internal_location']
                ->attribute('contentobject_attribute')
                ->attribute('data_type_string') == eZObjectRelationListType::DATA_TYPE_STRING
            && $this->container->attributesHandlers['internal_location']
                ->attribute('contentobject_attribute')->attribute('has_content');
    }

    private function hasRelatedLocationAttribute(): bool
    {
        return isset($this->container->attributesHandlers['internal_location'])
            && $this->container->attributesHandlers['internal_location']
                ->attribute('contentobject_attribute')
                ->attribute('data_type_string') == eZObjectRelationType::DATA_TYPE_STRING
            && $this->container->attributesHandlers['internal_location']
                ->attribute('contentobject_attribute')->attribute('has_content');
    }

    private function hasOpeningMode(): bool
    {
        $hasOpeningMode = $this->container->getContentNode()->attribute('class_identifier') === 'document'
            && isset($this->container->attributesHandlers['opening_mode'])
            && $this->container->attributesHandlers['opening_mode']
                ->attribute('contentobject_attribute')
                ->attribute('data_type_string') == eZSelectionType::DATA_TYPE_STRING
            && $this->container->attributesHandlers['opening_mode']
                ->attribute('contentobject_attribute')->attribute('has_content');

        if (!$hasOpeningMode){
            return false;
        }

        $openingMode = $this->getLinkOpeningMode();

        switch ($openingMode){
            case 0:
                return false;
            case 1:
                return isset($this->container->attributesHandlers['file'])
                    && $this->container->attributesHandlers['file']
                        ->attribute('contentobject_attribute')->attribute('has_content');
            case 2:
                return isset($this->container->attributesHandlers['link'])
                    && $this->container->attributesHandlers['link']
                        ->attribute('contentobject_attribute')->attribute('has_content');
        }

        return false;
    }

    private function getLinkOpeningMode(): int
    {
        return (int)$this->container->attributesHandlers['opening_mode']
            ->attribute('contentobject_attribute')->attribute('content')[0];
    }

    private function getLinkByOpeningMode()
    {
        $openingMode = $this->getLinkOpeningMode();
        switch ($openingMode) {
            case 1:
                $attribute = $this->container->attributesHandlers['file']
                    ->attribute('contentobject_attribute');
                $file = $attribute->attribute('content');
                $link = 'content/download/' . $attribute->attribute('contentobject_id')
                    . '/' . $attribute->attribute('id')
                    . '/file'
                    . '/' . urlencode($file->attribute('original_filename'));
                break;
            case 2:
                $link = $this->container->attributesHandlers['link']
                        ->attribute('contentobject_attribute')->attribute('content');
                break;
            default:
                $link = $this->container->getContentNode()->attribute('url_alias');
        }

        return $link;
    }

    protected function getLink()
    {
        if ($this->link === null) {
            $link = false;
            if ($this->container->getContentNode() instanceof eZContentObjectTreeNode) {
                $link = $this->container->getContentNode()->attribute('url_alias');

                // area tematica
                if ($this->isAreaTematica()) {
                    $link = $this->container->getContentObject()->attribute('main_node')->attribute('url_alias');
                }

                // url as location
                if ($this->hasUrlLocationAttribute()) {
                    $link = $this->container->attributesHandlers['location']
                        ->attribute('contentobject_attribute')->attribute('content');
                    $this->isInternal = false;
                    $this->isNodeLink = false;
                }

                // many relations as internal_location
                if ($this->hasRelatedLocationsAttribute()) {
                    $content = $this->container->attributesHandlers['internal_location']
                        ->attribute('contentobject_attribute')
                        ->attribute('content');
                    foreach ($content['relation_list'] as $relation) {
                        $object = eZContentObject::fetch($relation['contentobject_id']);
                        if ($object instanceof eZContentObject) {
                            $node = $object->attribute('main_node');
                            if ($node instanceof eZContentObjectTreeNode) {
                                $link = $node->attribute('url_alias');
                                $this->isInternal = true;
                                $this->isNodeLink = false;
                                break;
                            }
                        }
                    }
                }

                // one relation as internal_location
                if ($this->hasRelatedLocationAttribute()) {
                    $content = $this->container->attributesHandlers['internal_location']
                        ->attribute('contentobject_attribute')
                        ->attribute('content');
                    if ($content instanceof eZContentObject
                        && $content->attribute('main_node') instanceof eZContentObjectTreeNode) {
                        $link = $content->attribute('main_node')->attribute('url_alias');
                        $this->isInternal = true;
                        $this->isNodeLink = false;
                    }
                }

                // document opening mode
                if ($this->hasOpeningMode()) {
                    $link = $this->getLinkByOpeningMode();
                    $this->isInternal = $this->getLinkOpeningMode() === 1;
                    $this->isNodeLink = false;
                }

            }

            $this->link = $link;
        }

        return $this->link;
    }

    protected function getTarget()
    {
        $target = false;
        if (isset($this->container->attributesHandlers['open_in_new_window'])
            && $this->container->attributesHandlers['open_in_new_window']->attribute(
                'contentobject_attribute'
            )->attribute('data_int') == 1) {
            $target = '_blank';
        }
        return $target;
    }
}
