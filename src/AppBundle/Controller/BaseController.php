<?php

namespace AppBundle\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pimcore\Model\DataObject;

class BaseController extends FrontendController
{
    /**
     * @inheritDoc
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        // enable view auto-rendering
        $this->setViewAutoRender($event->getRequest(), true, 'twig');
    }

    /**
     * Help function
     * Get member by email
     *
     * @param String $email
     *
     * @return Object|null
     */
    protected function getMember($email)
    {
        // Get member
        $member = DataObject\Members::getByEmail($email, ['limit' => 1]);

        return $member;
    }
}
