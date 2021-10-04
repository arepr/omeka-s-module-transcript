<?php declare(strict_types=1);

namespace VimeoEmbed;

return [
    'media_ingesters' => [
        'factories' => [
            'vimeo' => Service\Media\Ingester\VideoFactory::class,
        ],
    ],
    'media_renderers' => [
        'factories' => [
            'vimeo' => Service\Media\Renderer\VideoFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'embed' => View\Helper\EmbedViewHelper::class,
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\ConfigForm::class => Form\ConfigForm::class
        ],
    ]
];

?>