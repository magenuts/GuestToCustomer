<?php
/**
 * Copyright © Magenuts LLC, Inc. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magenuts.com | support@magenuts.com
 */
namespace Magenuts\GuestToCustomer\Block\View\Element\Html\Link;

use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template\Context;
use Magenuts\GuestToCustomer\Helper\Data;

/**
 * Class Current
 * @package Magenuts\GuestToCustomer\Block\View\Element\Html\Link
 */
class Current extends \Magento\Framework\View\Element\Html\Link\Current
{

    /* @var Data*/
    protected $helperData;

    /**
     * Constructor
     *
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param array $data
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        array $data = [],
        Data $helperData
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->helperData = $helperData;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->helperData->isEnabledCustomerDashbard()) {
            return parent::_toHtml();
        }

        return '';
    }
}
