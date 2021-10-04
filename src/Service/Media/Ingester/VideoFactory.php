<?php
namespace VimeoEmbed\Service\Media\Ingester;

use VimeoEmbed\Media\Ingester\Video;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class VideoFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new Video($services->get('Omeka\HttpClient'),
            $services->get('Omeka\File\Downloader'),
            $services->get('Omeka\Settings'));
    }
}
?>