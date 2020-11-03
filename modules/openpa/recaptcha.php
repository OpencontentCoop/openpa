<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

$recaptcha = new OpenPARecaptcha();
$recaptcha3 = new OpenPARecaptcha(3);

if ($http->hasPostVariable('GoogleRecaptchaPublic')) {
    $public = trim($http->postVariable('GoogleRecaptchaPublic'));
    $private = trim($http->postVariable('GoogleRecaptchaPrivate'));
    $recaptcha->store($public, $private);
    $tpl->setVariable('message', 'Impostazioni salvate correttamente');
}

if ($http->hasPostVariable('GoogleRecaptchaV3Public')) {
    $public = trim($http->postVariable('GoogleRecaptchaV3Public'));
    $private = trim($http->postVariable('GoogleRecaptchaV3Private'));
    $recaptcha3->store($public, $private);
    $tpl->setVariable('message', 'Impostazioni salvate correttamente');
}

$tpl->setVariable('public', $recaptcha->getPublicKey());
$tpl->setVariable('private', $recaptcha->getPrivateKey());
$tpl->setVariable('publicV3', $recaptcha3->getPublicKey());
$tpl->setVariable('privateV3', $recaptcha3->getPrivateKey());

$Result = array();
$Result['content'] = $tpl->fetch('design:openpa/recaptcha.tpl');
$Result['path'] = array(array('text' => 'Impostazioni recaptcha', 'url' => false));
