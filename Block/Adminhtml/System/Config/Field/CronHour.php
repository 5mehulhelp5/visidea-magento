<?php

/**
 * Cron hour configuration field.
 *
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */

namespace Inferendo\Visidea\Block\Adminhtml\System\Config\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

/**
 * Provides dropdown field for cron hour configuration in admin panel
 */
class CronHour extends Field
{
    /**
     * Get the element HTML for cron hour selection
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $options = [1, 2, 3, 4, 6, 8, 12, 24];
        $html = '<select id="' . $element->getHtmlId() . '" name="' . $element->getName() . '">';
        foreach ($options as $value) {
            $selected = ($element->getValue() == $value) ? ' selected="selected"' : '';
            $html .= '<option value="' . $value . '"' . $selected . '>' . $value . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
}
