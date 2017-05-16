<?php

namespace App\Bundle\SiteBundle\Controller;

use App\Bundle\SiteBundle\Helper\CoreHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /** @var  CoreHelper */
    protected $coreHelper;

    /**
     * Homepage action
     * @param View $view
     * @return View
     */
    public function indexAction(View $view)
    {
        $this->coreHelper = $this->container->get('app.core_helper');
        $worksItemContentTypeIdentifier = $this->container->getParameter('app.work.content_type.identifier');
        $xpContentTypeIdentifier = $this->container->getParameter('app.experience.content_type.identifier');
        $skillContentTypeIdentifier = $this->container->getParameter('app.skill.content_type.identifier');
        $worksLocationId = $this->container->getParameter('app.works.locationid');
        $skillsLocationId = $this->container->getParameter('app.skills.locationid');
        $xpLocationId = $this->container->getParameter('app.xp.locationid');

        $params['works'] = $this->coreHelper->getChildrenObject([$worksItemContentTypeIdentifier], $worksLocationId);
        $params['languages'] = $this->coreHelper->getObjectByType($this->container->getParameter('app.type.language'),
            $skillsLocationId,
            $skillContentTypeIdentifier,
            'note',
            Query::SORT_DESC);
        $params['tools'] = $this->coreHelper->getObjectByType($this->container->getParameter('app.type.tools'),
            $skillsLocationId,
            $skillContentTypeIdentifier,
            'note',
            Query::SORT_DESC);
        $params['skills'] = $this->coreHelper->getObjectByType($this->container->getParameter('app.type.skill'),
            $skillsLocationId,
            $skillContentTypeIdentifier,
            'note',
            Query::SORT_DESC);

        $params['educations'] = $this->coreHelper->getObjectByType($this->container->getParameter('app.type.education'),
            $xpLocationId,
            $xpContentTypeIdentifier);
        $params['xps'] = $this->coreHelper->getObjectByType($this->container->getParameter('app.type.work'),
            $xpLocationId,
            $xpContentTypeIdentifier);

        $response = new Response();
        $response->headers->set('X-Location-Id', $view->getLocation()->id);
        $response->setEtag(md5(json_encode($params)));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('app.cache.high.ttl'));
        //$response->setLastModified();
        $view->setResponse($response);

        $view->addParameters([
            'params' => $params,
        ]);

        return $view;
    }
}
