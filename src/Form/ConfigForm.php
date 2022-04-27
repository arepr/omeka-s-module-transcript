<?php
namespace Transcript\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;

class ConfigForm extends Form
{
    public const SETTING_CLIENT_ID = 'vimeo_client_id';
    public const SETTING_CLIENT_SECRET = 'vimeo_client_secret';
    public const SETTING_ACCESS_TOKEN = 'vimeo_access_token';
    
    public const SETTINGS_ALL = [
        self::SETTING_CLIENT_ID => '',
        self::SETTING_CLIENT_SECRET => '',
        self::SETTING_ACCESS_TOKEN => '',
    ];
    
    protected $label = 'Vimeo';
    
    public function init()
    {
        $this->add([
            'name' => self::SETTING_CLIENT_ID,
            'type' => Element\Text::class,
            'options' => [
                'label' => 'API client identifier', // @translate
            ],
            'attributes' => [
                'id' => self::SETTING_CLIENT_ID,
                'required' => true,
            ],
        ]);
        
        $this->getInputFilter()->add([
            'name' => self::SETTING_CLIENT_ID,
            'required' => true,
        ]);
        
        $this->add([
            'name' => self::SETTING_CLIENT_SECRET,
            'type' => Element\Text::class,
            'options' => [
                'label' => 'API client secret', // @translate
            ],
            'attributes' => [
                'id' => self::SETTING_CLIENT_SECRET,
                'required' => true,
            ],
        ]);
        
        $this->getInputFilter()->add([
            'name' => self::SETTING_CLIENT_SECRET,
            'required' => true,
        ]);
        
        $this->add([
            'name' => self::SETTING_ACCESS_TOKEN,
            'type' => Element\Text::class,
            'options' => [
                'label' => 'API access token', // @translate
                'info' => 'Generate API keys through developer.vimeo.com. Your token must include the video files permission.', // @translate
            ],
            'attributes' => [
                'id' => self::SETTING_ACCESS_TOKEN,
                'required' => true,
            ],
        ]);
        
        $this->getInputFilter()->add([
            'name' => self::SETTING_ACCESS_TOKEN,
            'required' => true,
        ]);
    }
}

?>