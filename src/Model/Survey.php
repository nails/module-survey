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

use Nails\Common\Exception\NailsException;
use Nails\Common\Model\Base;
use Nails\Factory;

class Survey extends Base
{
    /**
     * Construct the model
     */
    public function __construct()
    {
        parent::__construct();
        $this->table             = NAILS_DB_PREFIX . 'survey_survey';
        $this->destructiveDelete = false;
        $this->addExpandableField([
            'trigger'   => 'form',
            'type'      => self::EXPANDABLE_TYPE_SINGLE,
            'property'  => 'form',
            'model'     => 'Form',
            'provider'  => 'nails/module-form-builder',
            'id_column' => 'form_id',
        ]);
        $this->addExpandableField([
            'trigger'   => 'responses',
            'type'      => self::EXPANDABLE_TYPE_MANY,
            'property'  => 'responses',
            'model'     => 'Response',
            'provider'  => 'nails/module-survey',
            'id_column' => 'survey_id',
        ]);
    }

    // --------------------------------------------------------------------------

    public function create(array $aData = [], $bReturnObject = false)
    {
        //  Generate access tokens
        Factory::helper('string');
        $aData['access_token']       = generateToken();
        $aData['access_token_stats'] = generateToken();

        //  Extract the form
        $aForm = array_key_exists('form', $aData) ? $aData['form'] : null;
        unset($aData['form']);

        try {

            $oDb = Factory::service('Database');
            $oDb->trans_begin();

            //  Create the associated form (if no ID supplied)
            if (empty($aForm['id'])) {

                $oFormModel       = Factory::model('Form', 'nails/module-form-builder');
                $aData['form_id'] = $oFormModel->create($aForm);

                if (!$aData['form_id']) {
                    throw new NailsException('Failed to create associated form.', 1);
                }

            } else {
                $aData['form_id'] = $aForm['id'];
            }

            $mResult = parent::create($aData, $bReturnObject);

            if (!$mResult) {
                throw new NailsException('Failed to create survey. ' . $this->lastError(), 1);
            }

            $oDb->trans_commit();
            return $mResult;

        } catch (\Exception $e) {
            $oDb->trans_rollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    // --------------------------------------------------------------------------

    public function update($iId, array $aData = [])
    {
        //  Ensure access tokens aren't updated
        unset($aData['access_token']);
        unset($aData['access_token_stats']);

        //  Extract the form
        $aForm = array_key_exists('form', $aData) ? $aData['form'] : null;
        unset($aData['form']);

        try {

            $oDb = Factory::service('Database');
            $oDb->trans_begin();

            //  Update the associated form (if no ID supplied)
            if (!empty($aForm['id'])) {

                $oFormModel = Factory::model('Form', 'nails/module-form-builder');

                if (!$oFormModel->update($aForm['id'], $aForm)) {
                    throw new NailsException('Failed to update associated form.', 1);
                }
            }

            if (!parent::update($iId, $aData)) {
                throw new NailsException('Failed to update form. ' . $this->lastError(), 1);
            }

            $oDb->trans_commit();
            return true;

        } catch (\Exception $e) {
            $oDb->trans_rollback();
            $this->setError($e->getMessage());
            return false;
        }

        return parent::update($iId, $aData);
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a new copy of an existing survey
     *
     * @param  integer $iSurveyId     The ID of the survey to duplicate
     * @param  boolean $bReturnObject Whether to return the entire new survey object, or just the ID
     * @param  array   $aReturnData   An array to pass to the getById() call when $bReturnObject is true
     *
     * @return mixed
     */
    public function copy($iSurveyId, $bReturnObject = false, $aReturnData = [])
    {
        try {

            //  Begin the transaction
            $oDb = Factory::service('Database');
            $oDb->trans_begin();

            //  Check survey exists
            $oSurvey = $this->getById($iSurveyId);
            if (empty($oSurvey)) {
                throw new NailsException('Not a valid survey ID.', 1);
            }

            //  Copy the form
            $oFormModel = Factory::model('Form', 'nails/module-form-builder');
            $iNewFormId = $oFormModel->copy($oSurvey->form_id);

            if (empty($iNewFormId)) {
                throw new NailsException('Failed to copy the survey\'s form. ' . $oFormModel->lastError(), 1);
            }

            $sTableSurvey = $this->getTableName();

            //  Duplicate the survey
            $oDb->where('id', $iSurveyId);
            $oSurveyRow = $oDb->get($sTableSurvey)->row();

            $oNow = Factory::factory('DateTime');
            $sNow = $oNow->format('Y-m-d H:i:s');

            Factory::helper('string');

            unset($oSurveyRow->id);
            $oSurveyRow->form_id            = $iNewFormId;
            $oSurveyRow->access_token       = generateToken();
            $oSurveyRow->access_token_stats = generateToken();
            $oSurveyRow->label              = $oSurveyRow->label . ' - copy';
            $oSurveyRow->created            = $sNow;
            $oSurveyRow->created_by         = activeUser('id') ?: null;
            $oSurveyRow->modified           = $sNow;
            $oSurveyRow->modified_by        = activeUser('id') ?: null;

            $oDb->set($oSurveyRow);
            if (!$oDb->insert($sTableSurvey)) {
                throw new NailsException('Failed to copy parent form record.', 1);
            }

            $iNewSurveyId = $oDb->insert_id();

            //  All done
            $oDb->trans_commit();

            //  Return the new form's ID or object
            return $bReturnObject ? $this->getById($iNewSurveyId, $aReturnData) : $iNewSurveyId;

        } catch (\Exception $e) {
            $oDb->trans_rollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Generates aggregated stats for all the responses for a particular survey
     *
     * @param  integer $iSurveyId The ID of the survey
     *
     * @return array
     */
    public function getStats($iSurveyId)
    {
        $oSurvey = $this->getById($iSurveyId, ['expand' => ['form']]);
        if (empty($oSurvey)) {
            return false;
        }

        $oResponseAnswerModel = Factory::model('ResponseAnswer', 'nails/module-survey');
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

    // --------------------------------------------------------------------------

    protected function formatObject(
        &$oObj,
        array $aData = [],
        array $aIntegers = [],
        array $aBools = [],
        array $aFloats = []
    ) {
        $aBools[] = 'thankyou_email';
        $aBools[] = 'allow_anonymous_response';
        $aBools[] = 'allow_public_stats';
        $aBools[] = 'is_active';
        $aBools[] = 'is_minimal';

        parent::formatObject($oObj, $aData, $aIntegers, $aBools, $aFloats);

        // --------------------------------------------------------------------------

        $oObj->url       = site_url('survey/' . $oObj->id . '/' . $oObj->access_token);
        $oObj->url_stats = site_url('survey/stats/' . $oObj->id . '/' . $oObj->access_token_stats);

        // --------------------------------------------------------------------------

        $oObj->header             = json_decode($oObj->header);
        $oObj->footer             = json_decode($oObj->footer);
        $oObj->notification_email = json_decode($oObj->notification_email);
        $oObj->stats_header       = json_decode($oObj->stats_header);
        $oObj->stats_footer       = json_decode($oObj->stats_footer);

        // --------------------------------------------------------------------------

        $oObj->cta = (object) [
            'label'      => $oObj->cta_label,
            'attributes' => $oObj->cta_attributes,
        ];

        unset($oObj->cta_label);
        unset($oObj->cta_attributes);

        // --------------------------------------------------------------------------

        $bSendThankYouEmail   = $oObj->thankyou_email;
        $oObj->thankyou_email = (object) [
            'send'    => $bSendThankYouEmail,
            'subject' => $oObj->thankyou_email_subject,
            'body'    => $oObj->thankyou_email_body,
        ];

        unset($oObj->thankyou_email_subject);
        unset($oObj->thankyou_email_body);

        // --------------------------------------------------------------------------

        $oObj->thankyou_page = (object) [
            'title' => $oObj->thankyou_page_title,
            'body'  => json_decode($oObj->thankyou_page_body),
        ];

        unset($oObj->thankyou_page_title);
        unset($oObj->thankyou_page_body);

        // --------------------------------------------------------------------------

        if (!empty($oObj->responses)) {
            $oResponseModel                   = Factory::model('Response', 'nails/module-survey');
            $oObj->responses->count_submitted = 0;
            foreach ($oObj->responses->data as $oResponse) {
                if ($oResponse->status === $oResponseModel::STATUS_SUBMITTED) {
                    $oObj->responses->count_submitted++;
                }
            }
        }
    }
}
