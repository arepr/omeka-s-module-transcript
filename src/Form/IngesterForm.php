<?php
namespace VimeoEmbed\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;

class IngesterForm extends Form
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
    }
}