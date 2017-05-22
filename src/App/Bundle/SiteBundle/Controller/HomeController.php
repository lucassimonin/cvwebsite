<?php

namespace App\Bundle\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * HomeController Class.
 *
 * @author simoninl
 */
class HomeController extends Controller
{
    /**
     * Homepage action
     *
     * @param View $view
     * @return View
     */
    public function indexAction(View $view) : View
    {
        $response = new Response();
        $response->setPrivate();
        $response->setSharedMaxAge($this->container->getParameter('app.cache.high.ttl'));
        $response->setLastModified($view->getContent()->versionInfo->modificationDate);
        $view->setResponse($response);

        return $view;
    }

    /**
     * Part experiences
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function experiencesAction() : Response
    {
        $this->coreHelper = $this->container->get('app.core_helper');
        $xpContentTypeIdentifier = $this->container->getParameter('app.experience.content_type.identifier');
        $xpLocationId = $this->container->getParameter('app.xp.locationid');
        $params['educations'] = $this->coreHelper->getObjectByType($this->container->getParameter('app.type.education'),
            $xpLocationId,
            $xpContentTypeIdentifier);
        $params['xps'] = $this->coreHelper->getObjectByType($this->container->getParameter('app.type.work'),
            $xpLocationId,
            $xpContentTypeIdentifier);

        return $this->render(
            'parts/experiences.html.twig',
            array('params' => $params)
        );

    }

    /**
     * Part skills
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function skillsAction() : Response
    {
        $this->coreHelper = $this->container->get('app.core_helper');
        $skillContentTypeIdentifier = $this->container->getParameter('app.skill.content_type.identifier');
        $skillsLocationId = $this->container->getParameter('app.skills.locationid');
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

        return $this->render(
            'parts/skills.html.twig',
            array('params' => $params)
        );

    }

    /**
     * Part works
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function worksAction() : Response
    {
        $this->coreHelper = $this->container->get('app.core_helper');
        $worksItemContentTypeIdentifier = $this->container->getParameter('app.work.content_type.identifier');
        $worksLocationId = $this->container->getParameter('app.works.locationid');
        $params['works'] = $this->coreHelper->getChildrenObject([$worksItemContentTypeIdentifier], $worksLocationId);

        return $this->render(
            'parts/works.html.twig',
            array('params' => $params)
        );
    }
}