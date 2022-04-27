<?php
namespace Transcript\Media\Renderer;

use Omeka\Media\Renderer\RendererInterface;
use Omeka\File\Store\StoreInterface;
use Omeka\Settings\SiteSettings;
use Omeka\Service\Exception\RuntimeException;
use Laminas\View\Renderer\PhpRenderer;
use Transcript\Module;

abstract class Generic implements RendererInterface
{
    public const DEFAULT_OPTIONS = [
        'hideTranscript' => false,
        'compactMode' => false,
    ];
    
    protected $store;
    protected $settings;
    
    public function __construct(StoreInterface $store, SiteSettings $settings)
    {
        $this->store = $store;
        $this->settings = $settings;
    }
    
    /*
     * @param array $tracks
     * @returns array
     */
    protected function prepareTextTracks($tracks)
    {
        foreach ($tracks as &$track)
        {
            $track['storage'] = $this->store->getUri($track['storage']);
            $track['language-label'] = ucwords(\Locale::getDisplayName($track['language'], $track['language']));
        }
        unset($track);
        
        return $tracks;
    }
    
    /*
     * @param array $tracks
     * @returns string
     */
    protected function getDefaultLanguage($tracks)
    {
        // Use the site's locale setting to choose the default track,
        // or fall back to the first in the list
        $default = $this->getSiteLocale();
        
        if (empty($tracks))
        {
            return $default;
        }
        
        $defaultFound = false;
        foreach ($tracks as $track)
        {
            if ($track['language'] == $default)
            {
                $defaultFound = true;
            }
        }
        
        if (!$defaultFound)
        {
            $default = $tracks[0]['language'];
        }
        
        return $default;
    }
    
    protected function getSiteLocale()
    {
        try
        {
            $locale = $this->settings->get('locale');
            return Module::stripLocaleToLang($locale);
        }
        catch (RuntimeException $e)
        {
            return 'en';
        }
    }
}
?>