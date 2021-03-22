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

use Nails\Admin\Controller\DefaultController;
use Nails\Admin\Factory\IndexFilter;
use Nails\Captcha\Service\Captcha;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Exception\ValidationException;
use Nails\Common\Helper\Model\Expand;
use Nails\Common\Resource;
use Nails\Common\Service\Asset;
use Nails\Common\Service\Input;
use Nails\Factory;
use Nails\FormBuilder;
use Nails\Survey\Constants;

/**
 * Class Survey
 *
 * @package Nails\Admin\Survey
 */
class Survey extends DefaultController
{
    const CONFIG_MODEL_NAME     = 'Survey';
    const CONFIG_MODEL_PROVIDER = Constants::MODULE_SLUG;
    const CONFIG_PERMISSION     = 'survey:survey';
    const CONFIG_SORT_OPTIONS   = [
        'Date Modified' => 'modified',
        'Date Created'  => 'created',
        'ID'            => 'id',
    ];
    const CONFIG_SORT_DIRECTION = self::SORT_DESCENDING;
    const CONFIG_INDEX_FIELDS   = [
        'ID'          => 'id',
        'Label'       => 'label',
        'Active'      => 'is_active',
        'Modified'    => 'modified',
        'Modified By' => 'modified_by',
    ];
    const CONFIG_INDEX_DATA     = [
        'expand' => [
            'responses:count',
            'responses_submitted:count',
        ],
    ];
    const CONFIG_EDIT_DATA      = [
        'expand' => [
            [
                'form',
                [
                    'expand' => [
                        [
                            'fields',
                            ['expand' => ['options']],
                        ],
                    ],
                ],
            ],
        ],
    ];
    const CONFIG_DELETE_DATA    = [
        'expand' => [
            'responses:count',
        ],
    ];

    // --------------------------------------------------------------------------

    /**
     * Survey constructor.
     *
     * @throws NailsException
     */
    public function __construct()
    {
        parent::__construct();
        $this->aConfig['CREATE_IGNORE_FIELDS'][] = 'token_stats';
        $this->aConfig['CREATE_IGNORE_FIELDS'][] = 'form_id';
        $this->aConfig['EDIT_IGNORE_FIELDS'][]   = 'token_stats';
        $this->aConfig['EDIT_IGNORE_FIELDS'][]   = 'form_id';

        static::addIndexRowButton(
            siteUrl('admin/survey/response/{{id}}'),
            function (\Nails\Survey\Resource\Survey $oItem) {
                return sprintf(
                    'Responses &ndash; %s',
                    $oItem->responses != $oItem->responses_submitted
                        ? $oItem->responses_submitted . '/' . $oItem->responses
                        : $oItem->responses
                );
            },
            'btn-warning'
        );

        static::addIndexRowButton(
            function (\Nails\Survey\Resource\Survey $oItem) {
                return $oItem->url_stats;
            },
            'Public Stats',
            'btn-default',
            'target="_blank"',
            null,
            function (\Nails\Survey\Resource\Survey $oItem) {
                return $oItem->allow_public_stats;
            }
        );
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    protected function indexCheckboxFilters(): array
    {
        /** @var IndexFilter $oStatusFilter */
        $oStatusFilter = Factory::factory('IndexFilter', \Nails\Admin\Constants::MODULE_SLUG);
        $oStatusFilter
            ->setLabel('Active')
            ->setColumn('is_active')
            ->addOption('Yes', true, true)
            ->addOption('No', false);

        return array_merge(
            parent::indexCheckboxFilters(),
            [
                $oStatusFilter,
            ]
        );
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    protected static function isEditButtonEnabled($oItem = null): bool
    {
        return parent::isEditButtonEnabled($oItem)
            && ($oItem->responses->count ?? $oItem->responses ?? 0) === 0;
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    protected static function isDeleteButtonEnabled($oItem = null): bool
    {
        return parent::isDeleteButtonEnabled($oItem)
            && ($oItem->responses->count ?? $oItem->responses ?? 0) === 0;
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    protected function runFormValidation(string $sMode, array $aOverrides = []): void
    {
        parent::runFormValidation($sMode, $aOverrides);

        /** @var Input $oInput */
        $oInput = Factory::service('Input');

        FormBuilder\Helper\FormBuilder::adminValidateFormData(
            (array) $oInput->post('fields')
        );
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    protected function getPostObject(): array
    {
        /** @var Input $oInput */
        $oInput = Factory::service('Input');

        $aPost = parent::getPostObject();

        $aPost['notification_email'] = json_encode(
            array_filter(
                array_unique(
                    array_map(
                        'trim',
                        explode(',', $aPost['notification_email'])
                    )
                )
            )
        );

        $aPost['form'] = FormBuilder\Helper\FormBuilder::adminNormalizeFormData(
            $this->data['oItem']->form->id ?? null,
            (bool) $oInput->post('has_captcha'),
            $oInput->post('fields')
        );

        return $aPost;
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function edit(): void
    {
        $oModel = $this->getModel();
        $oItem  = $this->getItem([new Expand('responses:count')]);

        if (empty($oItem) || !static::isEditButtonEnabled($oItem)) {
            show404();
        }

        parent::edit();
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    protected function loadEditViewData(Resource $oItem = null): void
    {
        parent::loadEditViewData($oItem);

        /** @var Captcha $oCaptcha */
        $oCaptcha = Factory::service('Captcha', \Nails\Captcha\Constants::MODULE_SLUG);

        $this->data['bIsCaptchaEnabled'] = $oCaptcha->isEnabled();
    }

    // --------------------------------------------------------------------------

    /**
     * @inheritDoc
     */
    public function delete(): void
    {
        $oModel = $this->getModel();
        $oItem  = $this->getItem([new Expand('responses:count')]);

        if (empty($oItem) || !static::isDeleteButtonEnabled($oItem)) {
            show404();
        }

        parent::delete();
    }
}
