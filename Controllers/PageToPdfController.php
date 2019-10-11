<?php

namespace Statamic\Addons\PageToPdf\Controllers;

use GuzzleHttp\Client as Guzzle;
use Statamic\API\Asset;
use Statamic\API\AssetContainer;
use Statamic\API\Content;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\Extend\Controller;

class PageToPdfController extends Controller
{
    /**
     * Endpoint to generate a pdf version of the page
     */
    public function postGeneratePDF()
    {
        $request = request();
        $uri = $request->get('uri', '');
        $content = Content::whereUri($uri);

        if(!$content) {
            return abort(404);
        }

        $id = $content->id();

        $response = $this->getPdf($uri);

        if($response['status_code'] == 200) {
            $path = $this->get_path() . "/$id.pdf";
            $url = $this->storePDF($response['contents'], $path);
            return json_encode([
                'status' => 'success',
                'file' => $url
            ]);
        }

        return json_encode([
            'status' => 'error',
            'status_code' => $response['status_code'],
            'error' => $response['reason_phrase']
        ]);
    }

    private function storePDF($contents, $path)
    {
        $container = AssetContainer::find($this->get_container());
        $url = '';

        switch($container->driver()) {
            case 's3':
                $url = $this->createS3Asset($contents, $path);
            break;
            case 'local':
            default:
                $url = $this->createLocalAsset($contents, $path);
            break;
        }

        return $url;
    }

    private function getPdf($uri)
    {
        $client = new Guzzle();
        $test_endpoint = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
        $pdfmyurl_response = $client->get($test_endpoint);
        $response_contents = $pdfmyurl_response->getBody()->getContents();
        return [
            'status_code' => $pdfmyurl_response->getStatusCode(),
            'reason_phrase' => $pdfmyurl_response->getReasonPhrase(),
            'contents' => $response_contents,
        ];
    }

    /**
     * Return the license key from the addon config
     *
     * @return string
     */
    private function get_key()
    {
        return $this->getConfig('pdfmyurl_license_key', '');
    }

    /**
     * Return the asset container for storing the pdf assets
     *
     * @return string
     */
    private function get_container()
    {
        return $this->getConfig('pdf_asset_container', 'main');
    }

    /**
     * Return the asset folder for storing the files
     *
     * @return string
     */
    private function get_path()
    {
        return $this->getConfig('pdf_asset_path', '/assets/html-pdfs/');
    }

    /**
     * Create an asset in a container using the local driver
     *
     * @param string $contents
     * @param string $filepath
     * @return string
     */
    public function createLocalAsset($contents, $path)
    {
        File::put($path, $contents);
        return Path::toUrl($path);
    }
}
