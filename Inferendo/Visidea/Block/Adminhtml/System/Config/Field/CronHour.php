<?php
namespace Inferendo\Visidea\Block\Adminhtml\System\Config\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

class CronHour extends Field
{
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
