<?php
namespace Transcript\Form;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;

class VimeoIngesterForm extends Fieldset
{
    public function init()
    {
        $this->add([
            'name' => 'o:media[__index__][o:source]',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Video URL', // @translate
                'info' => 'URL of the Vimeo video to embed.', // @translate
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);
        
        $this->add([
            'name' => 'o:media[__index__][capture_vtt]',
            'type' => Element\Checkbox::class,
            'options' => [
                'label' => 'Capture subtitles', // @translate
                'info' => 'When checked, any subtitles uploaded to Vimeo will be captured and stored locally. This is required to utilize the transcript sidebar.', // @translate
            ],
            'attributes' => [
                'checked' => true,
            ],
        ]);
    }
}