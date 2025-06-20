<?php

/**
 * Items export field.
 *
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */

namespace Inferendo\Visidea\Block\Adminhtml\System\Config\Field;

use Inferendo\Visidea\Helper\Data;
use Magento\Framework\Escaper;

/**
 * Handles items export functionality in admin configuration
 */
class ItemsExport extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var string
     */
    protected $_template = 'itemsexport.phtml';

    /**
     * Method __construct
     *
     * @param \Magento\Backend\Block\Template\Context $context context
     * @param Data                                    $helper  helper
     * @param Escaper                                 $escaper escaper
     * @param array                                   $data    data
     *
     * @return void no return
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Data $helper,
        Escaper $escaper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->escaper = $escaper;
        parent::__construct($context, $data);
    }

    /**
     * Method render
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element element
     *
     * @return string html
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setRenderer($this);
        return $this->_toHtml();
    }

    /**
     * Method getItemsExportUrl
     *
     * @return string url
     */
    public function getItemsExportUrl()
    {
        return $this->helper->getItemsExportUrl();
    }

    /**
     * Method isEnabled
     *
     * @return bool return true if enabled
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled();
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
}
