<?php
namespace Transcript\Form;

use Laminas\Form\Fieldset;
use Omeka\Form\Element\ColorPicker;

class SiteSettingsFieldset extends Fieldset
{
    public const SETTING_COLOR = 'vimeo_color';
    
    public const SETTINGS_ALL = [
        self::SETTING_COLOR => '',
    ];
    
    protected $elementGroups = [
        'transcript' => 'Transcript'
    ];
    
    public function init()
    {
        $this
            ->setOption('element_groups', $this->elementGroups)
            ->add([
                'name' => self::SETTING_COLOR,
                'type' => ColorPicker::class,
                'options' => [
                    'element_group' => 'transcript',
                    'label' => 'Player accent color' // @translate
                ]
            ]);
    }
}

?>