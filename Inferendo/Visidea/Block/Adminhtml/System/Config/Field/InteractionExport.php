<?php

/**
 * Interaction export field.
 *
 * @category  Visidea
 * @package   Inferendo_Visidea
 * @author    Inferendo SRL <hello@visidea.ai>
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */

namespace Inferendo\Visidea\Block\Adminhtml\System\Config\Field;

use Inferendo\Visidea\Helper\Data;

/**
 * InteractionExport class
 * 
 * @category  Visidea
 * @package   Inferendo_Visidea
 * @author    Inferendo SRL <hello@visidea.ai>
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */
class InteractionExport extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $helper;
    protected $_template = 'interactionexport.phtml';

    /**
     * Method __construct
     *
     * @param \Magento\Backend\Block\Template\Context $context context
     * @param Data                                    $helper  helper
     * @param array                                   $data    data
     * 
     * @return void no return
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
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
     * Method getInteractionExportUrl
     * 
     * @return string url
     */
    public function getInteractionExportUrl()
    {
        return $this->helper->getInteractionExportUrl();
    }

    /**
     * Method isEnabled
     *
     * @return bool return true if enabled
     */
    public function isEnabled()
    {
        return $this->helper->isEnable();
    }
}
