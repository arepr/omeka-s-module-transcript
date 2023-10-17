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

        $isAudio = in_array($media->extension(), WebVTTIngester::SUPPORTED_TYPES['audio']);
        $isVideo = in_array($media->extension(), WebVTTIngester::SUPPORTED_TYPES['video']);

        if ($isAudio xor $isVideo)
        {
            return $view->partial('common/media-embed', $baseOptions + $userOptions + [
                'type' => $isVideo ? 'video' : 'audio',
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