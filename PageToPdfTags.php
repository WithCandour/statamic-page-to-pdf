<?php

namespace Statamic\Addons\PageToPdf;

use Statamic\Extend\Tags;

class PageToPdfTags extends Tags
{
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

    public function script()
    {
        return '<script type="text/javascript" src="/_resources/addons/PageToPdf/js/pagetopdf-buttons.js"></script>';
    }
}

?>
