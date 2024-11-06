<?php

/** @var eZModule $module */
$module = $Params['Module'];
$objectID = $Params['ObjectID'];

$node = false;
$redirect = '/';

if ($objectID) {
    if (is_numeric($objectID)) {
        $node = eZContentObjectTreeNode::findMainNode($objectID, true);
        if ($node instanceof eZContentObjectTreeNode) {
            $redirect = $node->attribute('url_alias');
        }
    } else {
        $object = eZContentObject::fetchByRemoteID($objectID);
        if ($object instanceof eZContentObject) {
            $node = $object->attribute('main_node');
            if ($node instanceof eZContentObjectTreeNode) {
                $redirect = $node->attribute('url_alias');
            }
        }
    }
}

if ($redirect === '/') {
    header('X-Robots-Tag: noindex, nofollow, nosnippet, noarchive');
} else {
//    $module->setRedirectStatus(301);
    $canonicalUrl = $redirect;
    eZURI::transformURI($canonicalUrl, false, 'full');
    header('Link: <' . $canonicalUrl . '>; rel="canonical"');
}

$module->redirectTo($redirect);
return;