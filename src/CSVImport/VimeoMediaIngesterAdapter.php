<?php
namespace Transcript\CSVImport;

use CSVImport\MediaIngesterAdapter\MediaIngesterAdapterInterface;

class VimeoMediaIngesterAdapter implements MediaIngesterAdapterInterface
{
    public function getJson($mediaDatum)
    {
        return [
            'o:source' => $mediaDatum,
            'capture_vtt' => true
        ];
    }
}
?>