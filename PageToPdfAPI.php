<?php

namespace Statamic\Addons\PageToPdf;

use GuzzleHttp\Client as Guzzle;
use Statamic\API\AssetContainer;
use Statamic\API\Content;
use Statamic\API\Folder;
use Statamic\API\URL;
use Statamic\Extend\API;

class PageToPdfAPI extends API
{

    /**
     * Check if a PDF exists for the current content
     *
     * @param string $id
     * @return bool $exists
     */
    public function exists($id)
    {
        $container = $this->get_container();
        return $container->disk()->filesystem()->exists($this->get_path() . $id);
    }

    /**
     * Generate a new PDF by making a call to the API
     *
     * @param Statamic\Data\Content\Content $content
     * @return array
     */
    public function generate($content)
    {
        $client = new Guzzle();

        $url = $content->absoluteUrl();
        $id = $content->id();

        if($existing = $this->retrieve($id)) {
            return json_encode([
                'status' => 'cached',
                'status_code' => 304,
                'reason_phrase' => 'File exists in filesystem',
                'file' => $existing
            ]);
        }

        $endpoint = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
        $pdfmyurl_response = $client->get($endpoint);

        $response_contents = $pdfmyurl_response->getBody()->getContents();

        $result = [
            'reason_phrase' => $pdfmyurl_response->getReasonPhrase(),
            'status_code' => $pdfmyurl_response->getStatusCode()
        ];

        if($result['status_code'] == 200) {
            $url = $this->store($id, $response_contents);
            $data = [
                'file' => $url,
                'status' => 'success',
            ];
        } else {
            // TODO: Error logging
            $data = [
                'file' => null,
                'status' => 'error'
            ];
        }

        return json_encode(array_merge($result, $data));
    }

    /**
     * Retrieve an existing generated pdf
     *
     * @param string $id
     * @return array
     */
    public function retrieve($id = '')
    {
        $container = $this->get_container();
        $filesystem = $container->disk()->filesystem();
        $files = $filesystem->files($this->get_path() . '/' . $id);
        if($files) {
            return URL::makeAbsolute($container->url() . '/' . $files[0]);
        } else {
            return false;
        }
    }

    /**
     * Delete the cached pdf by id
     *
     * @param string $id
     */
    public function delete($id)
    {
        $container = $this->get_container();
        $container->disk()->filesystem()->deleteDirectory($this->get_path() . '/' . $id);
    }

    /**
     * Store a PDF in the filesystem
     *
     * @param string $id
     * @param string $contents
     */
    public function store($id, $contents)
    {
        $container = $this->get_container();
        $slug = Content::find($id)->slug();
        $path = $this->get_path() . "/$id/$slug.pdf";

        $container->disk()->filesystem()->put($path, $contents);

        return URL::makeAbsolute($container->url() . $path);
    }

    /**
     * Return the asset container for storing the pdf assets
     *
     * @return string
     */
    private function get_container()
    {
        $config = $this->getConfig('pdf_asset_container', 'main');
        return AssetContainer::find($config);
    }

    /**
     * Return the asset folder for storing the files
     *
     * @return string
     */
    private function get_path()
    {
        $config = $this->getConfig('pdf_asset_path', '/html-pdfs');
        return rtrim($config, '/');
    }
}
