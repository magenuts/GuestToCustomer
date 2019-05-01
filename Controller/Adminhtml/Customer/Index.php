<?php
/**
 * Copyright © Magenuts Pvt Ltd All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magenuts.com | support@magenuts.com
 **/

namespace Magenuts\GuestToCustomer\Controller\Adminhtml\Customer;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magenuts\GuestToCustomer\Helper\Data;

/**
 * Class Index
 * @package Magenuts\GuestToCustomer\Controller\Adminhtml\Customer
 */
class Index extends Action
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Index constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param AccountManagementInterface $accountManagement
     * @param OrderCustomerManagementInterface $orderCustomerService
     * @param JsonFactory $resultJsonFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        AccountManagementInterface $accountManagement,
        OrderCustomerManagementInterface $orderCustomerService,
        JsonFactory $resultJsonFactory,
        Data $helperData
    ) {
        parent::__construct($context);

        $this->orderRepository = $orderRepository;
        $this->orderCustomerService = $orderCustomerService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accountManagement = $accountManagement;
        $this->helperData = $helperData;
    }

    /**
     * Index action
     * @return Json
     * @throws Exception
     * @throws LocalizedException
     */
    public function execute()
    {
        $request = $this->getRequest();
        $orderId = $request->getPost('order_id', null);
        $resultJson = $this->resultJsonFactory->create();

        if ($orderId) {
            /** @var  $order OrderInterface */
            $order = $this->orderRepository->get($orderId);

            if ($order->getEntityId() && $this->accountManagement->isEmailAvailable($order->getEmailAddress())) {
                try {
                    $customer = $this->orderCustomerService->create($orderId);

                    if ($customer && $customer->getId()) {
                        $this->helperData->dispatchCustomerOrderLinkEvent($customer->getId(), $order->getIncrementId());
                    }

                    $this->messageManager->addSuccessMessage(__('Order was successfully converted.'));

                    return $resultJson->setData(
                        [
                            'error' => false,
                            'message' => __('Order was successfully converted.')
                        ]
                    );
                } catch (Exception $e) {
                    return $resultJson->setData(
                        [
                            'error' => true,
                            'message' => $e->getMessage()
                        ]
                    );
                }
            } else {
                return $resultJson->setData(
                    [
                        'error' => true,
                        'message' => __('Email address already belong to an existing customer.')
                    ]
                );
            }
        } else {
            return $resultJson->setData(
                [
                    'error' => true,
                    'message' => __('Invalid order id.')
                ]
            );
        }
    }

    /**
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenuts_GuestToCustomer::guesttocustomer');
    }
}
