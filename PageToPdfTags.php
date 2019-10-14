<?php

namespace Statamic\Addons\PageToPdf;

use Statamic\Extend\Tags;

class PageToPdfTags extends Tags
{
    /**
     * Return a button to trigger pdf generation
     *
     * @return string
     */
    public function index()
    {
        $ctx = collect($this->context);

        $classes = $this->getParam('classes', '');
        $button_text = $this->getParam('button_text', '');

        $data = collect(compact(
            'classes',
            'button_text'
        ));

        return $this->view('pagetopdf_button', $data->merge($ctx));
    }

    /**
     * Return the script tag to load the button event binder
     *
     * @return string
     */
    public function script()
    {
        $installDir = basename(__DIR__);
        return "<script type='text/javascript' src='/_resources/addons/$installDir/js/pagetopdf-buttons.js'></script>";
    }
}

?>
