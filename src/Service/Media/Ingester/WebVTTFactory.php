<?php
namespace Transcript\Service\Media\Ingester;

use Transcript\Media\Ingester\WebVTT;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class WebVTTFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new WebVTT($services->get('FormElementManager'),
            $services->get('Omeka\File\Uploader'));
    }
}
?>