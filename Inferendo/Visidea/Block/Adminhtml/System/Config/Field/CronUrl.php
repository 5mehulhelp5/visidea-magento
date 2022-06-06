<?php

/**
 * Cron Url field.
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
 * CronUrl class
 * 
 * @category  Visidea
 * @package   Inferendo_Visidea
 * @author    Inferendo SRL <hello@visidea.ai>
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */
class CronUrl extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $helper;
    protected $_template = 'cronurl.phtml';

    /**
     * Method __construct
     *
     * @param \Magento\Backend\Block\Template\Context $context context
     * @param \Inferendo\Visidea\Helper\Data          $helper  helper
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
     * Method getCronUrl
     * 
     * @return string url
     */
    public function getCronUrl()
    {
        return $this->helper->getCronUrl();
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
