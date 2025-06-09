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
use Magento\Framework\Locale\ResolverInterface;

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
    protected $httpContext;
    protected $localeResolver;

    /**
     * Method __construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context     context
     * @param \Magento\Framework\Registry                      $registry    registry
     * @param \Magento\Framework\App\Http\Context              $httpContext httpContext
     * @param array                                            $data        data
     * 
     * @return void
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->registry = $registry;
        $this->localeResolver = $localeResolver;
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

}
