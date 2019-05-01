<?php
/**
 * Copyright © Magenuts Pvt Ltd All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magenuts.com | support@magenuts.com
 */
namespace Magenuts\GuestToCustomer\Controller\Guesttocustomer;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magenuts\GuestToCustomer\Helper\Data;

/**
 * Class LookupformPost
 * @package Magenuts\GuestToCustomer\Controller\Guesttocustomer
 */
class LookupformPost extends AbstractAccount
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param Data $helperData
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        Data $helperData,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Validator $formKeyValidator
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $customerSession;
        $this->helperData = $helperData;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->formKeyValidator = $formKeyValidator;

        parent::__construct($context);
    }

    /**
     * Customer login form page
     *
     * @return Redirect|Page
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());

        if ($validFormKey
            && $this->getRequest()->isPost()
            && $incrementId = $this->getRequest()->getPost('order_increment')
        ) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                'increment_id',
                $incrementId,
                'eq'
            )->create();

            $order = $this->orderRepository->getList($searchCriteria)->getFirstItem();

            if ($order->getId() && !$order->getCustomerId()
                && $order->getCustomerEmail() === $this->session->getCustomer()->getEmail()
            ) {
                $order->setCustomerId($this->session->getCustomerId());
                $order->setCustomerIsGuest(0);
                $this->orderRepository->save($order);

                $this->helperData->dispatchCustomerOrderLinkEvent($this->session->getCustomerId(), $incrementId);

                $this->messageManager->addSuccessMessage(__('Order was successfully added to your account'));
            } else {
                $this->messageManager->addErrorMessage(__('Unknown error please try again.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find the order.'));
        }

        return $resultRedirect->setPath('*/*/lookupform');
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->helperData->isEnabledCustomerDashbard() || !$this->session->isLoggedIn()) {
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');

            return $resultRedirect;
        }

        return parent::dispatch($request);
    }
}
