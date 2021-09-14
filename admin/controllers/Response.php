<?php

/**
 * Manage Responses
 *
 * @package     module-survey
 * @subpackage  Admin
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Survey;

use Nails\Admin\Controller\Base;
use Nails\Admin\Helper;
use Nails\Common\Exception\AssetException;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Helper\Model\Expand;
use Nails\Common\Service\Asset;
use Nails\Common\Service\Input;
use Nails\Common\Service\Uri;
use Nails\Factory;
use Nails\Survey\Constants;
use Nails\Survey\Model\Response\Answer;

/**
 * Class Response
 *
 * @package Nails\Admin\Survey
 */
class Response extends Base
{
    /**
     * Renders the overall index view for a survey's responses
     *
     * @param \Nails\Survey\Resource\Survey $oSurvey
     *
     * @throws AssetException
     * @throws FactoryException
     * @throws NailsException
     */
    public function index(\Nails\Survey\Resource\Survey $oSurvey)
    {
        //  Sort the responses so that newest is at the top and unsubmitted are at the bottom
        arraySortMulti($oSurvey->responses->data, 'date_submitted');
        $oSurvey->responses->data = array_reverse($oSurvey->responses->data);

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Survey Responses &rsaquo; ' . $oSurvey->label;
        $this->data['oSurvey']     = $oSurvey;

        // --------------------------------------------------------------------------

        Helper::loadView('index');
    }

    // --------------------------------------------------------------------------

    /**
     * Renders a single response object
     *
     * @param \Nails\Survey\Resource\Survey $oSurvey
     *
     * @throws FactoryException
     * @throws NailsException
     * @throws ModelException
     */
    public function view(\Nails\Survey\Resource\Survey $oSurvey)
    {
        /** @var Uri $oUri */
        $oUri = Factory::service('Uri');
        /** @var \Nails\Survey\Model\Response $oResponseModel */
        $oResponseModel = Factory::model('Response', Constants::MODULE_SLUG);

        /** @var \Nails\Survey\Resource\Response $oResponse */
        $oResponse = $oResponseModel->getById(
            (int) $oUri->segment(6),
            [
                new Expand('answers', new Expand\Group([
                    new Expand('question'),
                    new Expand('option'),
                ])),
            ]
        );

        if (empty($oResponse)) {
            show404();
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Survey Responses &rsaquo; ' . $oSurvey->label . ' &rsaquo; Response';
        $this->data['oResponse']   = $oResponse;

        // --------------------------------------------------------------------------

        Helper::loadView('view');
    }

    // --------------------------------------------------------------------------

    /**
     * Allows a response to be edited
     *
     * @param \Nails\Survey\Resource\Survey $oSurvey
     *
     * @throws FactoryException
     * @throws ModelException
     */
    public function edit(\Nails\Survey\Resource\Survey $oSurvey)
    {
        /** @var Uri $oUri */
        $oUri = Factory::service('Uri');
        /** @var Input $oInput */
        $oInput = Factory::service('Input');
        /** @var Answer $oResponseAnswerModel */
        $oResponseAnswerModel = Factory::model('ResponseAnswer', Constants::MODULE_SLUG);

        /** @var \Nails\Survey\Resource\Response\Answer $oAnswer */
        $oAnswer = $oResponseAnswerModel->getById((int) $oUri->segment(6));
        if (empty($oAnswer)) {
            show404();
        }

        if ($oInput->post()) {

            try {

                if (!$oResponseAnswerModel->update($oAnswer->id, ['text' => $oInput->post('text')])) {
                    throw new NailsException('Failed to update answer. ' . $oResponseAnswerModel->lastError());
                }

                $this->oUserFeedback->success('Answer updated successfully.');

                $sIsModal = !empty($oInput->get('isModal')) ? '?isModal=1' : '';

                redirect('admin/survey/response/' . $oSurvey->id . '/view/' . $oAnswer->survey_response_id . $sIsModal);

            } catch (\Exception $e) {
                $this->oUserFeedback->error($e->getMessage());
            }
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Edit Survey Responses &rsaquo; ' . $oSurvey->label . ' &rsaquo; Answer';
        $this->data['answer']      = $oAnswer;

        // --------------------------------------------------------------------------

        Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Route the request accordingly
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    public function _remap()
    {
        if (!userHasPermission('admin:survey:survey:response')) {
            unauthorised();
        }

        /** @var Uri $oUri */
        $oUri = Factory::service('Uri');
        /** @var \Nails\Survey\Model\Survey $oModel */
        $oModel = Factory::model('Survey', Constants::MODULE_SLUG);

        $oItem = $oModel->getById($oUri->segment(4), [
            new Expand('form', new Expand('fields')),
            new Expand('responses', new Expand('user')),
        ]);

        if (empty($oItem)) {
            show404();
        }

        switch ($oUri->segment(5)) {
            case 'view':
                $this->view($oItem);
                break;

            case 'edit':
                $this->edit($oItem);
                break;

            default:
                $this->index($oItem);
                break;
        }
    }
}
