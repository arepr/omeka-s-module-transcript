<?php
namespace Transcript\Media\Renderer;

use Transcript\Media\Renderer\Generic;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\MediaRepresentation;
use Transcript\Media\Ingester\WebVTT as WebVTTIngester;

class WebVTT extends Generic
{
    public function render(PhpRenderer $view, MediaRepresentation $media, array $options = [])
    {
        $data = $media->mediaData();
        $data['texttracks'] = $this->prepareTextTracks($data['texttracks']);
        
        $options = [
            'texttracks' => $data['texttracks'],
            'default' => $this->getDefaultLanguage($data['texttracks']),
            'color' => $this->settings->get('vimeo_color'),
        ];
        
        if (in_array($media->extension(), WebVTTIngester::SUPPORTED_TYPES['audio']))
        {
            return $view->partial('common/audio-embed', array_merge($options, [
                'link' => $media->originalUrl(),
            ]));
        }
        else if (in_array($media->extension(), WebVTTIngester::SUPPORTED_TYPES['video']))
        {
            return $view->partial('common/video-embed', array_merge($options, [
                'links' => [
                    [
                        'link' => $media->originalUrl(),
                        'type' => $media->mediaType(),
                    ]
                ],
            ]));
        }
        else
        {
            return false;
        }
    }
}
?>