<?php

/**
 * Info for website.
 *
 * @category  Visidea
 * @package   Inferendo_Visidea
 * @author    Inferendo SRL <hello@visidea.ai>
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */

namespace Inferendo\Visidea\Block;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Info class
 * 
 * @category  Visidea
 * @package   Inferendo_Visidea
 * @author    Inferendo SRL <hello@visidea.ai>
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */
class Info extends \Magento\Framework\View\Element\Template
{
    protected $currentCustomer;
    protected $registry;

    /**
     * Method __construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context         context
     * @param \Magento\Framework\Registry                      $registry        registry
     * @param \Magento\Customer\Model\Session                  $customerSession customerSession
     * @param array                                            $data            data
     * 
     * @return void
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * Method getCustomerId
     *
     * @return string  returns customer id
     */
    public function getCustomerId()
    {
        return $this->customer->getId();
    }

    /**
     * Method getCurrentProduct
     *
     * @return string  returns current product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Method getCustomerSession
     *
     * @return string  returns customer session
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }
}
