<?php

/**
 * Manage Surveys
 *
 * @package     Nails
 * @subpackage  module-survey
 * @category    Model
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Survey\Model;

use Nails\Common\Exception\ModelException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Model\Base;
use Nails\Common\Service\Database;
use Nails\Common\Service\FormValidation;
use Nails\Common\Traits\Model\Copyable;
use Nails\Factory;
use Nails\FormBuilder;
use Nails\Survey\Constants;
use Nails\Survey\Model\Response\Answer;

/**
 * Class Survey
 *
 * @package Nails\Survey\Model
 */
class Survey extends Base
{
    use Copyable {
        copy as traitCopy;
    }

    // --------------------------------------------------------------------------

    /**
     * The table this model represents
     *
     * @var string
     */
    const TABLE = NAILS_DB_PREFIX . 'survey_survey';

    /**
     * The name of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_NAME = 'Survey';

    /**
     * The provider of the resource to use (as passed to \Nails\Factory::resource())
     *
     * @var string
     */
    const RESOURCE_PROVIDER = Constants::MODULE_SLUG;

    /**
     * Whether this model uses destructive delete or not
     *
     * @var bool
     */
    const DESTRUCTIVE_DELETE = false;

    /**
     * Whether to automatically set tokens or not
     *
     * @var bool
     */
    const AUTO_SET_TOKEN = true;

    // --------------------------------------------------------------------------

    /**
     * Survey constructor.
     *
     * @throws ModelException
     */
    public function __construct()
    {
        parent::__construct();

        /** @var \Nails\Survey\Model\Response $oResponseModel */
        $oResponseModel = Factory::model('Response', Constants::MODULE_SLUG);

        $this
            ->hasOne('form', 'Form', FormBuilder\Constants::MODULE_SLUG)
            ->hasMany('responses', 'Response', 'survey_id', Constants::MODULE_SLUG)
            ->hasMany('responses_submitted', 'Response', 'survey_id', Constants::MODULE_SLUG, [
                'where' => [
                    ['status', $oResponseModel::STATUS_SUBMITTED],
                ],
            ])
            ->hasMany('responses_open', 'Response', 'survey_id', Constants::MODULE_SLUG, [
                'where' => [
                    ['status', $oResponseModel::STATUS_OPEN],
                ],
            ]);
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function describeFields($sTable = null)
    {
        $aFields = parent::describeFields($sTable);

        $aRules = [
            FormValidation::RULE_REQUIRED     => [
                'label',
            ],
            FormValidation::RULE_VALID_EMAILS => [
                'notification_email',
            ],
        ];

        foreach ($aRules as $sRule => $aColumns) {
            foreach ($aColumns as $sColumn) {
                $aFields[$sColumn]->validation[] = $sRule;
            }
        }

        return $aFields;
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    protected function prepareWriteData(array &$aData): Base
    {
        //  Ensure access tokens aren't updated
        unset($aData['token_stats']);

        return parent::prepareWriteData($aData);
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    protected function prepareCreateData(array &$aData): Base
    {
        //  Generate additional token for stats
        $aData['token_stats'] = $this->generateToken();

        return parent::prepareCreateData($aData);
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a new copy of an existing survey
     *
     * @param integer $iSurveyId     The ID of the survey to duplicate
     * @param boolean $bReturnObject Whether to return the entire new survey object, or just the ID
     * @param array   $aReturnData   An array to pass to the getById() call when $bReturnObject is true
     *
     * @return int|\Nails\Survey\Resource\Survey
     */
    public function copy($iSurveyId, $bReturnObject = false, $aReturnData = [])
    {
        /** @var Database $oDb */
        $oDb = Factory::service('Database');
        /** @var FormBuilder\Model\Form $oFormModel */
        $oFormModel = Factory::model('Form', FormBuilder\Constants::MODULE_SLUG);

        /** @var \Nails\Survey\Resource\Survey $oSurvey */
        $oSurvey = $this->getById($iSurveyId);
        if (empty($oSurvey)) {
            throw new NailsException('Not a valid survey ID.');
        }

        try {

            $oDb->transaction()->start();

            $iNewSurveyId = $this->traitCopy($iSurveyId);
            $iNewFormId   = $oFormModel->copy($oSurvey->form_id);
            if (empty($iNewFormId)) {
                throw new NailsException('Failed to copy the survey\'s form. ' . $oFormModel->lastError());
            }

            $this->update($iNewSurveyId, ['form_id' => $iNewFormId]);

            $oDb->transaction()->commit();

            return $bReturnObject
                ? $this->getById($iNewSurveyId, $aReturnData)
                : $iNewSurveyId;

        } catch (\Exception $e) {
            $oDb->transaction()->rollback();
            throw $e;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Generates aggregated stats for all the responses for a particular survey
     *
     * @param integer $iSurveyId The ID of the survey
     *
     * @return array
     */
    public function getStats($iSurveyId)
    {
        $oSurvey = $this->getById($iSurveyId, ['expand' => ['form']]);
        if (empty($oSurvey)) {
            return false;
        }

        /** @var Answer $oResponseAnswerModel */
        $oResponseAnswerModel = Factory::model('ResponseAnswer', Constants::MODULE_SLUG);
        $aOut                 = [];

        //  Generate stats for each field
        foreach ($oSurvey->form->fields->data as $oField) {

            //  Get responses which apply to this field
            $aResponses = $oResponseAnswerModel->getAll([
                'expand' => ['option'],
                'where'  => [
                    ['form_field_id', $oField->id],
                ],
            ]);

            $aOut[] = (object) [
                'id'    => $oField->id,
                'label' => $oField->label,
                'data'  => [],
            ];
        }

        return $aOut;
    }
}
