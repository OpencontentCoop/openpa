<?php

class OpenPARecaptcha
{
    private $version = 2;

    public function __construct($version = 2)
    {
        $this->version = $version;
    }

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
        if ($this->version === 2 && class_exists('OcReCaptchaType')){
            $classAttributes = eZContentClassAttribute::fetchFilteredList(array('data_type_string' => OcReCaptchaType::DATA_TYPE_STRING));
            foreach ($classAttributes as $classAttribute) {
                $classAttribute->setAttribute(OcReCaptchaType::PUBLIC_KEY_FIELD, $public);
                $classAttribute->setAttribute(OcReCaptchaType::PRIVATE_KEY_FIELD, $private);
                $classAttribute->store();
            }
        }
    }

    private function getSiteData()
    {
        if ($this->version === 2){
            $siteDataName = 'GoogleRecaptcha';
        }else{
            $siteDataName = 'GoogleRecaptcha' . $this->version;
        }
        $data = eZSiteData::fetchByName($siteDataName);
        if (!$data instanceof eZSiteData) {
            $data = new eZSiteData(array(
                'name' => $siteDataName,
                'value' => 'no-public$$$no-secret'
            ));
        }

        return $data;
    }

}
