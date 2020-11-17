<?php

namespace Recommendify\ShowRecommendations\Block;

use Magento\Framework\Exception\NoSuchEntityException;

class Info extends \Magento\Framework\View\Element\Template
{
    protected $currentCustomer;
    protected $_registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        $this->_registry = $registry;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function getCustomerId()
    {
        return $this->customer->getId();
    }

    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    public function getCustomerSession()
    {
        return $this->_customerSession;
    }
}
