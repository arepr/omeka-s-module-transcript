<?php
namespace Transcript\Service\Media\Ingester;

use Transcript\Media\Ingester\Vimeo;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class VimeoFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new Vimeo($services->get('FormElementManager'),
            $services->get('Omeka\HttpClient'),
            $services->get('Omeka\File\Downloader'),
            $services->get('Omeka\Settings'));
    }
}
?>