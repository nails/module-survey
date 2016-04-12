<?php

/**
 * Manage Surveys
 *
 * @package     module-survey
 * @subpackage  Admin
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Survey;

use Nails\Factory;
use Nails\Admin\Helper;
use Nails\Survey\Controller\BaseAdmin;

class Survey extends BaseAdmin
{
    /**
     * Announces this controller's navGroups
     * @return stdClass
     */
    public static function announce()
    {
        if (userHasPermission('admin:survey:survey:browse')) {

            $oNavGroup = Factory::factory('Nav', 'nailsapp/module-admin');
            $oNavGroup->setLabel('Surveys');
            $oNavGroup->setIcon('fa-list-alt');
            $oNavGroup->addAction('Browse Surveys');
            return $oNavGroup;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of extra permissions for this controller
     * @return array
     */
    public static function permissions()
    {
        $permissions = parent::permissions();

        $permissions['browse']          = 'Can browse surveys';
        $permissions['create']          = 'Can create surveys';
        $permissions['edit']            = 'Can edit surveys';
        $permissions['delete']          = 'Can delete surveys';
        $permissions['stats']           = 'Can view survey stats';
        $permissions['response']        = 'Can view responses';
        $permissions['response:delete'] = 'Can delete responses';

        return $permissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Browse existing survey
     * @return void
     */
    public function index()
    {
        if (!userHasPermission('admin:survey:survey:browse')) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = 'Browse Surveys';

        // --------------------------------------------------------------------------

        //  Get pagination and search/sort variables
        $tablePrefix = $oSurveyModel->getTablePrefix();
        $page        = $this->input->get('page')      ? $this->input->get('page')      : 0;
        $perPage     = $this->input->get('perPage')   ? $this->input->get('perPage')   : 50;
        $sortOn      = $this->input->get('sortOn')    ? $this->input->get('sortOn')    : $tablePrefix . '.label';
        $sortOrder   = $this->input->get('sortOrder') ? $this->input->get('sortOrder') : 'asc';
        $keywords    = $this->input->get('keywords')  ? $this->input->get('keywords')  : '';

        // --------------------------------------------------------------------------

        //  Define the sortable columns
        $sortColumns = array(
            $tablePrefix . '.id'       => 'Survey ID',
            $tablePrefix . '.label'    => 'Label',
            $tablePrefix . '.modified' => 'Modified Date'
        );

        // --------------------------------------------------------------------------

        //  Define the $data variable for the queries
        $data = array(
            'includeResponses' => true,
            'sort' => array(
                array($sortOn, $sortOrder)
            ),
            'keywords' => $keywords
        );

        //  Get the items for the page
        $totalRows             = $oSurveyModel->countAll($data);
        $this->data['surveys'] = $oSurveyModel->getAll($page, $perPage, $data);

        //  Set Search and Pagination objects for the view
        $this->data['search']     = Helper::searchObject(true, $sortColumns, $sortOn, $sortOrder, $perPage, $keywords);
        $this->data['pagination'] = Helper::paginationObject($page, $perPage, $totalRows);

        //  Add a header button
        if (userHasPermission('admin:survey:survey:create')) {
             Helper::addHeaderButton('admin/survey/survey/create', 'Create Survey');
        }

        // --------------------------------------------------------------------------

        Helper::loadView('index');
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new Survey
     * @return void
     */
    public function create()
    {
        if (!userHasPermission('admin:survey:survey:create')) {
            unauthorised();
        }

        $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');

        if ($this->input->post()) {
            if ($this->runFormValidation()) {
                if ($oSurveyModel->create($this->getPostObject())) {

                    $oSession = Factory::model('Session', 'nailsapp/module-auth');
                    $oSession->set_flashdata('success', 'Survey created successfully.');
                    redirect('admin/survey/survey');

                } else {

                    $this->data['error'] = 'Failed to create survey.' . $oSurveyModel->lastError();
                }

            } else {

                $this->data['error'] = lang('fv_there_were_errors');
            }
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Create Survey';
        $this->loadViewData();
        Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Edit an existing Survey
     * @return void
     */
    public function edit()
    {
        if (!userHasPermission('admin:survey:survey:edit')) {
            unauthorised();
        }

        $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');

        $iSurveyId = (int) $this->uri->segment(5);
        $this->data['survey'] = $oSurveyModel->getById(
            $iSurveyId,
            array(
                'includeFields'    => true,
                'includeResponses' => true
            )
        );

        if (empty($this->data['survey']) || $this->data['survey']->responses->count > 0) {
            show_404();
        }

        $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');

        if ($this->input->post()) {
            if ($this->runFormValidation()) {
                if ($oSurveyModel->update($iSurveyId, $this->getPostObject())) {

                    $oSession = Factory::model('Session', 'nailsapp/module-auth');
                    $oSession->set_flashdata('success', 'Survey updated successfully.');
                    redirect('admin/survey/survey');

                } else {

                    $this->data['error'] = 'Failed to update survey.' . $oSurveyModel->lastError();
                }

            } else {

                $this->data['error'] = lang('fv_there_were_errors');
            }
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Edit Survey';
        $this->loadViewData();
        Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    protected function loadViewData()
    {
        $oAsset = Factory::service('Asset');
        $oAsset->load('admin.survey.edit.min.js', 'nailsapp/module-survey');
        $oAsset->inline('var _admin_survey_edit = new _ADMIN_SURVEY_EDIT();', 'JS');
    }

    // --------------------------------------------------------------------------

    protected function runFormValidation()
    {
        $oFormValidation = Factory::model('FormValidation');

        //  Define the rules
        $aRules = array(
            'label'                  => 'xss_clean|required',
            'header'                 => '',
            'footer'                 => '',
            'cta_label'              => 'xss_clean',
            'cta_attributes'         => 'xss_clean',
            'survey_attributes'        => 'xss_clean',
            'has_captcha'            => '',
            'fields'                 => 'required',
            'notification_email'     => 'valid_emails',
            'thankyou_email'         => '',
            'thankyou_email_subject' => 'xss_clean',
            'thankyou_email_body'    => 'xss_clean',
            'thankyou_page_title'    => 'xss_clean|required',
            'thankyou_page_body'     => '',
        );

        foreach ($aRules as $sKey => $sRules) {
            $oFormValidation->set_rules($sKey, '', $sRules);
        }

        $oFormValidation->set_message('required', lang('fv_required'));
        $oFormValidation->set_message('valid_emails', lang('fv_valid_emails'));

        return $oFormValidation->run();
    }

    // --------------------------------------------------------------------------

    protected function getPostObject()
    {
        $aData = array(
            'label'                  => $this->input->post('label'),
            'header'                 => $this->input->post('header'),
            'footer'                 => $this->input->post('footer'),
            'cta_label'              => $this->input->post('cta_label'),
            'cta_attributes'         => $this->input->post('cta_attributes'),
            'form_attributes'        => $this->input->post('form_attributes'),
            'has_captcha'            => (bool) $this->input->post('has_captcha'),
            'thankyou_email'         => (bool) $this->input->post('thankyou_email'),
            'thankyou_email_subject' => $this->input->post('thankyou_email_subject'),
            'thankyou_email_body'    => $this->input->post('thankyou_email_body'),
            'thankyou_page_title'    => $this->input->post('thankyou_page_title'),
            'thankyou_page_body'     => $this->input->post('thankyou_page_body'),
            'fields'                 => array()
        );

        //  Build up fields
        $iFieldOrder = 0;
        $aFields     = $this->input->post('fields') ?: array();

        foreach ($aFields as $aField) {

            $aTemp = array(
                'id'                   => !empty($aField['id']) ? (int) $aField['id'] : null,
                'type'                 => !empty($aField['type']) ? $aField['type'] : 'TEXT',
                'label'                => !empty($aField['label']) ? $aField['label'] : '',
                'sub_label'            => !empty($aField['sub_label']) ? $aField['sub_label'] : '',
                'placeholder'          => !empty($aField['placeholder']) ? $aField['placeholder'] : '',
                'is_required'          => !empty($aField['is_required']) ? (bool) $aField['is_required'] : false,
                'default_value'        => !empty($aField['default_value']) ? $aField['default_value'] : '',
                'default_value_custom' => !empty($aField['default_value_custom']) ? $aField['default_value_custom'] : '',
                'custom_attributes'    => !empty($aField['custom_attributes']) ? $aField['custom_attributes'] : '',
                'order'                => $iFieldOrder,
                'options'              => array()
            );

            if (!empty($aField['options'])) {

                $iOptionOrder = 0;

                foreach ($aField['options'] as $aOption) {

                    $aTemp['options'][] = array(
                        'id'          => !empty($aOption['id']) ? (int) $aOption['id'] : null,
                        'label'       => !empty($aOption['label']) ? $aOption['label'] : '',
                        'is_selected' => !empty($aOption['is_selected']) ? $aOption['is_selected'] : false,
                        'is_disabled' => !empty($aOption['is_disabled']) ? $aOption['is_disabled'] : false,
                        'order'       => $iOptionOrder
                    );

                    $iOptionOrder++;
                }
            }

            $aData['fields'][] = $aTemp;
            $iFieldOrder++;
        }

        //  Format the emails
        $aEmails = explode(',', $this->input->post('notification_email'));
        $aEmails = array_map('trim', $aEmails);
        $aEmails = array_unique($aEmails);
        $aEmails = array_filter($aEmails);

        $aData['notification_email'] = json_encode($aEmails);

        return $aData;
    }

    // --------------------------------------------------------------------------

    /**
     * Delete an existing survey
     * @return void
     */
    public function delete()
    {
        if (!userHasPermission('admin:survey:survey:delete')) {
            unauthorised();
        }

        $iSurveyId = (int) $this->uri->segment(5);
        $sReturn   = $this->input->get('return') ? $this->input->get('return') : 'admin/survey/survey/index';

        $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');

        $oSurvey = $oSurveyModel->getById(
            $iSurveyId,
            array(
                'includeResponses' => true
            )
        );

        if (empty($oSurvey) || $oSurvey->responses->count > 0) {
            show_404();
        }

        if ($oSurveyModel->delete($iSurveyId)) {

            $sStatus  = 'success';
            $sMessage = 'Custom survey was deleted successfully.';

        } else {

            $sStatus  = 'error';
            $sMessage = 'Custom survey failed to delete. ' . $oSurveyModel->lastError();
        }

        $oSession = Factory::model('Session', 'nailsapp/module-auth');
        $oSession->set_flashdata($sStatus, $sMessage);
        redirect($sReturn);
    }

    // --------------------------------------------------------------------------

    /**
     * Browse survey responses
     * @return void
     */
    public function response()
    {
        if (!userHasPermission('admin:survey:survey:response')) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');

        $iSurveyId = (int) $this->uri->segment(5);
        $this->data['survey'] = $oSurveyModel->getById(
            $iSurveyId,
            array(
                'includeFields'    => true,
                'includeResponses' => true
            )
        );

        if (empty($this->data['survey'])) {
            show_404();
        }

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = 'Survey Responses &rsaquo; ' . $this->data['survey']->label;

        // --------------------------------------------------------------------------

        Helper::loadView('response');
    }

    // --------------------------------------------------------------------------

    /**
     * Browse existing survey
     * @return void
     */
    public function stats()
    {
        if (!userHasPermission('admin:survey:survey:stats')) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');

        $iSurveyId = (int) $this->uri->segment(5);
        $this->data['survey'] = $oSurveyModel->getById($iSurveyId);

        if (empty($this->data['survey'])) {
            show_404();
        }

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = 'Survey Statistics &rsaquo; ' . $this->data['survey']->label;

        // --------------------------------------------------------------------------

        Helper::loadView('stats');
    }
}
