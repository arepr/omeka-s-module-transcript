<?php
namespace Transcript\Media\Renderer;

use Transcript\Media\Renderer\Generic;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\MediaRepresentation;
use Transcript\Media\Ingester\WebVTT as WebVTTIngester;

class WebVTT extends Generic
{
    public function render(PhpRenderer $view, MediaRepresentation $media, array $userOptions = [])
    {
        $data = $media->mediaData();
        $data['texttracks'] = $this->prepareTextTracks($data['texttracks']);
        
        $baseOptions = [
            'texttracks' => $data['texttracks'],
            'default' => $this->getDefaultLanguage($data['texttracks']),
            'color' => $this->settings->get('vimeo_color'),
        ];
        
        $userOptions = array_intersect_key($userOptions, Generic::DEFAULT_OPTIONS)
            + Generic::DEFAULT_OPTIONS;
        
        if (in_array($media->extension(), WebVTTIngester::SUPPORTED_TYPES['audio']))
        {
            // Render audio file
            return $view->partial('common/audio-embed', $baseOptions + $userOptions + [
                'link' => $media->originalUrl(),
            ]);
        }
        else if (in_array($media->extension(), WebVTTIngester::SUPPORTED_TYPES['video']))
        {
            // Render video file
            return $view->partial('common/video-embed', $baseOptions + $userOptions + [
                'links' => [
                    [
                        'link' => $media->originalUrl(),
                        'type' => $media->mediaType(),
                    ]
                ],
            ]);
        }
        
        return false;
    }
}
?>