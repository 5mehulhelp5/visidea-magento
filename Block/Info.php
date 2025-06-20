<?php

/**
 * Info for website.
 *
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 * @api
 */

namespace Inferendo\Visidea\Block;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Escaper;
use Inferendo\Visidea\Helper\Data;

/**
 * Provides customer and product information for Visidea integration
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Method __construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context        context
     * @param \Magento\Framework\Registry                      $registry       registry
     * @param \Magento\Framework\App\Http\Context              $httpContext    httpContext
     * @param \Magento\Framework\Locale\ResolverInterface      $localeResolver localeResolver
     * @param Escaper                                          $escaper        escaper
     * @param Data                                             $helper         helper
     * @param array                                            $data           data
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        Escaper $escaper,
        Data $helper,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->registry = $registry;
        $this->localeResolver = $localeResolver;
        $this->escaper = $escaper;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Method getCustomerId
     *
     * @return string  returns customer id
     */
    public function getCustomerId()
    {
        // return $this->customer->getId();
        return $this->httpContext->getValue('customer_id');
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
     * Method isLoggedIn
     *
     * @return bool  returns true if user is logged in
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue('customer_id') > 0;
    }

    /**
     * Get language code
     *
     * @return string
     */
    public function getLanguage()
    {
        $locale = $this->localeResolver->getLocale();
        $languageCode = substr($locale, 0, 2); // Extract language code (e.g., 'en' from 'en_US')
        return $languageCode;
    }

    /**
     * Get escaper instance
     *
     * @return Escaper
     */
    public function getEscaper()
    {
        return $this->escaper;
    }

    /**
     * Get helper instance
     *
     * @return Data
     */
    public function helper()
    {
        return $this->helper;
    }
}
