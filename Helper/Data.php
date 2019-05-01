<?php
/**
 * Copyright © Magenuts Pvt Ltd All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magenuts.com | support@magenuts.com
 */

namespace Magenuts\GuestToCustomer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ACTIVE = 'guesttocustomer/general/active';
    const XML_CUSTOMER_DASHBOARD = 'guesttocustomer/general/customer_dashboard';

    /**
     * @param Context $context
     * @param ObjectManagerInterface
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Whether is active
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ACTIVE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Customer dashboard active
     *
     * @return bool
     */
    public function isEnabledCustomerDashbard()
    {
        return $this->isEnabled() && $this->scopeConfig->getValue(
            self::XML_CUSTOMER_DASHBOARD,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $customerId int
     * @param $orderId int
     * @return $this
     */
    public function dispatchCustomerOrderLinkEvent($customerId, $orderId)
    {
        $this->_eventManager->dispatch('magenuts_guest_to_customer_save', [
            'customer_id' => $customerId,
            'order_id' => $orderId, //incrementId
            'increment_id' => $orderId //$incrementId
        ]);

        return $this;
    }
}
