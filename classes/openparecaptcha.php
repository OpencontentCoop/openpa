<?php

class OpenPARecaptcha
{
    public function validate()
    {
        if (!class_exists('OcReCaptchaType')){
            eZDebug::writeError("Missing required extension OcReCaptcha");
            return false;
        }

        $gRecaptchaResponse = eZHTTPTool::instance()->postVariable( 'g-recaptcha-response' );
        return OcReCaptchaType::validateCaptcha($gRecaptchaResponse, $this->getPrivateKey());
    }

    public function getPublicKey()
    {
        list($public, $private) = explode('$$$', $this->getSiteData()->attribute('value'));

        return $public;
    }

    public function getPrivateKey()
    {
        list($public, $private) = explode('$$$', $this->getSiteData()->attribute('value'));

        return $private;
    }

    public function store($public, $private)
    {
        $data = $this->getSiteData();
        $data->setAttribute('value', $public . '$$$' . $private);
        $data->store();
    }

    private function getSiteData()
    {
        $data = eZSiteData::fetchByName('GoogleRecaptcha');
        if (!$data instanceof eZSiteData) {
            $data = new eZSiteData(array(
                'name' => 'GoogleRecaptcha',
                'value' => 'no-public$$$no-secret'
            ));
        }

        return $data;
    }

}
