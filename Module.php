<?php declare(strict_types=1);

namespace Transcript;

require_once __DIR__ . '/vendor/autoload.php';

use Generic\AbstractModule;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\EventManager\Event;
use Laminas\View\Renderer\PhpRenderer;

class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;
    
    /**
     * Get this module's configuration array.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    /**
     * Attach listeners to events.
     *
     * @param SharedEventManagerInterface $sharedEventManager
     */
    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            \Omeka\Entity\Media::class,
            'entity.remove.post',
            [$this, 'deleteMediaFiles']
        );
        
        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_elements',
            [$this, 'handleSiteSettings']
        );
    }
    
    /**
     * Clean up text track asset files when media is deleted
     *
     * @param Event $event
     */
    public function deleteMediaFiles(Event $event)
    {
        $media = $event->getTarget();
        if ($media->getIngester() == 'vimeo' || $media->getIngester() == 'webvtt')
        {
            $store = $this->getServiceLocator()->get('Omeka\File\Store');
            $files = $media->getData()['texttracks'];
            foreach ($files as $file)
            {
                $store->delete($file['storage']);
            }
        }
    }
    
    /*
     * @param string $locale
     * @return string
     */
    public static function stripLocaleToLang($locale)
    {
        return explode('_', $locale)[0];
    }
}

?>