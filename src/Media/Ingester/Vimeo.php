<?php
namespace Transcript\Media\Ingester;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Omeka\Api\Request;
use Omeka\Entity\Media;
use Omeka\Media\Ingester\IngesterInterface;
use Omeka\Stdlib\ErrorStore;
use Omeka\Settings\Settings;
use Omeka\File\Downloader;
use Laminas\Form\FormElementManager;
use Laminas\Http\Client;
use Laminas\View\Renderer\PhpRenderer;
use Transcript\Form\ConfigForm;
use Transcript\Form\VimeoIngesterForm;
use Vimeo\Vimeo as VimeoAPI;

class Vimeo implements IngesterInterface
{
    /**
     * @var FormElementManager
     */
    protected $formElementManager;
    
    /**
     * @var Laminas\Http\Client
     */
    protected $client;
    
    /*
     * @var Omeka\File\Downloader
     */
    protected $downloader;
    
    /*
     * @var Omeka\Settings\Settings
     */
    protected $settings;
    
    public function __construct(FormElementManager $formElementManager, Client $client, Downloader $downloader, Settings $settings)
    {
        $this->formElementManager = $formElementManager;
        $this->client = $client;
        $this->downloader = $downloader;
        $this->settings = $settings;
    }
    
    public function getLabel()
    {
        return 'Vimeo';
    }
    
    public function getRenderer()
    {
        return 'vimeo';
    }
    
    public function form(PhpRenderer $view, array $options = [])
    {
        $form = $this->formElementManager->get(VimeoIngesterForm::class);        
        return $view->formCollection($form, false);
    }
    
    public function ingest(Media $media, Request $request, ErrorStore $errorStore)
    {
        // Validate the request data
        $data = $request->getContent();
        if (!isset($data['o:source'])) {
            $errorStore->addError('o:source', 'No video URL specified');
            return;
        }
        
        // Validate the URL
        $isMatch = preg_match('/^(?:http\:\/\/|https\:\/\/)?(?:www\.)?(?:vimeo\.com\/).*/', $data['o:source']);
        if (!$isMatch) {
            $errorStore->addError('o:source', sprintf(
                'Invalid video URL: %s',
                $data['o:source']
            ));
            return;
        }
        
        $videoId = array();
        preg_match('/(\d+)(?:\/)*$/', $data['o:source'], $videoId);
        $videoId = $videoId[1];
        
        // Request the video links
        $vimeo = new VimeoAPI(
            $this->settings->get(ConfigForm::SETTING_CLIENT_ID),
            $this->settings->get(ConfigForm::SETTING_CLIENT_SECRET),
            $this->settings->get(ConfigForm::SETTING_ACCESS_TOKEN));
            
        $links = $this->getVideoLinks($vimeo, $videoId, $media, $request, $errorStore);
        if ($links === false)
        {
            return false;
        }
        
        // Download the thumbnail        
        if (!$this->ingestThumbnail($vimeo, $videoId, $media, $request, $errorStore))
        {
            return false;
        }
        
        $tracks = (!$data['capture_vtt']) ? [] : 
            $this->downloadTextTracks($vimeo, $videoId, $errorStore);
        
        if ($tracks === false)
        {
            return false;
        }
        
        // Set the Media source and data
        $media->setSource($data['o:source']);
        $media->setData([
            "links" => $links,
            "texttracks" => $tracks,
        ]);
    }
    
    /*
     * @param Vimeo $vimeo
     * @param string $videoId
     * @param Media $media
     * @param Request $request
     * @param ErrorStore $errorStore
     * @return bool false if fatal error
     */
    private function ingestThumbnail(VimeoAPI $vimeo, $videoId, Media $media, Request $request, ErrorStore $errorStore)
    {
        $thumbnails = $vimeo->request('/videos/' . $videoId . '/pictures', [], "GET");
        
        if ($thumbnails['status'] != 200 || empty($thumbnails['body']['data'][0]['sizes']))
        {
            $errorStore->addError('o:source', sprintf(
                'Error acceessing Vimeo thumbnail API: HTTP %s',
                $thumbnails['status']
            ));
            return false;
        }
        
        $largestWidth = 0;
        foreach ($thumbnails['body']['data'][0]['sizes'] as $thumbnail)
        {
            if ($thumbnail['width'] > $largestWidth)
            {
                $largestWidth = $thumbnail['width'];
                $bestURL = $thumbnail['link'];
            }
        }
        
        $thumbnailFile = $this->downloader->download($bestURL);
        if ($thumbnailFile)
        {
            $thumbnailFile->mediaIngestFile($media, $request, $errorStore, false, true);
        }
        
        return true;
    }
    
    /*
     * @param Vimeo $vimeo
     * @param string $videoId
     * @param Media $media
     * @param Request $request
     * @param ErrorStore $errorStore
     * @return array links to and metadata about video files
     * @return bool false if fatal error
     */
    private function getVideoLinks(VimeoAPI $vimeo, $videoId, Media $media, Request $request, ErrorStore $errorStore)
    {
        $video = $vimeo->request('/videos/' . $videoId, [], "GET");
        
        if ($video['status'] != 200)
        {
            $errorStore->addError('o:source', sprintf(
                'Error acceessing Vimeo thumbnail API: HTTP %s',
                $video['status']
            ));
            return false;
        }
        else if (empty($video['body']['files']))
        {
            $errorStore->addError('o:source', sprintf(
                'Vimeo third-party player API not available. Ensure you have a Pro plan or higher and have allowed your access token the video_files permission. %s', json_encode($video['body']['files'])
            ));
            return false;
        }
        
        $videoLinks = [];
        foreach ($video['body']['files'] as $link) {
            $videoLinks[] = array_filter($link, function ($key) {
                return $key == "quality" ||
                    $key == "rendition" ||
                    $key == "type" ||
                    $key == "width" ||
                    $key == "height" ||
                    $key == "link" ||
                    $key == "fps" ||
                    $key == "public_name";
            }, ARRAY_FILTER_USE_KEY);
        }
        
        return $videoLinks;
    }
    
    /*
     * @param Vimeo $vimeo
     * @param string $videoId
     * @param ErrorStore $errorStore
     * @return array storage IDs and languages of track files
     * @return bool false if fatal error
     */
    private function downloadTextTracks(VimeoAPI $vimeo, $videoId, ErrorStore $errorStore)
    {
        $tracks = $vimeo->request('/videos/' . $videoId . '/texttracks', [], "GET");
        
        if ($tracks['status'] != 200)
        {
            $errorStore->addError('o:source', sprintf(
                'Error acceessing Vimeo text track API: HTTP %s',
                $tracks['status']
            ));
            return false;
        }
        
        $data = [];
        if (!empty($tracks['body']['data']))
        {
            foreach ($tracks['body']['data'] as $track)
            {
                if (!empty($track['link']) || !$track['active'])
                {
                    if ($file = $this->downloader->download($track['link']))
                    {
                        $data[] = [
                            "storage" => $file->store('asset', 'vtt'),
                            "language" => $track['language'],
                        ];
                        $file->delete();
                    }
                }
            }
        }
        return $data;
    }
}
?>