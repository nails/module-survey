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
        $permissions['copy']            = 'Can copy surveys';
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
        $tableAlias = $oSurveyModel->getTableAlias();
        $oInput      = Factory::service('Input');
        $page        = $oInput->get('page')      ? $oInput->get('page')      : 0;
        $perPage     = $oInput->get('perPage')   ? $oInput->get('perPage')   : 50;
        $sortOn      = $oInput->get('sortOn')    ? $oInput->get('sortOn')    : $tableAlias . '.label';
        $sortOrder   = $oInput->get('sortOrder') ? $oInput->get('sortOrder') : 'asc';
        $keywords    = $oInput->get('keywords')  ? $oInput->get('keywords')  : '';

        // --------------------------------------------------------------------------

        //  Define the sortable columns
        $sortColumns = array(
            $tableAlias . '.id'       => 'Survey ID',
            $tableAlias . '.label'    => 'Label',
            $tableAlias . '.modified' => 'Modified Date'
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
        $oInput       = Factory::service('Input');

        if ($oInput->post()) {
            if ($this->runFormValidation()) {
                if ($oSurveyModel->create($this->getPostObject())) {

                    $oSession = Factory::service('Session', 'nailsapp/module-auth');
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

        $oUri         = Factory::service('Uri');
        $oInput       = Factory::service('Input');
        $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');

        $iSurveyId            = (int) $oUri->segment(5);
        $this->data['survey'] = $oSurveyModel->getById(
            $iSurveyId,
            array(
                'includeForm'      => true,
                'includeResponses' => true
            )
        );

        if (empty($this->data['survey']) || $this->data['survey']->responses->count > 0) {
            show_404();
        }

        $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');

        if ($oInput->post()) {
            if ($this->runFormValidation()) {
                if ($oSurveyModel->update($iSurveyId, $this->getPostObject())) {

                    $oSession = Factory::service('Session', 'nailsapp/module-auth');
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

        Factory::helper('formbuilder', 'nailsapp/module-form-builder');
        adminLoadFormBuilderAssets('#survey-fields');

        $oCaptchaModel = Factory::model('Captcha', 'nailsapp/module-captcha');
        $this->data['bIsCaptchaEnabled'] = $oCaptchaModel->isEnabled();
    }

    // --------------------------------------------------------------------------

    protected function runFormValidation()
    {
        $oFormValidation = Factory::service('FormValidation');
        $oInput          = Factory::service('Input');

        //  Define the rules
        $aRules = array(
            'label'                    => 'xss_clean|required',
            'is_active'                => '',
            'header'                   => '',
            'footer'                   => '',
            'cta_label'                => 'xss_clean',
            'cta_attributes'           => 'xss_clean',
            'survey_attributes'        => 'xss_clean',
            'has_captcha'              => '',
            'is_minimal'               => '',
            'notification_email'       => 'valid_emails',
            'allow_anonymous_response' => '',
            'allow_public_stats'       => '',
            'stats_header'             => '',
            'stats_footer'             => '',
            'thankyou_email'           => '',
            'thankyou_email_subject'   => 'xss_clean',
            'thankyou_email_body'      => 'xss_clean',
            'thankyou_page_title'      => 'xss_clean|required',
            'thankyou_page_body'       => ''
        );

        foreach ($aRules as $sKey => $sRules) {
            $oFormValidation->set_rules($sKey, '', $sRules);
        }

        $oFormValidation->set_message('required', lang('fv_required'));
        $oFormValidation->set_message('valid_emails', lang('fv_valid_emails'));

        $bValidForm = $oFormValidation->run();

        //  Validate fields
        Factory::helper('formbuilder', 'nailsapp/module-form-builder');
        $bValidFields = adminValidateFormData($oInput->post('fields'));

        return $bValidForm && $bValidFields;
    }

    // --------------------------------------------------------------------------

    protected function getPostObject()
    {
        Factory::helper('formbuilder', 'nailsapp/module-form-builder');
        $oInput  = Factory::service('Input');
        $iFormId = !empty($this->data['survey']->form->id) ? $this->data['survey']->form->id : null;
        $aData   = array(
            'label'                    => $oInput->post('label'),
            'is_active'                => (bool) $oInput->post('is_active'),
            'header'                   => $oInput->post('header'),
            'footer'                   => $oInput->post('footer'),
            'cta_label'                => $oInput->post('cta_label'),
            'cta_attributes'           => $oInput->post('cta_attributes'),
            'form_attributes'          => $oInput->post('form_attributes'),
            'is_minimal'               => (bool) $oInput->post('is_minimal'),
            'allow_anonymous_response' => (bool) $oInput->post('allow_anonymous_response'),
            'allow_public_stats'       => (bool) $oInput->post('allow_public_stats'),
            'stats_header'             => $oInput->post('stats_header'),
            'stats_footer'             => $oInput->post('stats_footer'),
            'thankyou_email'           => (bool) $oInput->post('thankyou_email'),
            'thankyou_email_subject'   => $oInput->post('thankyou_email_subject'),
            'thankyou_email_body'      => $oInput->post('thankyou_email_body'),
            'thankyou_page_title'      => $oInput->post('thankyou_page_title'),
            'thankyou_page_body'       => $oInput->post('thankyou_page_body'),
            'form'                     => adminNormalizeFormData(
                $iFormId,
                (bool) $oInput->post('has_captcha'),
                $oInput->post('fields')
            )
        );

        //  Format the emails
        $aEmails = explode(',', $oInput->post('notification_email'));
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

        $oUri      = Factory::service('Uri');
        $oInput    = Factory::service('Input');
        $iSurveyId = (int) $oUri->segment(5);
        $sReturn   = $oInput->get('return') ? $oInput->get('return') : 'admin/survey/survey/index';

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
            $sMessage = 'Survey was deleted successfully.';

        } else {

            $sStatus  = 'error';
            $sMessage = 'Survey failed to delete. ' . $oSurveyModel->lastError();
        }

        $oSession = Factory::service('Session', 'nailsapp/module-auth');
        $oSession->set_flashdata($sStatus, $sMessage);
        redirect($sReturn);
    }

    // --------------------------------------------------------------------------

    /**
     * Delete an existing survey
     * @return void
     */
    public function copy()
    {
        if (!userHasPermission('admin:survey:survey:copy')) {
            unauthorised();
        }

        $oUri         = Factory::service('Uri');
        $oInput       = Factory::service('Input');
        $iSurveyId    = (int) $oUri->segment(5);
        $sReturn      = $oInput->get('return') ? $oInput->get('return') : 'admin/survey/survey/index';
        $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');

        $iNewSurveyId = $oSurveyModel->copy($iSurveyId);

        if ($iNewSurveyId) {

            $sStatus  = 'success';
            $sMessage = 'Survey was copied successfully.';
            $sReturn  = 'admin/survey/survey/edit/' . $iNewSurveyId;

        } else {

            $sStatus  = 'error';
            $sMessage = 'Survey failed to copy. ' . $oSurveyModel->lastError();
        }

        $oSession = Factory::service('Session', 'nailsapp/module-auth');
        $oSession->set_flashdata($sStatus, $sMessage);
        redirect($sReturn);
    }

    // --------------------------------------------------------------------------

    /**
     * Route the response controllers
     * @return void
     */
    public function response()
    {
        $oUri    = Factory::service('Uri');
        $sMethod = $oUri->segment(6) ?: 'index';
        $sMethod = 'response' . ucfirst($sMethod);

        if (is_callable(array($this, $sMethod))) {

            $oSurveyModel = Factory::model('Survey', 'nailsapp/module-survey');

            $iSurveyId = (int) $oUri->segment(5);
            $this->data['survey'] = $oSurveyModel->getById(
                $iSurveyId,
                array(
                    'includeForm'      => true,
                    'includeResponses' => true
                )
            );

            if (empty($this->data['survey'])) {
                show_404();
            }

            $this->{$sMethod}();
        } else {
            show_404();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Browse survey responses
     * @return void
     */
    protected function responseIndex()
    {
        if (!userHasPermission('admin:survey:survey:response')) {
            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = 'Survey Responses &rsaquo; ' . $this->data['survey']->label;

        // --------------------------------------------------------------------------

        //  Sort the responses so that newest is at the top and unsubmitted are at the bottom
        array_sort_multi($this->data['survey']->responses->data, 'date_submitted');
        $this->data['survey']->responses->data = array_reverse($this->data['survey']->responses->data);

        // --------------------------------------------------------------------------

        $oAsset = Factory::service('Asset');
        $oAsset->library('ZEROCLIPBOARD');
        $oAsset->load('https://www.gstatic.com/charts/loader.js');
        $oAsset->load('admin.survey.stats.min.js', 'nailsapp/module-survey');
        $oAsset->load('admin.survey.stats.charts.min.js', 'nailsapp/module-survey');
        $oAsset->inline(
            'var SurveyStats = new _ADMIN_SURVEY_STATS(
                ' . $this->data['survey']->id . ',
                "' . $this->data['survey']->access_token . '"
            );',
            'JS'
        );

        // --------------------------------------------------------------------------

        Helper::loadView('response/index');
    }

    // --------------------------------------------------------------------------

    protected function responseView()
    {
        $oUri                   = Factory::service('Uri');
        $oResponseModel         = Factory::model('Response', 'nailsapp/module-survey');
        $iResponseId            = (int) $oUri->segment(7);
        $this->data['response'] = $oResponseModel->getById(
            $iResponseId,
            array(
                'includeAnswer' => true
            )
        );

        if (empty($this->data['response'])) {
            show_404();
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Survey Responses &rsaquo; ' . $this->data['survey']->label . ' &rsaquo; Response';

        // --------------------------------------------------------------------------

        Helper::loadView('response/view');
    }
}
