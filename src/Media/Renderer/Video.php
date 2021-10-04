<?php
namespace VimeoEmbed\Media\Renderer;

use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Media\Renderer\RendererInterface;
use Omeka\File\Store\StoreInterface;
use Laminas\View\Renderer\PhpRenderer;
use VimeoEmbed\Module;
use VimeoEmbed\View\Helper\EmbedViewHelper;

class Video implements RendererInterface
{
    protected $store;
    
    public function __construct(StoreInterface $store)
    {
        $this->store = $store;
    }
    
    public function render(PhpRenderer $view, MediaRepresentation $media, array $options = [])
    {
        // Use the iframe HTML from the stored Vimeo oEmbed response
        $data = $media->mediaData();

        foreach ($data['texttracks'] as &$track)
        {
            $track['storage'] = $this->store->getUri($track['storage']);
            $track['language-label'] = ucwords(\Locale::getDisplayName($track['language'], $track['language']));
        }
        
        return $view->embed([
            'iframe' => $data['html'],
            'texttracks' => $data['texttracks'],
        ]);
    }
}
?>