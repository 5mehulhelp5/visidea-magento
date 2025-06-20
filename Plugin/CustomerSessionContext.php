<?php

/**
 * Add session context to cached views.
 *
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */

namespace Inferendo\Visidea\Plugin;

/**
 * Adds customer session context to HTTP context for proper cache differentiation
 */
class CustomerSessionContext
{
    /**
     * Customer session instance
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * HTTP context instance
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\Session     $customerSession Customer session instance
     * @param \Magento\Framework\App\Http\Context $httpContext     HTTP context instance
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }

    /**
     * Around dispatch plugin to set customer context values
     *
     * @param \Magento\Framework\App\ActionInterface  $subject Action interface
     * @param callable                                $proceed Proceed callable
     * @param \Magento\Framework\App\RequestInterface $request Request interface
     *
     * @return mixed
     */
    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->httpContext->setValue(
            'customer_id',
            $this->customerSession->getCustomerId(),
            false
        );

        $this->httpContext->setValue(
            'customer_name',
            $this->customerSession->getCustomer()->getName(),
            false
        );

        $this->httpContext->setValue(
            'customer_email',
            $this->customerSession->getCustomer()->getEmail(),
            false
        );

        return $proceed($request);
    }
}
