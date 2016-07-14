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

use Nails\Factory;
use Nails\Common\Model\Base;

class Survey extends Base
{
    /**
     * Construct the model
     */
    public function __construct()
    {
        parent::__construct();

        $this->table             = NAILS_DB_PREFIX . 'survey_survey';
        $this->tablePrefix       = 's';
        $this->destructiveDelete = false;
    }

    // --------------------------------------------------------------------------

    public function getAll($iPage = null, $iPerPage = null, $aData = array(), $bIncludeDeleted = false)
    {
        $aItems = parent::getAll($iPage, $iPerPage, $aData, $bIncludeDeleted);

        if (!empty($aItems)) {

            if (!empty($aData['includeAll']) || !empty($aData['includeForm'])) {
                $this->getSingleAssociatedItem(
                    $aItems,
                    'form_id',
                    'form',
                    'Form',
                    'nailsapp/module-form-builder',
                    array(
                        'includeFields' => true
                    )
                );
            }

            if (!empty($aData['includeAll']) || !empty($aData['includeResponses'])) {
                $this->getManyAssociatedItems(
                    $aItems,
                    'responses',
                    'survey_id',
                    'Response',
                    'nailsapp/module-survey',
                    array(
                        'includeUser' => true
                    )
                );

                // Loop through them and add a second `count` field, that of actually submitted responses
                $oResponseModel = Factory::model('Response', 'nailsapp/module-survey');
                foreach ($aItems as $oItem) {
                    $iCounter = 0;
                    foreach ($oItem->responses->data as $oResponse) {
                        if ($oResponse->status == $oResponseModel::STATUS_SUBMITTED) {
                            $iCounter++;
                        }
                    }
                    $oItem->responses->count_submitted = $iCounter;
                }
            }
        }

        return $aItems;
    }

    // --------------------------------------------------------------------------

