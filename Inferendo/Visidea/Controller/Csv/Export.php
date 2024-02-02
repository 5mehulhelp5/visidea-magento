<?php

/**
 * Export csv files.
 *
 * @category  Visidea
 * @package   Inferendo_Visidea
 * @author    Inferendo SRL <hello@visidea.ai>
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */

namespace Inferendo\Visidea\Controller\Csv;

use Inferendo\Visidea\Helper\Data;

/**
 * Export class
 * 
 * @category  Visidea
 * @package   Inferendo_Visidea
 * @author    Inferendo SRL <hello@visidea.ai>
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */
class Export extends \Magento\Framework\App\Action\Action
{
    protected $request;
    protected $helper;

    /**
     * Method __construct
     *
     * @param \Magento\Framework\App\Action\Contex $context context
     * @param \Magento\Framework\App\Action\Http   $request request
     * @param \Inferendo\Visidea\Helper\Data       $helper  helper
     * 
     * @return void no return
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        Data $helper
    ) {
        $this->request = $request;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Method execute
     *
     * @return void no return
     */
    public function execute()
    {
        $req = $this->getRequest();
        $data = $this->request->getParams();
        if (isset($data['token_id'])
            && $data['token_id'] == $this->helper->getConfig('general', 'private_token')
            && $this->helper->isEnabled()
        ) {
            $this->helper->createExportFolder();

            $collection = $this->helper->getQuoteCollection();
            if ($collection) {
                $dataCsv = [];
                foreach ($collection as $quote) {
                    if (count($quote->getAllVisibleItems()) > 0) {
    
                        foreach ($quote->getAllVisibleItems() as $quoteItem) {
    
                            $item = [];
                            $item['user_id'] = $quote->getCustomerId();
                            $item['item_id'] = $quoteItem->getProductId();
    
                            if ($quote->getReservedOrderId()) {
                                $item['action'] = 'purchase';
                            } else {
                                $item['action'] = 'cart';
                            }
                            $item['timestamp'] = date(DATE_ISO8601, strtotime($quote->getUpdatedAt()));
                            $dataCsv[] = $item;
                        }
    
                    }
                }
    
                $this->helper->generateInteractionCsv($dataCsv);
            }

            $customerCollection = $this->helper->getCustomerCollection();
            if ($customerCollection) {
                $dataUserCsv = [];
                foreach ($customerCollection as $customer) {
                    $userId = $customer->getId();
                    $userEmail = $customer->getEmail();
                    $userName = $customer->getFirstname();
                    $userSurname = $customer->getLastname();
                    $userAddress = '';
                    $userCity = '';
                    $userZip = '';
                    $userState = '';
                    $userCountry = '';
                    $userBirthday = $customer->getDob();
    
                    if (count($customer->getAddresses()) > 0) {
                        $i = 0;
                        foreach ($customer->getAddresses() as $address) {
                            $i++;
                            if ($i == 1) {
                                $userAddress = implode(",", $address->getStreet());
                                $userCity = $address->getCity();
                                $userZip = $address->getPostcode();
                                $userState = $address->getRegion();
                                $userCountry = $address->getCountryId();
                            }
                        }
                    }
                    $user = [];
                    $user['user_id'] = $userId;
                    $user['email'] = $userEmail;
                    $user['name'] = $userName;
                    $user['surname'] = $userSurname;
                    $user['address'] = $userAddress;
                    $user['city'] = $userCity;
                    $user['zip'] = $userZip;
                    $user['state'] = $userState;
                    $user['country'] = strtolower($userCountry);
                    $user['birthday'] = $userBirthday;
    
                    $dataUserCsv[] = $user;
                }
    
                $this->helper->generateUserCsv($dataUserCsv);
                
            }

            $productCollection = $this->helper->getProductCollection();
            if ($productCollection) {
                $dataItemCsv = [];
                foreach ($productCollection as $product) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $_product = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
                    $visibility = $_product->getAttributeText('visibility')->getText();
    
                    if ($visibility !== 'Not Visible Individually') {
    
                        $stockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
                        $categoryCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\Collection');
        
                        $images = $_product->getMediaGalleryImages();
                        $productImages = [];
                        foreach ($images as $child) {
                            $productImages[] = $child->getUrl();
                        }
                        $productStock = $stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
        
                        $simplePrice = 0;
                        $finalPrice = 0;
                        $_savingPercent = 0;
        
                        if ($_product->getTypeId() == "configurable") {
                            $_children = $_product->getTypeInstance()->getUsedProducts($_product);
                            $simplePrice = $_children[0]->getPrice();
                            $finalPrice = $_children[0]->getFinalPrice();
                            foreach ($_children as $child) {
                                $productStock += $stockState->getStockQty($child->getId(), $product->getStore()->getWebsiteId());
                            }
                        } elseif ($_product->getTypeId() == "grouped") {
                            $lowest_stock = -1;
                            $simulationPrice = 0;
                            $simulationFinalPrice = 0;
                            $associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($_product);
                            foreach ($associatedProducts as $childProduct) {
                                $simulationPrice += $childProduct->getPrice();
                                $simulationFinalPrice += $childProduct->getFinalPrice();
                                $child_stock = $stockState->getStockQty($childProduct->getId(), $product->getStore()->getWebsiteId());
                                if ($child_stock < $lowest_stock || $lowest_stock == -1)
                                    $lowest_stock = $child_stock;
                            }
                            $simplePrice = $simulationPrice;
                            $finalPrice = $simulationFinalPrice;
                            $productStock = $lowest_stock;
                        } elseif ($_product->getTypeId() == "bundle") {
                            $simplePrice = $_product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
                            $finalPrice = $_product->getPriceInfo()->getPrice('final_price')->getValue();
                        } else {
                            $simplePrice = $_product->getPrice();
                            $finalPrice = $_product->getFinalPrice();
                        }
        
                        $_categoryCollection = $categoryCollection->addAttributeToSelect('*')->addAttributeToFilter('entity_id', $product->getCategoryIds());
                        $productCategories = [];
                        if (count($_categoryCollection) > 0) {
                            foreach ($_categoryCollection as $_category) {
                                $productCategories[] = $_category->getName();
                            }
                        }
        
                        $itemId = $product->getId();
                        $itemName = $product->getName();
                        $itemBrandId = $product->getManufacturer();
                        $itemBrandName = $product->getAttributeText('manufacturer');
                        $itemDescription = $product->getDescription();
        
                        if ($finalPrice < $simplePrice) {
                            $_savingPercent = 100 - round(($finalPrice / $simplePrice) * 100);
                        }
        
                        $itemDiscount = $_savingPercent;
                        $itemPageIds = implode("|", $product->getCategoryIds());
                        $itemPageNames = implode("|", $productCategories);
        
                        $parentProduct = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($itemId);
                        if (isset($parentProduct[0])) {
                            $_parentProduct = $objectManager->create('Magento\Catalog\Model\Product')->load($parentProduct[0]);
                            $itemUrl = $_parentProduct->getProductUrl();
                        } else if ($product->isVisibleInCatalog() && $product->isVisibleInSiteVisibility()) {
                            $itemUrl = $product->getProductUrl();
                        } else {
                            $itemUrl = null;
                        }
        
                        $itemImages = implode("|", $productImages);
                        $itemStock = $productStock;
        
                        if ($itemUrl != '') {
                            $item = [];
                            $item['item_id'] = $itemId;
                            $item['name'] = $itemName;
                            $item['description'] = $itemDescription;
                            $item['brand_id'] = $itemBrandId;
                            $item['brand_name'] = $itemBrandName;
                            $item['price'] = round($simplePrice, 2);
                            $item['market_price'] = round($finalPrice, 2);
                            $item['discount'] = $itemDiscount;
                            $item['page_ids'] = $itemPageIds;
                            $item['page_names'] = $itemPageNames;
                            $item['url'] = $itemUrl;
                            $item['images'] = $itemImages;
                            $item['stock'] = $itemStock;
                            $item['gender'] = '';
                            $dataItemCsv[] = $item;
                        }

                    }

                }

                $this->helper->generateItemCsv($dataItemCsv);
            }

            echo 'Files exported successfully.';

        } else {
            echo 'Invalid token';
        }
    }
}
