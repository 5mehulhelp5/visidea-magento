<?php

/**
 * Instructions field.
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
 * Instructions class
 * 
 * @category  Visidea
 * @package   Inferendo_Visidea
 * @author    Inferendo SRL <hello@visidea.ai>
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */
class Instructions extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $helper;
    protected $_template = 'instructions.phtml';

    /**
     * Method __construct
     *
     * @param \Magento\Backend\Block\Template\Context  $context   context
     * @param \Magento\Framework\View\Asset\Repository $assetRepo assetRepo
     * @param array                                    $data      data
     * 
     * @return void no return
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        array $data = []
    ) {
        $this->_assetRepo = $assetRepo;
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
     * Method getImageUrl
     * 
     * @return string url
     */
    public function getImageUrl()
    {
        return $this->_assetRepo->getUrl("Inferendo_Visidea::images/visidea-logo.png");
    }
    
}
