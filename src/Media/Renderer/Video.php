<?php
namespace VimeoEmbed\Media\Renderer;

use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Media\Renderer\RendererInterface;
use Omeka\File\Store\StoreInterface;
use Omeka\Settings\SiteSettings;
use Omeka\Service\Exception\RuntimeException;
use Laminas\View\Renderer\PhpRenderer;
use VimeoEmbed\Module;
use VimeoEmbed\View\Helper\EmbedViewHelper;

class Video implements RendererInterface
{
    protected $store;
    protected $settings;
    
    public function __construct(StoreInterface $store, SiteSettings $settings)
    {
        $this->store = $store;
        $this->settings = $settings;
    }
    
    public function render(PhpRenderer $view, MediaRepresentation $media, array $options = [])
    {
        // Use the iframe HTML from the stored Vimeo oEmbed response
        $data = $media->mediaData();
        
        // Use the site's locale setting to choose the default track,
        // or fall back to the first in the list
        $default = $this->getSiteLocale();
        $defaultFound = false;

        foreach ($data['texttracks'] as &$track)
        {
            $track['storage'] = $this->store->getUri($track['storage']);
            $track['language-label'] = ucwords(\Locale::getDisplayName($track['language'], $track['language']));
            if ($track['language'] == $default) { $defaultFound = true; }
        }
        
        if (!$defaultFound)
        {
            $default = $data['texttracks'][0]['language'];
        }
        
        return $view->embed([
            'iframe' => $data['html'],
            'texttracks' => $data['texttracks'],
            'default' => $default,
        ]);
    }
    
    private function getSiteLocale()
    {
        try
        {
            $locale = $this->settings->get('locale');
            return explode('_', $locale)[0];
        }
        catch (RuntimeException $e)
        {
            return 'en';
        }
    }
}
?>