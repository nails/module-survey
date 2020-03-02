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

use Nails\Admin\Helper;
use Nails\Common\Exception\NailsException;
use Nails\Common\Service\Session;
use Nails\Factory;
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
            $oNavGroup = Factory::factory('Nav', 'nails/module-admin');
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
    public static function permissions(): array
    {
        $aPermissions = parent::permissions();

        $aPermissions['browse']          = 'Can browse surveys';
        $aPermissions['create']          = 'Can create surveys';
        $aPermissions['edit']            = 'Can edit surveys';
        $aPermissions['copy']            = 'Can copy surveys';
        $aPermissions['delete']          = 'Can delete surveys';
        $aPermissions['stats']           = 'Can view survey stats';
        $aPermissions['response']        = 'Can view responses';
        $aPermissions['response:edit']   = 'Can edit text component of responses';
        $aPermissions['response:delete'] = 'Can delete responses';

        return $aPermissions;
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

        $oSurveyModel = Factory::model('Survey', 'nails/module-survey');

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = 'Browse Surveys';

        // --------------------------------------------------------------------------

        //  Get pagination and search/sort variables
        $sTableAlias = $oSurveyModel->getTableAlias();
        $oInput      = Factory::service('Input');
        $iPage       = (int) $oInput->get('page') ? $oInput->get('page') : 0;
        $iPerPage    = (int) $oInput->get('perPage') ? $oInput->get('perPage') : 50;
        $sSortOn     = $oInput->get('sortOn') ? $oInput->get('sortOn') : $sTableAlias . '.label';
        $sSortOrder  = $oInput->get('sortOrder') ? $oInput->get('sortOrder') : 'asc';
        $sKeywords   = $oInput->get('keywords') ? $oInput->get('keywords') : '';

        // --------------------------------------------------------------------------

        //  Define the sortable columns
        $sortColumns = [
            $sTableAlias . '.id'       => 'Survey ID',
            $sTableAlias . '.label'    => 'Label',
            $sTableAlias . '.modified' => 'Modified Date',
        ];

        // --------------------------------------------------------------------------

        //  Define the $aData variable for the queries
        $aData = [
            'expand'   => ['responses'],
            'sort'     => [
                [$sSortOn, $sSortOrder],
            ],
            'keywords' => $sKeywords,
        ];

        //  Get the items for the page
        $totalRows             = $oSurveyModel->countAll($aData);
        $this->data['surveys'] = $oSurveyModel->getAll($iPage, $iPerPage, $aData);

        //  Set Search and Pagination objects for the view
        $this->data['search']     = Helper::searchObject(true, $sortColumns, $sSortOn, $sSortOrder, $iPerPage, $sKeywords);
        $this->data['pagination'] = Helper::paginationObject($iPage, $iPerPage, $totalRows);

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

        $oSurveyModel = Factory::model('Survey', 'nails/module-survey');
        $oInput       = Factory::service('Input');

        if ($oInput->post()) {
            if ($this->runFormValidation()) {
                if ($oSurveyModel->create($this->getPostObject())) {

                    /** @var Session $oSession */
                    $oSession = Factory::service('Session');
                    $oSession->setFlashData('success', 'Survey created successfully.');
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
        $oSurveyModel = Factory::model('Survey', 'nails/module-survey');

        $iSurveyId            = (int) $oUri->segment(5);
        $this->data['survey'] = $oSurveyModel->getById(
            $iSurveyId,
            [
                'expand' => [
                    [
                        'form',
                        [
                            'expand' => [
                                ['fields', ['expand' => ['options']]],
                            ],
                        ],
                    ],
                    'responses',
                ],
            ]
        );

        if (empty($this->data['survey']) || $this->data['survey']->responses->count > 0) {
            show404();
        }

        $oSurveyModel = Factory::model('Survey', 'nails/module-survey');

        if ($oInput->post()) {
            if ($this->runFormValidation()) {
                if ($oSurveyModel->update($iSurveyId, $this->getPostObject())) {

                    /** @var Session $oSession */
                    $oSession = Factory::service('Session');
                    $oSession->setFlashData('success', 'Survey updated successfully.');
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
        //  @todo (Pablo - 2018-11-15) - Update/Remove/Use minified once JS is refactored to be a module
        $oAsset->load('admin.survey.edit.js', 'nails/module-survey');

        Factory::helper('formbuilder', 'nails/module-form-builder');
        adminLoadFormBuilderAssets('#survey-fields');

        $oCaptcha                        = Factory::service('Captcha', 'nails/module-captcha');
        $this->data['bIsCaptchaEnabled'] = $oCaptcha->isEnabled();
    }

    // --------------------------------------------------------------------------

    /**
     * Form validation for edit/create
     *
     * @param array $aOverrides Any overrides for the fields; best to do this in the model's describeFields() method
     *
     * @return bool
     */
    protected function runFormValidation(array $aOverrides = [])
    {
        $oFormValidation = Factory::service('FormValidation');
        $oInput          = Factory::service('Input');

        //  Define the rules
        $aRules = [
            'label'                    => 'required',
            'is_active'                => '',
            'header'                   => '',
            'footer'                   => '',
            'cta_label'                => '',
            'cta_attributes'           => '',
            'survey_attributes'        => '',
            'has_captcha'              => '',
            'is_minimal'               => '',
            'notification_email'       => 'valid_emails',
            'allow_anonymous_response' => '',
            'allow_public_stats'       => '',
            'stats_header'             => '',
            'stats_footer'             => '',
            'thankyou_email'           => '',
            'thankyou_email_subject'   => '',
            'thankyou_email_body'      => '',
            'thankyou_page_title'      => '',
            'thankyou_page_body'       => '',
        ];

        foreach ($aRules as $sKey => $sRules) {
            $oFormValidation->set_rules($sKey, '', $sRules);
        }

        $oFormValidation->set_message('required', lang('fv_required'));
        $oFormValidation->set_message('valid_emails', lang('fv_valid_emails'));

        $bValidForm = $oFormValidation->run();

        //  Validate fields
        Factory::helper('formbuilder', 'nails/module-form-builder');
        $bValidFields = adminValidateFormData($oInput->post('fields'));

        return $bValidForm && $bValidFields;
    }

    // --------------------------------------------------------------------------

    protected function getPostObject()
    {
        Factory::helper('formbuilder', 'nails/module-form-builder');
        $oInput  = Factory::service('Input');
        $iFormId = !empty($this->data['survey']->form->id) ? $this->data['survey']->form->id : null;
        $aData   = [
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
            ),
        ];

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

        $oSurveyModel = Factory::model('Survey', 'nails/module-survey');
        $oSurvey      = $oSurveyModel->getById($iSurveyId, ['expand' => ['responses']]);

        if (empty($oSurvey) || $oSurvey->responses->count > 0) {
            show404();
        }

        if ($oSurveyModel->delete($iSurveyId)) {
            $sStatus  = 'success';
            $sMessage = 'Survey was deleted successfully.';
        } else {
            $sStatus  = 'error';
            $sMessage = 'Survey failed to delete. ' . $oSurveyModel->lastError();
        }

        /** @var Session $oSession */
        $oSession = Factory::service('Session');
        $oSession->setFlashData($sStatus, $sMessage);
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
        $oSurveyModel = Factory::model('Survey', 'nails/module-survey');

        $iNewSurveyId = $oSurveyModel->copy($iSurveyId);

        if ($iNewSurveyId) {

            $sStatus  = 'success';
            $sMessage = 'Survey was copied successfully.';
            $sReturn  = 'admin/survey/survey/edit/' . $iNewSurveyId;

        } else {
            $sStatus  = 'error';
            $sMessage = 'Survey failed to copy. ' . $oSurveyModel->lastError();
        }

        /** @var Session $oSession */
        $oSession = Factory::service('Session');
        $oSession->setFlashData($sStatus, $sMessage);
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

        if (is_callable([$this, $sMethod])) {

            $oSurveyModel         = Factory::model('Survey', 'nails/module-survey');
            $iSurveyId            = (int) $oUri->segment(5);
            $this->data['survey'] = $oSurveyModel->getById(
                $iSurveyId,
                [
                    'expand' => [
                        [
                            'form',
                            ['expand' => ['fields']],
                        ],
                        ['responses', ['expand' => ['user']]],
                    ],
                ]
            );

            if (empty($this->data['survey'])) {
                show404();
            }
            $this->{$sMethod}();

        } else {
            show404();
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
        arraySortMulti($this->data['survey']->responses->data, 'date_submitted');
        $this->data['survey']->responses->data = array_reverse($this->data['survey']->responses->data);

        // --------------------------------------------------------------------------

        $oAsset = Factory::service('Asset');
        $oAsset->library('ZEROCLIPBOARD');
        $oAsset->load('https://www.gstatic.com/charts/loader.js');
        //  @todo (Pablo - 2018-11-15) - Update/Remove/Use minified once JS is refactored to be a module
        $oAsset->load('admin.survey.stats.js', 'nails/module-survey');
        $oAsset->load('admin.survey.stats.charts.js', 'nails/module-survey');
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
        $oResponseModel         = Factory::model('Response', 'nails/module-survey');
        $iResponseId            = (int) $oUri->segment(7);
        $this->data['response'] = $oResponseModel->getById(
            $iResponseId,
            [
                'expand' => ['answers'],
            ]
        );

        if (empty($this->data['response'])) {
            show404();
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Survey Responses &rsaquo; ' . $this->data['survey']->label . ' &rsaquo; Response';

        // --------------------------------------------------------------------------

        Helper::loadView('response/view');
    }

    // --------------------------------------------------------------------------

    protected function responseEdit()
    {

        $oUri                 = Factory::service('Uri');
        $oResponseAnswerModel = Factory::model('ResponseAnswer', 'nails/module-survey');
        $iSurveyId            = (int) $oUri->segment(5);
        $iAnswerId            = (int) $oUri->segment(7);
        $oAnswer              = $oResponseAnswerModel->getById(
            $iAnswerId
        );

        if (empty($oAnswer)) {
            show404();
        }

        $oInput = Factory::service('Input');
        if ($oInput->post()) {

            try {

                $oFormValidation = Factory::service('FormValidation');
                $oFormValidation->set_rules('text', '', 'trim');
                if (!$oFormValidation->run()) {
                    throw new NailsException(lang('fv_there_were_errors'));
                }

                $aData = [
                    'text' => $oInput->post('text'),
                ];

                if (!$oResponseAnswerModel->update($iAnswerId, $aData)) {
                    throw new NailsException('Failed to update answer. ' . $oResponseAnswerModel->lastError());
                }

                /** @var Session $oSession */
                $oSession = Factory::service('Session');
                $oSession->setFlashData('success', 'Answer updated successfully.');

                $sIsModal = !empty($oInput->get('isModal')) ? '?isModal=1' : '';

                redirect('admin/survey/survey/response/' . $iSurveyId . '/view/' . $oAnswer->survey_response_id . $sIsModal);

            } catch (\Exception $e) {
                $this->data['error'] = $e->getMessage();
            }
        }

        // --------------------------------------------------------------------------

        $this->data['answer']      = $oAnswer;
        $this->data['page']->title = 'Edit Survey Responses &rsaquo; ' . $this->data['survey']->label . ' &rsaquo; Answer';

        // --------------------------------------------------------------------------

        Helper::loadView('response/edit');
    }
}
