<?php declare(strict_types=1);

namespace Transcript;

use Transcript\Form\ConfigForm;
use Transcript\Form\SiteSettingsFieldset;

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
            Form\SiteSettingsFieldset::class => Form\SiteSettingsFieldset::class,
            Form\VimeoIngesterFieldset::class => Form\VimeoIngesterFieldset::class,
            Form\WebVTTIngesterFieldset::class => Form\WebVTTIngesterFieldset::class,
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
    ],
    'js_translate_strings' => [
        'Video player', // @translate
        'Play', // @translate
        'Pause', // @translate
        'Timecode', // @translate
        'Duration', // @translate
        'Mute', // @translate
        'Unmute', // @translate
        'Volume', // @translate
        'Picture in picture', // @translate
        'Fullscreen', // @translate
        'Exit fullscreen', // @translate
        'Audio player', // @translate
        'Skip forwards 15 seconds', // @translate
        'Skip backwards 15 seconds', // @translate
        'Transcript language', // @translate
        'Close transcript' // @translate
    ],
    'transcript' => [
        'config' => ConfigForm::SETTINGS_ALL,
        'site_settings' => SiteSettingsFieldset::SETTINGS_ALL
    ]
];

?>