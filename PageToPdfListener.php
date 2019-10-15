<?php

namespace Statamic\Addons\PageToPdf;

use Statamic\Extend\Listener;
use Statamic\Events\Data\ContentSaved;

class PageToPdfListener extends Listener
{
    public $events = [
        ContentSaved::class => 'clearCachedPDF'
    ];

    public function clearCachedPDF(ContentSaved $event)
    {
        $id = $event->data->id();
        $this->api('PageToPdf')->delete($id);
    }
}
