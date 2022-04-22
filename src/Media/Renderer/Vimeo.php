<?php
namespace Transcript\Media\Renderer;

use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Media\Renderer\RendererInterface;
use Omeka\File\Store\StoreInterface;
use Omeka\Settings\SiteSettings;
use Omeka\Service\Exception\RuntimeException;
use Laminas\View\Renderer\PhpRenderer;
use Transcript\Module;
use Transcript\View\Helper\EmbedViewHelper;

class Vimeo implements RendererInterface
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
        $data = $media->mediaData();
        
        if (empty($data['links']))
        {
            // File needs to be reimported by admin
            return false;
        }
        
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
        
        if (!$defaultFound && $data['texttracks'])
        {
            $default = $data['texttracks'][0]['language'];
        }
        
        return $view->embed([
            'links' => $data['links'],
            'poster' => $media->thumbnailUrl('large'),
            'texttracks' => $data['texttracks'],
            'default' => $default,
            'color' => $this->settings->get('vimeo_color'),
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