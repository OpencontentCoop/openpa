<?php

class OpenPAChildrenViewContent
{
    private $options = array();

    private $extraConfigs = array();

    public static function instance(eZContentObjectAttribute $attribute)
    {
        $instance = new self();
        $storedValue = $attribute->attribute('data_text');

        $data = @unserialize($storedValue);
        if ($storedValue === 'b:0;' || $data !== false) {
            $instance->setSelectedOptions($data['options']);
            $instance->setExtraConfigs($data['extraConfigs']);
        } elseif (is_string($storedValue)) {
            $instance->setSelectedOptions(explode('-', $storedValue));
        }

        return $instance;
    }

    public function attributes()
    {
        return array(
            'options',
            'extra_configs'
        );
    }

    public function hasAttribute($name)
    {
        return in_array($name, $this->attributes());
    }

    public function attribute($name)
    {
        if ($name == 'options'){
            return $this->getOptions();
        }else{
            return $this->getExtraConfigs();
        }
    }

    /**
     * @param array $options
     */
    public function setSelectedOptions($options)
    {
        if (is_array($options)) {
            $this->options = $options;
        }
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getExtraConfigs()
    {
        return $this->extraConfigs;
    }

    /**
     * @param array $extraConfigs
     */
    public function setExtraConfigs($extraConfigs)
    {
        $this->extraConfigs = $extraConfigs;
    }

    public function __toString()
    {
        $data = array(
            'options' => $this->options,
            'extraConfigs' => $this->extraConfigs
        );

        return serialize($data);
    }

}