    public function create($aData = array(), $bReturnObject = false)
    {
        //  Generate an access token
        Factory::helper('string');
        $aData['access_token'] = generateToken();

        //  Extract the form
        $aForm = array_key_exists('form', $aData) ? $aData['form'] : null;
        unset($aData['form']);

        try {

            $oDb = Factory::service('Database');

            $oDb->trans_begin();

            //  Create the associated form (if no ID supplied)
            if (empty($aForm['id'])) {

                $oFormModel       = Factory::model('Form', 'nailsapp/module-form-builder');
                $aData['form_id'] = $oFormModel->create($aForm);

                if (!$aData['form_id']) {
                    throw new \Exception('Failed to create associated form.', 1);
                }

            } else {

                $aData['form_id'] = $aForm['id'];
            }

            $mResult = parent::create($aData, $bReturnObject);

            if (!$mResult) {
                throw new \Exception('Failed to create survey. ' . $this->lastError(), 1);
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

    public function update($iId, $aData = array())
    {
        //  Ensure access tokens aren't updated
        unset($aData['access_token']);

        //  Extract the form
        $aForm = array_key_exists('form', $aData) ? $aData['form'] : null;
        unset($aData['form']);

        try {

            $oDb = Factory::service('Database');

            $oDb->trans_begin();

            //  Update the associated form (if no ID supplied)
            if (!empty($aForm['id'])) {

                $oFormModel = Factory::model('Form', 'nailsapp/module-form-builder');

                if (!$oFormModel->update($aForm['id'], $aForm)) {
                    throw new \Exception('Failed to update associated form.', 1);
                }
            }

            if (!parent::update($iId, $aData)) {
                throw new \Exception('Failed to update form. ' . $this->lastError(), 1);
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
     * @param  integer $iSurveyId    The ID of the survey to duplicate
     * @param  boolean $bReturnObject Whether to return the entire new survey object, or just the ID
     * @param  array   $aReturnData   An array to pass to the getById() call when $bReturnObject is true
     * @return mixed
     */
    public function copy($iSurveyId, $bReturnObject = false, $aReturnData = array())
    {
        try {

            //  Begin the transaction
            $oDb = Factory::service('Database');
            $oDb->trans_begin();

            //  Check survey exists
            $oSurvey = $this->getById($iSurveyId);
            if (empty($oSurvey)) {
                throw new \Exception('Not a valid survey ID.', 1);
            }

            //  Copy the form
            $oFormModel = Factory::model('Form', 'nailsapp/module-form-builder');
            $iNewFormId = $oFormModel->copy($oSurvey->form_id);

            if (empty($iNewFormId)) {
                throw new \Exception('Failed to copy the survey\'s form. ' . $oFormModel->lastError(), 1);
            }

            $sTableSurvey = $this->getTableName();

            //  Duplicate the survey
            $oDb->where('id', $iSurveyId);
            $oSurveyRow = $oDb->get($sTableSurvey)->row();

            $oNow = Factory::factory('DateTime');
            $sNow = $oNow->format('Y-m-d H:i:s');

            Factory::helper('string');

            unset($oSurveyRow->id);
            $oSurveyRow->form_id      = $iNewFormId;
            $oSurveyRow->access_token = generateToken();
            $oSurveyRow->label        = $oSurveyRow->label . ' - copy';
            $oSurveyRow->created      = $sNow;
            $oSurveyRow->created_by   = activeUser('id') ?: null;
            $oSurveyRow->modified     = $sNow;
            $oSurveyRow->modified_by  = activeUser('id') ?: null;

            $oDb->set($oSurveyRow);
            if (!$oDb->insert($sTableSurvey)) {
                throw new \Exception('Failed to copy parent form record.', 1);
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
     * @param  integer $iSurveyId The ID of the survey
     * @return array
     */
    public function getStats($iSurveyId)
    {
        $oSurvey = $this->getById(
            $iSurveyId,
            array(
                'includeForm' => true
            )
        );

        if (empty($oSurvey)) {
            return false;
        }

        $oResponseAnswerModel = Factory::model('ResponseAnswer', 'nailsapp/module-survey');
        $aOut                 = array();

        //  Generate stats for each field
        foreach ($oSurvey->form->fields->data as $oField) {

            //  Get responses which apply to this field
            $aResponses = $oResponseAnswerModel->getAll(
                null,
                null,
                array(
                    'includeOption' => true,
                    'where' => array(
                        array('form_field_id', $oField->id)
                    )
                )
            );


            $aOut[] = (object) array(
                'id'    => $oField->id,
                'label' => $oField->label,
                'data'  => array()
            );
        }

        return $aOut;
    }

    // --------------------------------------------------------------------------

    protected function formatObject(
        &$oObj,
        $aData = array(),
        $aIntegers = array(),
        $aBools = array(),
        $aFloats = array()
    ) {
        $aBools[] = 'thankyou_email';
        $aBools[] = 'is_active';
        $aBools[] = 'is_minimal';

        parent::formatObject($oObj, $aData, $aIntegers, $aBools, $aFloats);

        // --------------------------------------------------------------------------

        $oObj->url = site_url('survey/' . $oObj->id . '/' . $oObj->access_token);

        // --------------------------------------------------------------------------

        $oObj->header             = json_decode($oObj->header);
        $oObj->footer             = json_decode($oObj->footer);
        $oObj->notification_email = json_decode($oObj->notification_email);

        // --------------------------------------------------------------------------

        $oObj->cta             = new \stdClass();
        $oObj->cta->label      = $oObj->cta_label;
        $oObj->cta->attributes = $oObj->cta_attributes;

        unset($oObj->cta_label);
        unset($oObj->cta_attributes);

        // --------------------------------------------------------------------------

        $bSendThankYouEmail = $oObj->thankyou_email;

        $oObj->thankyou_email          = new \stdClass();
        $oObj->thankyou_email->send    = $bSendThankYouEmail;
        $oObj->thankyou_email->subject = $oObj->thankyou_email_subject;
        $oObj->thankyou_email->body    = $oObj->thankyou_email_body;

        unset($oObj->thankyou_email_subject);
        unset($oObj->thankyou_email_body);

        // --------------------------------------------------------------------------

        $oObj->thankyou_page        = new \stdClass();
        $oObj->thankyou_page->title = $oObj->thankyou_page_title;
        $oObj->thankyou_page->body  = json_decode($oObj->thankyou_page_body);

        unset($oObj->thankyou_page_title);
        unset($oObj->thankyou_page_body);
    }
}
