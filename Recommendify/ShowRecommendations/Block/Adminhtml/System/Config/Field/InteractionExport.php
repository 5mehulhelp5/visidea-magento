<?php

namespace Recommendify\ShowRecommendations\Block\Adminhtml\System\Config\Field;

use Recommendify\ShowRecommendations\Helper\Data;

class InteractionExport extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $helper;
    protected $_template = 'interactionexport.phtml';

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

    public function getInteractionExportUrl()
    {
        return $this->helper->getInteractionExportUrl();
    }

    public function isEnabled()
    {
        return $this->helper->isEnable();
    }
}
