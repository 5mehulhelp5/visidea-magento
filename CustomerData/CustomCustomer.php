<?php
namespace Inferendo\Visidea\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as CustomerSession;

class CustomCustomer implements SectionSourceInterface
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Constructor.
     *
     * @param CustomerSession $customerSession
     */
    public function __construct(CustomerSession $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * Returns customer section data for frontend JS.
     *
     * @return array
     */
    public function getSectionData()
    {
        $customerId = $this->customerSession->getCustomerId();
        $customer = $this->customerSession->getCustomer();

        return [
            'loaded' => true,
            'customer_id' => $customerId,
            'customer_email' => $customer->getEmail(),
            'is_logged_in' => $this->customerSession->isLoggedIn(),
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
        ];
    }
}
