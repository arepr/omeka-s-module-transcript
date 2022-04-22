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
        
        return $view->partial('common/video-embed', [
            'links' => [
                [
                    'link' => $media->originalUrl(),
                    'type' => $media->mediaType(),
                ]
            ],
            'poster' => $media->thumbnailUrl('large'),
            'texttracks' => $data['texttracks'],
            'default' => $this->getDefaultLanguage($data['texttracks']),
            'color' => $this->settings->get('vimeo_color'),
        ]);
    }
}
?>