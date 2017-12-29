<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

$recaptcha = new OpenPARecaptcha();
if ($http->hasPostVariable('GoogleRecaptchaPublic')) {
    $public = trim($http->postVariable('GoogleRecaptchaPublic'));
    $private = trim($http->postVariable('GoogleRecaptchaPrivate'));
    $recaptcha->store($public, $private);
    $tpl->setVariable('message', 'Impostazioni salvate correttamente');
}

$tpl->setVariable('public', $recaptcha->getPublicKey());
$tpl->setVariable('private', $recaptcha->getPrivateKey());

$Result = array();
$Result['content'] = $tpl->fetch('design:openpa/recaptcha.tpl');
$Result['path'] = array(array('text' => 'Impostazioni recaptcha', 'url' => false));
