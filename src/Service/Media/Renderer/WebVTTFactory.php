<?php
namespace Transcript\Service\Media\Renderer;

use Transcript\Media\Renderer\WebVTT;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class WebVTTFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new WebVTT($services->get('Omeka\File\Store'),
            $services->get('Omeka\Settings\Site'));
    }
}
?>