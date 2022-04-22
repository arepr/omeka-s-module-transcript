<?php
namespace Transcript\Form;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Omeka\Form\Element\LocaleSelect;

class WebVTTIngesterForm extends Fieldset
{
    public function __construct($options = [])
    {
        parent::__construct(null, $options);
    }
    
    public function init()
    {
        $this->add([
            'name' => 'file[__index__][media]',
            'type' => Element\File::class,
            'options' => [
                'label' => 'Media file', // @translate
                'info' => $this->getOption('upload_limit'),
            ],
            'attributes' => [
                'accept' => $this->convertFilterFormat(
                    $this->getOption('supported_media_types')
                ),
                'required' => true,
            ],
        ]);
        
        $this->add([
            'name' => 'file[__index__][subtitles]',
            'type' => Element\File::class,
            'options' => [
                'label' => 'Subtitles files', // @translate
            ],
            'attributes' => [
                'class' => 'webvtt-subtitles-file',
                'accept' => $this->convertFilterFormat(
                    $this->getOption('supported_texttrack_types')
                ),
                'multiple' => true,
            ],
        ]);
        
        $this->add([
            'name' => 'o:media[__index__][file_index]',
            'type' => Element\Hidden::class,
            'attributes' => [
                'value' => '__index__',
            ],
        ]);
        
        $this->add([
            'name' => 'o:media[__index__][locale][__subtitleIndex__]',
            'type' => LocaleSelect::class,
            'options' => [
                'label' => 'Subtitles locale', // @translate
                'empty_option' => 'Unknown', // @translate
            ],
            'attributes' => [
                'class' => 'chosen-select webvtt-locale',
                'data-template' => true,
                'disabled' => true,
            ],
        ]);
    }
    
    /*
     * Converts an array of file types to a comma-separated string
     *
     * @param array $types
     * @returns string
     */
    public static function convertFilterFormat($types)
    {
        return implode(",", array_map(function ($type) {
            return "." . $type;
        }, $types));
    }
}