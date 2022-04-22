<?php declare(strict_types=1);

namespace Transcript;

use Omeka\Module\AbstractModule;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\EventManager\Event;
use Laminas\View\Renderer\PhpRenderer;
use Transcript\Form\ConfigForm;

class Module extends AbstractModule
{
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
            [$this, 'getSiteSettingsForm']
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
    
    /**
     * Get this module's configuration form.
     *
     * @param ViewModel $view
     * @return string
     */
    public function getConfigForm(PhpRenderer $renderer)
    {        
        $services = $this->getServiceLocator();
        $formManager = $services->get('FormElementManager');
        $formClass = Form\ConfigForm::class;
        if (!$formManager->has($formClass))
        {
            return '';
        }

        $settings = $this->getSettings(true);
        $form = $formManager->get($formClass);
        $form->init();
        if ($settings) { $form->setData($settings); }
        $form->prepare();
        return $renderer->formCollection($form);
    }
    
    /**
     * Handle this module's configuration form.
     *
     * @param AbstractController $controller
     * @return bool False if there was an error during handling
     */
    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $formManager = $services->get('FormElementManager');
        $formClass = Form\ConfigForm::class;
        if (!$formManager->has($formClass))
        {
            return false;
        }

        $form = $formManager->get($formClass);
        $form->init();
        $form->setData($controller->getRequest()->getPost());
        if (!$form->isValid())
        {
            $controller->messenger()->addErrors($form->getMessages());
            return false;
        }

        $settings = array_intersect_key($form->getData(),
            Form\ConfigForm::SETTINGS_ALL);

        $this->setSettings($settings, true);
        return true;
    }
    
    /**
     * @param Event $event
     */
    public function getSiteSettingsForm(Event $event)
    {
        $services = $this->getServiceLocator();
        $formManager = $services->get('FormElementManager');
        $fieldsetClass = Form\SiteSettingsForm::class;
        
        $fieldset = $formManager->get($fieldsetClass);
        $fieldset->setName(__NAMESPACE__);
            
        $form = $event->getTarget();
        $form->add($fieldset);
        $form->get(__NAMESPACE__)->populateValues($this->getSettings(false));
    }
    
    /**
     * @return array Saved settings
     */
    public function getSettings($global)
    {
        $service = $this->getSettingsService($global);
        $formClass = ($global) ? Form\ConfigForm::class : Form\SiteSettingsForm::class;
        
        $settings = [];
        foreach (array_keys($formClass::SETTINGS_ALL) as $key)
        {
            $setting = $service->get($key);
            if (!is_null($setting)) { $settings[$key] = $setting; }
        }
        return $settings;
    }
    
    /**
     * @param array $settings New settings values
     */
    private function setSettings($settings, $global)
    {
        $service = $this->getSettingsService($global);
        foreach ($settings as $key => $value)
        {
            $service->set($key, $value);
        }
    }
    
    /*
     * @return Settings|SiteSettings
     */
    private function getSettingsService($global)
    {
        return $this->getServiceLocator()->get(
            ($global) ? 'Omeka\Settings' : 'Omeka\Settings\Site');
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