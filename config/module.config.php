<?php declare(strict_types=1);

namespace Transcript;

return [
    'media_ingesters' => [
        'factories' => [
            'vimeo' => Service\Media\Ingester\VimeoFactory::class,
        ],
    ],
    'media_renderers' => [
        'factories' => [
            'vimeo' => Service\Media\Renderer\VimeoFactory::class,
        ],
    ],
    'csv_import' => [
        'media_ingester_adapter' => [
            'vimeo' => CSVImport\VimeoMediaIngesterAdapter::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\ConfigForm::class => Form\ConfigForm::class,
            Form\VimeoIngesterForm::class => Form\VimeoIngesterForm::class,
            Form\SiteSettingsForm::class => Form\SiteSettingsForm::class,
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ]
];

?>