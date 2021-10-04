<?php
namespace VimeoEmbed\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class EmbedViewHelper extends AbstractHelper
{
    /**
     * The default partial view script.
     */
    const PARTIAL_NAME = 'common/helper/embed';
    
    public function __invoke($options = null)
    {
        $template = $options['template'] ?? self::PARTIAL_NAME;
        return $this->getView()->partial($template, $options);
    }
}
?>