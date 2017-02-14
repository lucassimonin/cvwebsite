<?php

namespace App\Bundle\SiteBundle\Controller;

use App\Bundle\SiteBundle\Helper\CoreHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Bundle\SiteBundle\Entity\Contact;
use Symfony\Component\HttpFoundation\JsonResponse;

class HomeController extends Controller
{
    /** @var  CoreHelper */
    protected $coreHelper;

    /**
     * Homepage action
     * @param $locationId
     * @param $viewType
     * @param bool $layout
     * @param array $params
     * @return mixed
     */
    public function indexAction($locationId, $viewType, $layout = false, array $params = array())
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
            $skillContentTypeIdentifier);
        $params['tools'] = $this->coreHelper->getObjectByType($this->container->getParameter('app.type.tools'),
            $skillsLocationId,
            $skillContentTypeIdentifier);
        $params['skills'] = $this->coreHelper->getObjectByType($this->container->getParameter('app.type.skill'),
            $skillsLocationId,
            $skillContentTypeIdentifier);

        $params['educations'] = $this->coreHelper->getObjectByType($this->container->getParameter('app.type.education'),
            $xpLocationId,
            $xpContentTypeIdentifier);
        $params['xps'] = $this->coreHelper->getObjectByType($this->container->getParameter('app.type.work'),
            $xpLocationId,
            $xpContentTypeIdentifier);


        $response = $this->get('ez_content')->viewLocation(
            $locationId,
            $viewType,
            $layout,
            $params
        );

        $response->headers->set('X-Location-Id', $locationId);
        $response->setEtag(md5(json_encode($params)));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('app.cache.high.ttl'));

        return $response;
    }
}
