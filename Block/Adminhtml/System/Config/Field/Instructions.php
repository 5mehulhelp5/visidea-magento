<?php

/**
 * Instructions field.
 *
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */

namespace Inferendo\Visidea\Block\Adminhtml\System\Config\Field;

use Inferendo\Visidea\Helper\Data;
use Magento\Framework\Escaper;

/**
 * Renders instructions and configuration for Visidea integration in admin panel
 */
class Instructions extends \Magento\Config\Block\System\Config\Form\Field
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
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var string
     */
    protected $_template = 'instructions.phtml';

    /**
     * Method __construct
     *
     * @param \Magento\Backend\Block\Template\Context  $context   context
     * @param \Magento\Framework\View\Asset\Repository $assetRepo assetRepo
     * @param Data                                     $helper    helper
     * @param Escaper                                  $escaper   escaper
     * @param array                                    $data      data
     *
     * @return void no return
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Inferendo\Visidea\Helper\Data $helper,
        Escaper $escaper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->escaper = $escaper;
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

    /**
     * Method generateRandomString
     *
     * @param int $length length
     *
     * @return string randomString
     */
    protected function generateRandomString($length = 32)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Method getVisideaUrl
     *
     * @return array [reload_flag, url]
     */
    public function getVisideaUrl()
    {
        $reload = false;

        $fullwebsite = $this->parseWebsiteUrl();
        $website = $fullwebsite['host'];
        $private_token = $this->helper->getPrivateToken();
        $public_token = $this->helper->getPublicToken();

        if ($private_token == null) {
            $private_token = $this->generateRandomString();
            $public_token = $this->generateRandomString();
            $this->helper->setConfig('general', 'private_token', $private_token);
            $this->helper->setConfig('general', 'public_token', $public_token);
            $this->helper->setConfig('general', 'website', $website);
            $this->helper->flushCache();
            $reload = true;
        }

        $cronhour = $this->helper->getCronHour();
        if ($cronhour == null) {
            $this->helper->setConfig('general', 'cronhour', 1);
            $this->helper->flushCache();
            $reload = true;
        }

        $items_url = $this->helper->getItemsExportUrl();
        $users_url = $this->helper->getCustomerExportUrl();
        $interactions_url = $this->helper->getInteractionExportUrl();

        $url = $this->buildVisideaUrl(
            $website,
            $private_token,
            $public_token,
            $items_url,
            $users_url,
            $interactions_url
        );
        
        return [$reload, $url];
    }

    /**
     * Parse website URL using Magento's URL helper
     *
     * @return array
     */
    private function parseWebsiteUrl()
    {
        $baseUrl = $this->helper->getBaseUrl('/');
        $urlParts = explode('/', $baseUrl);
        $protocol = $urlParts[0];
        $host = str_replace('//', '', $urlParts[2] ?? '');
        
        return [
            'scheme' => rtrim($protocol, ':'),
            'host' => $host
        ];
    }

    /**
     * Build Visidea application URL
     *
     * @param string $website
     * @param string $private_token
     * @param string $public_token
     * @param string $items_url
     * @param string $users_url
     * @param string $interactions_url
     * @return string
     */
    private function buildVisideaUrl($website, $private_token, $public_token, $items_url, $users_url, $interactions_url)
    {
        $params = [
            'platform' => 'magento',
            'website' => $website,
            'private_token' => $private_token,
            'public_token' => $public_token,
            'items_url' => urlencode($items_url),
            'users_url' => urlencode($users_url),
            'interactions_url' => urlencode($interactions_url)
        ];
        
        return 'https://app.visidea.ai/?' . http_build_query($params);
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
