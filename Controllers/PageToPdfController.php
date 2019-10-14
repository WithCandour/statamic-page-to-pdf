<?php

namespace Statamic\Addons\PageToPdf\Controllers;

use Statamic\API\Content;
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

        if(!$uri || !$content) {
            return abort(404);
        }

        $id = $content->id();

        if($exists = $this->api('PageToPdf')->exists($id)) {
            return response($exists, 304);
        }

        return $this->api('PageToPdf')->generate($content);
    }

}
