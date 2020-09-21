<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class ContentController extends BaseController
{
    public function StartAction(Request $request)
    {
        $this->view->body_bg_img_class = 'body-bg-img';
    }
}