<?php
namespace Transcript\Service\Media\Renderer;

use Transcript\Media\Renderer\Vimeo;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class VimeoFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new Vimeo($services->get('Omeka\File\Store'),
            $services->get('Omeka\Settings\Site'));
    }
}
?>