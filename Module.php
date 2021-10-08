<?php declare(strict_types=1);

namespace VimeoEmbed;

use Omeka\Module\AbstractModule;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\EventManager\Event;
use Laminas\View\Renderer\PhpRenderer;
use VimeoEmbed\Form\ConfigForm;

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

        $settings = $this->getSettings();
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

        $this->setSettings($settings);
        return true;
    }
    
    /**
     * @return array Saved settings
     */
    public function getSettings()
    {
        $service = $this->getServiceLocator()->get('Omeka\Settings');
        $settings = [];
        foreach (array_keys(Form\ConfigForm::SETTINGS_ALL) as $key)
        {
            $setting = $service->get($key);
            if (!is_null($setting)) { $settings[$key] = $setting; }
        }
        return $settings;
    }
    
    /**
     * @param array $settings New settings values
     */
    private function setSettings($settings)
    {
        $service = $this->getServiceLocator()->get('Omeka\Settings');
        foreach ($settings as $key => $value)
        {
            $service->set($key, $value);
        }
    }
}

?>