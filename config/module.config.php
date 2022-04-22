<?php declare(strict_types=1);

namespace Transcript;

return [
    'media_ingesters' => [
        'factories' => [
            'vimeo' => Service\Media\Ingester\VimeoFactory::class,
            'webvtt' => Service\Media\Ingester\WebVTTFactory::class,
        ],
    ],
    'media_renderers' => [
        'factories' => [
            'vimeo' => Service\Media\Renderer\VimeoFactory::class,
            'webvtt' => Service\Media\Renderer\WebVTTFactory::class,
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
            Form\SiteSettingsForm::class => Form\SiteSettingsForm::class,
            Form\VimeoIngesterForm::class => Form\VimeoIngesterForm::class,
            Form\WebVTTIngesterForm::class => Form\WebVTTIngesterForm::class,
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