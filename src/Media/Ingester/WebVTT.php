<?php
namespace Transcript\Media\Ingester;

use Omeka\Api\Request;
use Omeka\Entity\Media;
use Omeka\Media\Ingester\IngesterInterface;
use Omeka\Stdlib\ErrorStore;
use Omeka\File\Uploader;
use Laminas\Form\FormElementManager;
use Laminas\View\Renderer\PhpRenderer;
use Transcript\Module;
use Transcript\Form\WebVTTIngesterForm;

class WebVTT implements IngesterInterface
{
    public const SUPPORTED_TYPES = [
        'video' => [
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
        ],
        'audio' => [
            'audio/mpeg' => 'mp3',
            'audio/mp4' => 'm4a',
            'audio/m4a' => 'm4a',
            'audio/x-m4a' => 'm4a',
            'audio/wav' => 'wav',
        ],
        'texttrack' => [
            'text/vtt' => 'vtt',
        ],
    ];
    
    /**
     * @var FormElementManager
     */
    protected $formElementManager;
    
    /*
     * @var Omeka\File\Uploader
     */
    protected $uploader;
    
    public function __construct(FormElementManager $formElementManager, Uploader $uploader)
    {
        $this->formElementManager = $formElementManager;
        $this->uploader = $uploader;
    }
    
    public function getLabel()
    {
        return 'WebVTT';
    }
    
    public function getRenderer()
    {
        return 'webvtt';
    }
    
    public function prepareForm(PhpRenderer $view)
    {
        $view->headScript()->appendFile($view->assetUrl('js/webvtt-ingester.js', 'Transcript'));
    }
    
    public function form(PhpRenderer $view, array $options = [])
    {
        $this->prepareForm($view);
        
        $form = $this->formElementManager->get(WebVTTIngesterForm::class, [
            'upload_limit' =>
                $view->uploadLimit(),
            'supported_media_types' =>
                array_unique(
                    array_values(
                        array_merge(
                            self::SUPPORTED_TYPES['video'],
                            self::SUPPORTED_TYPES['audio']
                        )
                    )
                ),
            'supported_texttrack_types' =>
                self::SUPPORTED_TYPES['texttrack'],
        ]);
        
        return $view->formCollection($form, false);
    }
    
    public function ingest(Media $media, Request $request, ErrorStore $errorStore)
    {
        // Validate request data
        $data = $request->getContent();
        $fileData = $request->getFileData();
        
        if (!isset($fileData['file']))
        {
            $errorStore->addError('error', 'No files were uploaded');
            return false;
        }
        
        if (!isset($data['file_index']))
        {
            $errorStore->addError('error', 'No file index was specified');
            return false;
        }
        
        $index = $data['file_index'];
        if (!isset($fileData['file'][$index]))
        {
            $errorStore->addError('error', 'No file uploaded for the specified index');
            return false;
        }
        
        if (!empty($fileData['file'][$index]['subtitles'][0]['name']))
        {
            if (count($fileData['file'][$index]['subtitles']) != count($data['locale']) ||
                in_array('', $data['locale']))
            {
                $errorStore->addError('error', 'Missing subtitles\' locale specification');
                return false;
            }
        }
        
        // Store uploaded files
        if (!$this->uploadMedia($fileData['file'][$index]['media'], $media, $request, $errorStore))
        {
            return false;
        }
        
        $tracks = [];
        foreach ($fileData['file'][$index]['subtitles'] as $subtitleIndex => $file)
        {
            if (empty($file['name']))
            {
                break;
            }
            
            if (!$storage = $this->uploadTextTrack($file, $media, $request, $errorStore))
            {
                return false;
            }
            
            $tracks[] = [
                "storage" => $storage,
                "language" => Module::stripLocaleToLang($data["locale"][$subtitleIndex])
            ];
        }
        
        $media->setData([
            "texttracks" => $tracks
        ]);
    }
    
    /*
     * @param array $metadata
     * @param Media $media
     * @param Request $request
     * @param ErrorStore $errorStore
     * @return bool false if fatal error
     */
    private function uploadMedia($metadata, Media $media, Request $request, ErrorStore $errorStore)
    {
        if (!in_array($metadata['type'], array_keys(self::SUPPORTED_TYPES['video'])) &&
            !in_array($metadata['type'], array_keys(self::SUPPORTED_TYPES['audio'])))
        {
            $errorStore->addError('error', sprintf('Unsupported media format: %s', $metadata['type']));
            return false;
        }
        
        $file = $this->uploader->upload($metadata, $errorStore);
        if (!$file)
        {
            $errorStore->addError('error', 'Unknown error storing media file');
            return false;
        }
        
        $file->setSourceName($metadata['name']);
        $media->setSource($metadata['name']);
        $file->mediaIngestFile($media, $request, $errorStore);
        
        return true;
    }
    
    /*
     * @param array $metadata
     * @param Media $media
     * @param Request $request
     * @param ErrorStore $errorStore
     * @return bool false if fatal error
     */
    private function uploadTextTrack($metadata, Media $media, Request $request, ErrorStore $errorStore)
    {
        if (!in_array($metadata['type'], array_keys(self::SUPPORTED_TYPES['texttrack'])))
        {
            $errorStore->addError('error', sprintf('Unsupported subtitle format: %s', $metadata['type']));
            return false;
        }
        
        $file = $this->uploader->upload($metadata, $errorStore);
        if (!$file)
        {
            $errorStore->addError('error', 'Unknown error storing subtitles file');
            return false;
        }
        
        $extension = self::SUPPORTED_TYPES['texttrack'][$metadata['type']];
        $storage = $file->store('asset', $extension);
        $file->delete();
        
        return $storage;
    }
}

?>