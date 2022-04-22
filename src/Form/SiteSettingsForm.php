<?php
namespace Transcript\Form;

use Laminas\Form\Fieldset;
use Omeka\Form\Element\ColorPicker;

class SiteSettingsForm extends Fieldset
{
    public const SETTING_COLOR = 'vimeo_color';
    
    public const SETTINGS_ALL = [
        self::SETTING_COLOR => '',
    ];
    
    protected $label = 'Vimeo';
    
    public function init()
    {
        $this->add([
            'name' => self::SETTING_COLOR,
            'type' => ColorPicker::class,
            'options' => [
                'label' => 'Player accent color', // @translate
            ],
        ]);
    }
}

?>