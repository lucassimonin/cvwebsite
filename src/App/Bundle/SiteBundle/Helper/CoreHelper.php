<?php

namespace App\Bundle\SiteBundle\Helper;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query;
use Monolog\Logger;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * CoreHelper Class
 *
 * This class is used to persist data
 * Class to eZ Publish 5 using only the new eZ Publish API and not legacy.
 *
 * @author simoninl
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class CoreHelper
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    protected $contentEntityManager;

    /** @var CriteriaHelper */
    protected $criteriaHelper;

    /** @var \eZ\Publish\API\Repository\SearchService */
    protected $searchService;

    /** @var \Monolog\Logger */
    protected $logger;

    /**
     * CoreHelper constructor.
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \App\Bundle\SiteBundle\Helper\CriteriaHelper $criteraHelper
     * @param \Monolog\Logger $logger
     */
    public function __construct(Repository $repository, CriteriaHelper $criteraHelper, Logger $logger)
    {
        $this->repository = $repository;
        $this->criteriaHelper = $criteraHelper;
        $this->searchService = $this->repository->getSearchService();
        $this->logger = $logger;
    }

    /**
     * Get article
     * @param string $category
     * @param int    $locationId
     * @param sting  $contentTypeIdentifier
     * @param string $sortField
     * @param string $sortDirection
     * @return array
     */
    public function getObjectByType($category, $locationId, $contentTypeIdentifier, $sortField = null, $sortDirection = null)
    {
        $fieldsData = ['attribute' => 'type', 'operator' => Operator::EQ, 'value' => $category];

        // Initialize latestNews
        $latestObjects = [];
        $sortClauses = array(

        );
        if($sortField != null && $sortDirection != null) {
            $sortClauses[] = new Field($contentTypeIdentifier, $sortField, $sortDirection);
        } else {
            $sortClauses[] =new SortClause\DatePublished(Query::SORT_DESC);
        }


        // Try loading all article under loaded location (listing news)
        try {
            // Generate criteria to get all article under authors listing news class
            $criteriaLatestObjects = $this->criteriaHelper->generateContentCriterionByParentLocationIdAndContentIdentifiersAndFieldsData($locationId, [$contentTypeIdentifier], [$fieldsData]);

            // Building Query
            $queryLatestObjects = new Query();
            $queryLatestObjects->filter = $criteriaLatestObjects;
            $queryLatestObjects->sortClauses = $sortClauses;

            // Getting results
            $searchResultLatestObjects = $this->repository->sudo(
                function() use ($queryLatestObjects) {
                    return $this->searchService->findContent($queryLatestObjects);
                }
            );



        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->logger->critical($e->getCode());
            $this->logger->critical($e->getFile());
            $this->logger->critical($e->getLine());
            exit("error");
        }
        //var_dump($searchResultLatestNews);die;
        // Building latest News tab
        if (isset($searchResultLatestObjects->searchHits)) {
            foreach ($searchResultLatestObjects->searchHits as $hit) {
                array_push($latestObjects, $hit->valueObject);
            }
        }


        return $latestObjects;
    }

    /**
     * Get repository
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Get Search services
     * @return \eZ\Publish\API\Repository\SearchService
     */
    public function getSearchService()
    {
        return $this->searchService;
    }

    /**
     * Get criteria helper
     * @return CriteriaHelper
     */
    public function getCriteriaHelper()
    {
        return $this->criteriaHelper;
    }


    /**
     *
     * @param array      $contentType
     * @param string|int $locationId
     * @return array
     */
    public function getChildrenObject($contentType, $locationId)
    {
        $criteria = $this->criteriaHelper->generateContentCriterionByParentLocationIdAndContentIdentifiersAndFieldsData($locationId, $contentType);
        $query = new Query();
        $query->filter = $criteria;
        $searchResult = $this->repository->sudo(
            function() use ($query) {
                return $this->searchService->findContent($query);
            }
        );
        $childrensObject = array();
        if (isset($searchResult->searchHits)) {
            foreach ($searchResult->searchHits as $hit) {
                array_push($childrensObject, $hit->valueObject);
            }
        }

        return $childrensObject;
    }

}
