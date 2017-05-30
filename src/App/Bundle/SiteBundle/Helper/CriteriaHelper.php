<?php
namespace App\Bundle\SiteBundle\Helper;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * CriteriaHelper Class
 *
 * @author simoninl
 */
class CriteriaHelper
{
    /**
     * Generate criterion list to be used to list sub folder items
     *
     * @param int      $parentLocationId       Location of the folder
     * @param string[] $contentTypeIdentifiers Array of included contentType identifiers
     * @param string[] $fieldsData             Array of fields array(array(value =>'', attribute => '', operator => ''), array())
     * @param string[] $relationList           Array of fields array(array(value =>'', attribute => '', operator => ''), array())
     * @param string[] $tagsList               Array of fields array(array(value =>'', attribute => '', operator => ''), array())
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public function generateContentCriterionByParentLocationIdAndContentIdentifiersAndFieldsData(int $parentLocationId, array $contentTypeIdentifiers = [], array $fieldsData = [], array $relationList = [], array $tagsList = []) : Criterion
    {

        $criteria = [];
        $criteria[] = new Criterion\Visibility(Criterion\Visibility::VISIBLE);
        $criteria[] = new Criterion\ContentTypeIdentifier($contentTypeIdentifiers);
        $criteria[] = new Criterion\ParentLocationId($parentLocationId);

        $fieldSearchCriterion = array();
        if (!empty($fieldsData)) {
            for ($cpt = 0; $cpt < sizeof($fieldsData); $cpt++) {
                if (isset($fieldsData[$cpt]['attribute']) && isset($fieldsData[$cpt]['operator']) && isset($fieldsData[$cpt]['value'])) {
                    $fieldSearchCriterion[] = new Criterion\Field(
                        $fieldsData[$cpt]['attribute'],
                        $fieldsData[$cpt]['operator'],
                        $fieldsData[$cpt]['value']
                    );
                }
            }
            if (sizeof($fieldSearchCriterion) > 0) {
                $tmpCriteria = new Criterion\LogicalAnd($fieldSearchCriterion);
                $criteria[] = $tmpCriteria;
            }
        }

        $relationListSearchCriterion = array();
        if (!empty($relationList)) {
            for ($cpt = 0; $cpt < sizeof($relationList); $cpt++) {
                $relationListSearchCriterion[] = new Criterion\FieldRelation(
                    $relationList[$cpt]['attribute'],
                    $relationList[$cpt]['operator'],
                    $relationList[$cpt]['value']
                );
            }
            if (sizeof($relationListSearchCriterion) > 0) {
                $tmpCriteria = new Criterion\LogicalAnd($relationListSearchCriterion);
                $criteria[] = $tmpCriteria;
            }
        }

        return new Criterion\LogicalAnd($criteria);
    }
}
