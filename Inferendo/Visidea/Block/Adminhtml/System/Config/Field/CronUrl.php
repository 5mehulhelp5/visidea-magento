<?php

namespace Inferendo\Visidea\Block\Adminhtml\System\Config\Field;

use Inferendo\Visidea\Helper\Data;

class CronUrl extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $helper;
    protected $_template = 'cronurl.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Data $helper,
        array $data = []
    )
    {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setRenderer($this);
        return $this->_toHtml();
    }

    public function getCronUrl()
    {
        return $this->helper->getCronUrl();
    }

    public function isEnabled()
    {
        return $this->helper->isEnable();
    }
}
