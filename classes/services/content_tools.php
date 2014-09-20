<?php

class ObjectHandlerServiceContentTools extends ObjectHandlerServiceBase
{
    function run()
    {
        $canUser = eZFunctionHandler::execute(
            'user',
            'has_access_to',
            array( 'module' => 'openpa', 'function' => 'editor_tools' )
        );
        $this->data['editor_tools'] = $canUser;
        $this->data['preferences'] = eZPreferences::values();
    }
}