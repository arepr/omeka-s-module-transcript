<?php
namespace VimeoEmbed\Media\Ingester;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Omeka\Api\Request;
use Omeka\Entity\Media;
use Omeka\Media\Ingester\IngesterInterface;
use Omeka\Stdlib\ErrorStore;
use Omeka\Settings\Settings;
use Omeka\File\Downloader;
use Laminas\Form\Element\Text;
use Laminas\Http\Client;
use Laminas\View\Renderer\PhpRenderer;
use VimeoEmbed\Form\ConfigForm;
use Vimeo\Vimeo;

class Video implements IngesterInterface
{
    protected $client;
    protected $downloader;
    protected $settings;
    
    public function __construct(Client $client, Downloader $downloader, Settings $settings)
    {
        $this->client = $client;
        $this->downloader = $downloader;
        $this->settings = $settings;
    }
    
    public function getLabel()
    {
        return 'Vimeo'; // @translate
    }
    
    public function getRenderer()
    {
        return 'vimeo';
    }
    
    public function form(PhpRenderer $view, array $options = [])
    {
        $input = new Text('o:media[__index__][o:source]');
        $input->setOptions([
            'label' => 'Video URL', // @translate
            'info' => 'URL for the Vimeo video to embed.', // @translate
        ]);
        $input->setAttributes([
            'required' => true,
        ]);
        return $view->formRow($input);
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
        
        // Get the oEmbed JSON
        $url = sprintf('https://vimeo.com/api/oembed.json?url=%s', urlencode($data['o:source']));
        $response = $this->client->setUri($url)->send();
        if (!$response->isOk()) {
            $errorStore->addError('o:source', sprintf(
                'Error reading video: %s (%s)',
                $response->getReasonPhrase(),
                $response->getStatusCode()
            ));
            return false;
        }
        $oembed = json_decode($response->getBody(), true);
        
        // Download the thumbnail
        // A thumbnail URL is already provided with the oEmbed packet,
        // however it's lower resolution compared to calling the API
        $vimeo = new Vimeo(
            $this->settings->get(ConfigForm::SETTING_CLIENT_ID),
            $this->settings->get(ConfigForm::SETTING_CLIENT_SECRET),
            $this->settings->get(ConfigForm::SETTING_ACCESS_TOKEN));
        
        if (!$this->ingestThumbnail($vimeo, $oembed['video_id'], $media, $request, $errorStore))
        {
            return false;
        }
        
        $tracks = $this->downloadTextTracks($vimeo, $oembed['video_id'], $errorStore);
        if ($tracks === false)
        {
            return false;
        }
        
        // Set the Media source and data
        $media->setSource($data['o:source']);
        $media->setData([
            "html" => $oembed["html"],
            "texttracks" => $tracks,
        ]);
    }
    
    /*
     * @param Vimeo $vimeo
     * @param int $videoId
     * @param Media $media
     * @param Request $request
     * @param ErrorStore $errorStore
     * @return bool false if fatal error
     */
    private function ingestThumbnail(Vimeo $vimeo, $videoId, Media $media, Request $request, ErrorStore $errorStore)
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
     * @param int $videoId
     * @param ErrorStore $errorStore
     * @return array storage IDs and languages of track files
     * @return bool false if fatal error
     */
    private function downloadTextTracks(Vimeo $vimeo, $videoId, ErrorStore $errorStore)
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