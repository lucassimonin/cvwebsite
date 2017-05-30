<?php

namespace App\Bundle\SiteBundle\Helper;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query;
use Monolog\Logger;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\SearchService;

/**
 * CoreHelper Class
 * @author simoninl
 */
class CoreHelper
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var CriteriaHelper */
    private $criteriaHelper;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \Monolog\Logger */
    private $logger;

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
    public function getObjectByType(string $category, string $locationId, string $contentTypeIdentifier, $sortField = null, $sortDirection = null) : array
    {
        $fieldsData = ['attribute' => 'type', 'operator' => Operator::EQ, 'value' => $category];
        // Initialize latestNews
        $latestObjects = [];
        $sortClauses = array();
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
        // Building latest News tab
        if (isset($searchResultLatestObjects->searchHits)) {
            foreach ($searchResultLatestObjects->searchHits as $hit) {
                array_push($latestObjects, $hit->valueObject);
            }
        }

        return $latestObjects;
    }

    /**
     *
     * @param array      $contentType
     * @param string $locationId
     * @return array
     */
    public function getChildrenObject(array $contentType, $locationId) : array
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
