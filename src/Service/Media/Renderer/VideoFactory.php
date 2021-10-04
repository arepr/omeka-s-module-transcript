<?php
namespace VimeoEmbed\Service\Media\Renderer;

use VimeoEmbed\Media\Renderer\Video;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class VideoFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new Video($services->get('Omeka\File\Store'));
    }
}
?>