<?php
namespace VimeoEmbed\CSVImport;

use CSVImport\MediaIngesterAdapter\MediaIngesterAdapterInterface;

class VimeoMediaIngesterAdapter implements MediaIngesterAdapterInterface
{
    public function getJson($mediaDatum)
    {
        return [ 'o:source' => $mediaDatum ];
    }
}
?>