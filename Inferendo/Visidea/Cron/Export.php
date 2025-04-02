<?php

/**
 * Cron to export csv files.
 *
 * @category  Visidea
 * @package   Inferendo_Visidea
 * @author    Inferendo SRL <hello@visidea.ai>
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */

namespace Inferendo\Visidea\Cron;

use Inferendo\Visidea\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

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
class Export
{

    protected $request;
    protected $helper;
    protected $directory;
    protected $directoryList;
    protected $fileIo;
    protected $logger;

    /**
     * Method __construct
     *
     * @param \Inferendo\Visidea\Helper\Data $helper helper
     * 
     * @return void no return
     */
    public function __construct(
        Data $helper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $fileIo,
        LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::PUB);
        $this->directoryList = $directoryList;
        $this->fileIo = $fileIo;
        $this->logger = $logger;
    }

    /**
     * Method execute
     *
     * @return Export return a reference to object
     */
    public function execute()
    {

        $this->logger->info('Visidea - cron started');

        $this->helper->createExportFolder();
        $pubDirectory = $this->directoryList->getPath(DirectoryList::PUB);
        $csvDirectory = $pubDirectory . '/media/visidea/csv/';

        $token_id = $this->helper->getConfig('general', 'private_token');

        $productsCollection = $this->helper->getItemsCollection();
        if ($productsCollection) {
            $fileName = 'items_' . $token_id . '.csv';
            $tempFileName = 'items_' . $token_id . '.temp';
            $hashFileName = 'items_' . $token_id . '.hash';
            $filePath = $csvDirectory . $fileName;
            $tempFilePath = $csvDirectory . $tempFileName;
            $hashFilePath = $csvDirectory . $hashFileName;

            $this->logger->info('Visidea - File path: ' . $filePath);
            $this->logger->info('Visidea - Temp file path: ' . $tempFilePath);

            try {

                $stream1 = $this->directory->openFile($tempFilePath, 'w+');
                $stream1->lock();

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                $stores = $storeManager->getStores();
                $primaryStoreId = $storeManager->getDefaultStoreView()->getId();

                // Determine non-primary store views
                $nonPrimaryStores = array();
                foreach ($stores as $store) {
                    if ($store->getId() != $primaryStoreId)
                        $nonPrimaryStores[] = $store;
                }

                $columns = $this->helper->getItemsColumnsHeader();
                $headers = [];
                foreach ($columns as $column) {
                    $headers[] = $column;
                }
                foreach ($nonPrimaryStores as $store) {
                    $headers[] = 'name_' . $store->getCode();
                    $headers[] = 'description_' . $store->getCode();
                    $headers[] = 'page_names_' . $store->getCode();
                    $headers[] = 'url_' . $store->getCode();
                }
                $stream1->writeCsv($headers, ";");

                foreach ($productsCollection as $product) {
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
                        $itemBarcode = $_product->getData('barcode'); // Assuming 'barcode' is the attribute code for the barcode
                        $itemMpn = $_product->getData('mpn'); // Assuming 'mpn' is the attribute code for the MPN
                        $itemSku = $product->getSku(); // Get the SKU

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
                            // Add name, description, page names, and URL for each non-primary language
                            foreach ($nonPrimaryStores as $store) {
                                $storeId = $store->getId();
                                // Reload the product for the specific store view
                                $localizedProduct = $objectManager->create('Magento\Catalog\Model\Product')->setStoreId($storeId)->load($product->getId());
                                $item['name_' . $store->getCode()] = $localizedProduct->getName();
                                $item['description_' . $store->getCode()] = $localizedProduct->getDescription();

                                // Retrieve categories for this store view
                                $_categoryCollection = $categoryCollection->addAttributeToSelect('*')->addAttributeToFilter('entity_id', $product->getCategoryIds())->setStoreId($storeId);
                                $productCategories = [];
                                if (count($_categoryCollection) > 0) {
                                    foreach ($_categoryCollection as $_category) {
                                        $productCategories[] = $_category->getName();
                                    }
                                }
                                $item['page_names_' . $store->getCode()] = implode("|", $productCategories);
                                $item['url_' . $store->getCode()] = $localizedProduct->getProductUrl();
                            }

                            if (!empty($itemId)) {
                                $itemData = [];
                                $itemData[] = (int)$itemId;
                                $itemData[] = str_replace('"', '\"', $itemName);
                                $itemData[] = str_replace('"', '\"', $itemDescription);
                                $itemData[] = $itemBrandId;
                                $itemData[] = str_replace('"', '\"', $itemBrandName);
                                $itemData[] = round($simplePrice, 2);
                                $itemData[] = round($finalPrice, 2);
                                $itemData[] = $itemDiscount;
                                $itemData[] = $itemPageIds;
                                $itemData[] = str_replace('"', '\"', $itemPageNames);
                                $itemData[] = $itemUrl;
                                $itemData[] = $itemImages;
                                $itemData[] = $itemStock;
                                $itemData[] = '';
                                $itemData[] = $itemBarcode;
                                $itemData[] = $itemMpn;
                                $itemData[] = $itemSku;
                                foreach ($nonPrimaryStores as $store) {
                                    $storeId = $store->getId();
                                    // Reload the product for the specific store view
                                    $localizedProduct = $objectManager->create('Magento\Catalog\Model\Product')->setStoreId($storeId)->load($product->getId());
                                    $itemData[] = $localizedProduct->getName();
                                    $itemData[] = $localizedProduct->getDescription();
        
                                    // Retrieve categories for this store view
                                    $_categoryCollection = $categoryCollection->addAttributeToSelect('*')->addAttributeToFilter('entity_id', $product->getCategoryIds())->setStoreId($storeId);
                                    $productCategories = [];
                                    if (count($_categoryCollection) > 0) {
                                        foreach ($_categoryCollection as $_category) {
                                            $productCategories[] = $_category->getName();
                                        }
                                    }
                                    $itemData[] = implode("|", $productCategories);
                                    $itemData[] = $localizedProduct->getProductUrl();
                                }
        
                                // $this->logger->info(json_encode($itemData));
                                $stream1->writeCsv($itemData, ";");
                            }

                        }

                    }

                }

                // Close and unlock the file
                $stream1->unlock();
                $stream1->close();

                // Rename the temporary file to the final file name
                if (!rename($tempFilePath, $filePath)) {
                    $this->logger->error('Visidea - Failed to rename the file from ' . $tempFileName . ' to ' . $fileName);
                    throw new \Exception('Visidea - Failed to rename the file from ' . $tempFileName . ' to ' . $fileName);
                }

                file_put_contents($hashFilePath, hash_file('sha256', $filePath));

                $this->logger->info('Visidea - File exported and renamed successfully: ' . $filePath);
            } catch (\Exception $e) {
                $this->logger->error('Visidea - Error exporting items: ' . $e->getMessage());

                // Attempt to clean up the temporary file if an error occurred
                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }
            }

        } else {
            $this->logger->info('Visidea - No items found to export');
        }

        $cartsCollection = $this->helper->getCartsCollection();
        $ordersCollection = $this->helper->getOrdersCollection();

        $this->logger->info('Visidea - Processing carts: ' . count($cartsCollection));
        $this->logger->info('Visidea - Processing orders: ' . count($ordersCollection));
        if (count($cartsCollection) > 0 || count($ordersCollection) > 0) {
            $fileName = 'interactions_' . $token_id . '.csv';
            $tempFileName = 'interactions_' . $token_id . '.temp';
            $hashFileName = 'interactions_' . $token_id . '.hash';
            $filePath = $csvDirectory . $fileName;
            $tempFilePath = $csvDirectory . $tempFileName;
            $hashFilePath = $csvDirectory . $hashFileName;

            try {
                $stream2 = $this->directory->openFile($tempFilePath, 'w+');
                $stream2->lock();

                $columns = $this->helper->getInteractionsColumnsHeader();
                $stream2->writeCsv($columns, ";");

                foreach ($cartsCollection as $interaction) {
                    $interactionItems = $interaction->getAllVisibleItems();
                    $this->logger->info('Visidea - Processing interaction', [
                        'interaction_id' => $interaction->getId(),
                        'items_count' => count($interactionItems)
                    ]);

                    if (count($interactionItems) > 0) {
                        foreach ($interactionItems as $interactionItem) {
                            $this->logger->debug('Visidea - Processing cart item', [
                                'product_id' => $interactionItem->getProductId()
                            ]);

                            if (!empty($interactionItem->getProductId())) {
                                $interactionData = [
                                    (int)$interaction->getCustomerId(),
                                    $interactionItem->getProductId(),
                                    'cart',
                                    number_format((float)$interactionItem->getPrice(), 2),
                                    (int)$interactionItem->getQty(),
                                    date(DATE_ISO8601, strtotime($interaction->getUpdatedAt()))
                                ];

                                $stream2->writeCsv($interactionData, ";");
                            }
                        }
                    }
                }

                foreach ($ordersCollection as $interaction) {
                    $interactionItems = $interaction->getAllVisibleItems();
                    $this->logger->info('Visidea - Processing interaction', [
                        'interaction_id' => $interaction->getId(),
                        'items_count' => count($interactionItems)
                    ]);

                    if (count($interactionItems) > 0) {
                        foreach ($interactionItems as $interactionItem) {
                            $this->logger->debug('Visidea - Processing order item', [
                                'product_id' => $interactionItem->getProductId()
                            ]);

                            if (!empty($interactionItem->getProductId())) {
                                $interactionData = [
                                    (int)$interaction->getCustomerId(),
                                    $interactionItem->getProductId(),
                                    'purchase',
                                    number_format((float)$interactionItem->getPrice(), 2),
                                    (int)$interactionItem->getQtyOrdered(),
                                    date(DATE_ISO8601, strtotime($interaction->getUpdatedAt()))
                                ];

                                $stream2->writeCsv($interactionData, ";");
                            }
                        }
                    }
                }

                $stream2->unlock();
                $stream2->close();

                if (!rename($tempFilePath, $filePath)) {
                    $this->logger->error('Visidea - Failed to rename the file from ' . $tempFileName . ' to ' . $fileName);
                    throw new \Exception('Visidea - Failed to rename the file from ' . $tempFileName . ' to ' . $fileName);
                }

                file_put_contents($hashFilePath, hash_file('sha256', $filePath));
                $this->logger->info('Visidea - File exported and renamed successfully: ' . $filePath);
            } catch (\Exception $e) {
                $this->logger->error('Visidea - Error exporting interactions: ' . $e->getMessage());
                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }
            }
        } else {
            $this->logger->info('Visidea - No interactions found to export');
        }        
        

        $customerCollection = $this->helper->getUsersCollection();
        if ($customerCollection) {
            $fileName = 'users_' . $token_id . '.csv';
            $tempFileName = 'users_' . $token_id . '.temp';
            $hashFileName = 'users_' . $token_id . '.hash';
            $filePath = $csvDirectory . $fileName;
            $tempFilePath = $csvDirectory . $tempFileName;
            $hashFilePath = $csvDirectory . $hashFileName;

            try {
                $stream3 = $this->directory->openFile($tempFilePath, 'w+');
                $stream3->lock();

                $columns = $this->helper->getUsersColumnsHeader();
                foreach ($columns as $column) {
                    $header[] = $column;
                }
                $stream3->writeCsv($header, ";");

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
                    $userBirthday = date('Y-m-d H:i:s', strtotime($customer->getDob()));
                    $userRegistrationDate = date('Y-m-d H:i:s', strtotime($customer->getCreatedAt()));

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

                    if (!empty($userId)) {
                        $userData = [];
                        $userData[] = (int)$userId;
                        $userData[] = $userEmail;
                        $userData[] = str_replace('"', '\"', $userName);
                        $userData[] = str_replace('"', '\"', $userSurname);
                        $userData[] = str_replace('"', '\"', $userAddress);
                        $userData[] = str_replace('"', '\"', $userCity);
                        $userData[] = str_replace('"', '\"', $userZip);
                        $userData[] = str_replace('"', '\"', $userState);
                        $userData[] = strtolower($userCountry);
                        $userData[] = $userBirthday;
                        $userData[] = $userRegistrationDate;
                        $stream3->writeCsv($userData, ";");
                    }

                }

                // Close and unlock the file
                $stream3->unlock();
                $stream3->close();

                // Rename the temporary file to the final file name
                if (!rename($tempFilePath, $filePath)) {
                    $this->logger->error('Visidea - Failed to rename the file from ' . $tempFileName . ' to ' . $fileName);
                    throw new \Exception('Visidea - Failed to rename the file from ' . $tempFileName . ' to ' . $fileName);
                }

                file_put_contents($hashFilePath, hash_file('sha256', $filePath));

                $this->logger->info('Visidea - File exported and renamed successfully: ' . $filePath);
            } catch (\Exception $e) {
                $this->logger->error('Visidea - Error exporting users: ' . $e->getMessage());

                // Attempt to clean up the temporary file if an error occurred
                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }
            }
            
        } else {
            $this->logger->info('Visidea - No users found to export');
        }

        $this->logger->info('Visidea - cron ended');

        return $this;

    }

}
