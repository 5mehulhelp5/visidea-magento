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
use stdClass;

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

    public function exportItemsCron()
    {
        $cronhour = (int)$this->helper->getConfig('general', 'cronhour');
        $lastRun = $this->helper->getConfig('general', 'cron_items_last_run');
        $lastRunTs = $lastRun ? strtotime($lastRun) : 0;
        $now = time();

        $this->logger->info('Visidea - items export cron debug: cronhour=' . $cronhour . ', lastRun=' . $lastRun . ', now=' . date('Y-m-d H:i', $now));

        if ($lastRunTs === 0 || $this->shouldRunCronByMinutes($lastRunTs, $now, $cronhour)) {
            $this->logger->info('Visidea - items export cron started');
            $this->helper->createExportFolder();
            $pubDirectory = $this->directoryList->getPath(DirectoryList::PUB);
            $csvDirectory = $pubDirectory . '/media/visidea/csv/';
            $token_id = $this->helper->getConfig('general', 'private_token');
            $this->exportItems($csvDirectory, $token_id);
            $this->logger->info('Visidea - items export cron ended');
            // Save current datetime as last run
            $this->helper->setConfig('general', 'cron_items_last_run', date('Y-m-d H:i', $now));
        } else {
            $this->logger->info('Visidea - items export cron skipped (not enough time passed)');
        }
    }

    private function shouldRunCronByMinutes($lastRunTs, $now, $cronhour)
    {
        $diffMinutes = ($now - $lastRunTs) / 60;
        $this->logger->info('Visidea - shouldRunCronByMinutes debug: lastRunTs=' . $lastRunTs . ', now=' . $now . ', diffMinutes=' . $diffMinutes . ', cronhour=' . $cronhour);
        return $diffMinutes >= ($cronhour * 60);
    }

    public function exportInteractionsCron()
    {
        $cronhour = (int)$this->helper->getConfig('general', 'cronhour');
        $lastRun = $this->helper->getConfig('general', 'cron_interactions_last_run');
        $lastRunTs = $lastRun ? strtotime($lastRun) : 0;
        $now = time();

        $this->logger->info('Visidea - interactions export cron debug: cronhour=' . $cronhour . ', lastRun=' . $lastRun . ', now=' . date('Y-m-d H:i', $now));

        if ($lastRunTs === 0 || $this->shouldRunCronByMinutes($lastRunTs, $now, $cronhour)) {
            $this->logger->info('Visidea - interactions export cron started');
            $this->helper->createExportFolder();
            $pubDirectory = $this->directoryList->getPath(DirectoryList::PUB);
            $csvDirectory = $pubDirectory . '/media/visidea/csv/';
            $token_id = $this->helper->getConfig('general', 'private_token');
            $this->exportInteractions($csvDirectory, $token_id);
            $this->logger->info('Visidea - interactions export cron ended');
            // Save current datetime as last run
            $this->helper->setConfig('general', 'cron_interactions_last_run', date('Y-m-d H:i', $now));
        } else {
            $this->logger->info('Visidea - interactions export cron skipped (not enough time passed)');
        }
    }

    public function exportUsersCron()
    {
        $cronhour = (int)$this->helper->getConfig('general', 'cronhour');
        $lastRun = $this->helper->getConfig('general', 'cron_users_last_run');
        $lastRunTs = $lastRun ? strtotime($lastRun) : 0;
        $now = time();

        $this->logger->info('Visidea - users export cron debug: cronhour=' . $cronhour . ', lastRun=' . $lastRun . ', now=' . date('Y-m-d H:i', $now));

        if ($lastRunTs === 0 || $this->shouldRunCronByMinutes($lastRunTs, $now, $cronhour)) {
            $this->logger->info('Visidea - users export cron started');
            $this->helper->createExportFolder();
            $pubDirectory = $this->directoryList->getPath(DirectoryList::PUB);
            $csvDirectory = $pubDirectory . '/media/visidea/csv/';
            $token_id = $this->helper->getConfig('general', 'private_token');
            $this->exportUsers($csvDirectory, $token_id);
            $this->logger->info('Visidea - users export cron ended');
            // Save current datetime as last run
            $this->helper->setConfig('general', 'cron_users_last_run', date('Y-m-d H:i', $now));
        } else {
            $this->logger->info('Visidea - users export cron skipped (not enough time passed)');
        }
    }

    private function exportItems($csvDirectory, $token_id)
    {
        $pageSize = 500;
        $currentPage = 1;

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

            // Prepare columns for default and additional store views
            $columns = $this->helper->getItemsColumnsHeader();
            $headers = [];
            foreach ($columns as $column) {
                $headers[] = $column;
            }
            $nonPrimaryStores = [];
            $hasTraslation = false;
            foreach ($stores as $store) {
                if ($store->getId() != $primaryStoreId) {
                    $nonPrimaryStores[] = $store;
                    if (!$hasTraslation)
                        $headers[] = 'translations';
                    $hasTraslation = true;
                    // $headers[] = 'name_' . $store->getCode();
                    // $headers[] = 'description_' . $store->getCode();
                    // $headers[] = 'page_names_' . $store->getCode();
                    // $headers[] = 'url_' . $store->getCode();
                }
            }
            $stream1->writeCsv($headers, ";");

            $productCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
            do {
                $this->logger->info('Visidea - Processing page: ' . $currentPage);
                $productsCollection = $productCollectionFactory->create();
                $productsCollection->addAttributeToSelect([
                    'name', 'description', 'manufacturer', 'price', 'final_price', 'sku', 'barcode', 'mpn', 'visibility', 'media_gallery', 'url_key'
                ]);
                $productsCollection->setStoreId($primaryStoreId);
                $productsCollection->setPageSize($pageSize);
                $productsCollection->setCurPage($currentPage);

                $itemsOnPage = count($productsCollection);

                if ($itemsOnPage === 0) {
                    break;
                }

                foreach ($productsCollection as $product) {
                    // Only export simple, virtual, downloadable, and configurable products
                    if (!in_array($product->getTypeId(), ['simple', 'virtual', 'downloadable', 'configurable'])) {
                        continue;
                    }

                    // If the simple product is a child of a configurable, skip it (to avoid duplicate variants)
                    if ($product->getTypeId() === 'simple') {
                        $parentIds = $objectManager
                            ->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')
                            ->getParentIdsByChild($product->getId());
                        if (!empty($parentIds)) {
                            continue;
                        }
                    }

                    $visibility = $product->getAttributeText('visibility');
                    if ($visibility === 'Not Visible Individually') {
                        continue;
                    }

                    $itemId = $product->getId();
                    $itemName = $product->getName();
                    $itemBrandId = $product->getManufacturer();
                    $itemBrandName = $product->getAttributeText('manufacturer');
                    $itemDescription = $product->getDescription();
                    $itemSku = $product->getSku();
                    $itemBarcode = $product->getData('barcode');
                    $itemMpn = $product->getData('mpn');

                    if ($product->getTypeId() === 'configurable') {
                        $childProducts = $product->getTypeInstance()->getUsedProducts($product);
                        $minPriceSimple = null;
                        $minPriceFinal = null;
                        foreach ($childProducts as $child) {
                            $childPriceSimple = $child->getPrice();
                            $childPriceFinal = $child->getFinalPrice();
                            if ($minPriceSimple === null || $childPriceSimple < $minPriceSimple) {
                                $minPriceSimple = $childPriceSimple;
                            }
                            if ($minPriceFinal === null || $childPriceFinal < $minPriceFinal) {
                                $minPriceFinal = $childPriceFinal;
                            }
                        }
                        $simplePrice = $minPriceSimple !== null ? $minPriceSimple : $product->getPrice();
                        $finalPrice = $minPriceFinal !== null ? $minPriceFinal : $product->getFinalPrice();
                    } else {
                        $simplePrice = $product->getPrice();
                        $finalPrice = $product->getFinalPrice();
                    }

                    $itemDiscount = ($finalPrice < $simplePrice && $simplePrice > 0)
                        ? 100 - round(($finalPrice / $simplePrice) * 100)
                        : 0;

                    // Category IDs and names
                    $categoryIds = $product->getCategoryIds();
                    $categoryNames = [];
                    if (!empty($categoryIds)) {
                        $categoryCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\Collection');
                        $categoryCollection->addAttributeToSelect('name')
                            ->addAttributeToFilter('entity_id', $categoryIds)
                            ->setStoreId($primaryStoreId);
                        foreach ($categoryCollection as $_category) {
                            $categoryNames[] = $_category->getName();
                        }
                    }
                    $itemPageIds = implode("|", $categoryIds);
                    $itemPageNames = str_replace('"', '\"', implode("|", $categoryNames));

                    // Product URL
                    // $product = $objectManager->create(\Magento\Catalog\Model\Product::class)
                    //     ->setStoreId($primaryStoreId)
                    //     ->load($product->getId());
                    $itemUrl = $product->getProductUrl();

                    // Images
                    $mediaGallery = $product->getMediaGalleryImages();
                    $images = [];
                    if (!$mediaGallery || count($mediaGallery) === 0) {
                        // Fallback: reload product for images
                        $productWithImages = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
                        $mediaGallery = $productWithImages->getMediaGalleryImages();
                    }
                    if ($mediaGallery) {
                        foreach ($mediaGallery as $img) {
                            $images[] = $img->getUrl();
                        }
                    }
                    $itemImages = implode("|", $images);

                    // Stock
                    $stockQty = 0;
                    try {
                        $stockRegistry = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
                        if ($product->getTypeId() === 'configurable') {
                            // Sum stock of all variants
                            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
                            foreach ($childProducts as $child) {
                                $childStockItem = $stockRegistry->getStockItem($child->getId());
                                $stockQty += $childStockItem ? (int)$childStockItem->getQty() : 0;
                            }
                        } else {
                            // Simple product stock
                            $stockItem = $stockRegistry->getStockItem($product->getId());
                            $stockQty = $stockItem ? (int)$stockItem->getQty() : 0;
                        }
                    } catch (\Exception $e) {
                        $stockQty = 0;
                    }

                    // Gender (if attribute exists)
                    $itemGender = $product->getResource()->getAttribute('gender')
                        ? $product->getAttributeText('gender')
                        : '';
                    if (is_array($itemGender)) {
                        $itemGender = implode('|', $itemGender);
                    }
                    $itemGender = str_replace('"', '\"', $itemGender);

                    $itemData = [
                        (int)$itemId,
                        str_replace('"', '\"', $itemName ?? ''),
                        str_replace('"', '\"', $itemDescription ?? ''),
                        $itemBrandId,
                        str_replace('"', '\"', $itemBrandName ?? ''),
                        round($simplePrice, 2),
                        round($finalPrice, 2),
                        $itemDiscount,
                        $itemPageIds,
                        $itemPageNames,
                        $itemUrl,
                        $itemImages,
                        $stockQty,
                        $itemGender,
                        $itemBarcode,
                        $itemMpn,
                        $itemSku
                    ];

                    $translations = [];
                    // Add additional language columns
                    foreach ($nonPrimaryStores as $store) {
                        $storeId = $store->getId();
                        $locale = $store->getConfig('general/locale/code'); // e.g., 'en_US'
                        $lang = substr($locale, 0, 2); // 'en'
                        
                        // Clone the product object to avoid affecting the original
                        $localizedProduct = $objectManager->create(\Magento\Catalog\Model\Product::class)
                            ->setStoreId($storeId)
                            ->load($product->getId());

                        // Now use $localizedProduct for localized data
                        $localizedName = str_replace('"', '\"', $localizedProduct->getName());
                        $localizedDescription = str_replace('"', '\"', $localizedProduct->getDescription());

                        // Category names for this store
                        $localizedPageNames = '';
                        if (!empty($categoryIds)) {
                            $categoryCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\Collection');
                            $categoryCollection->addAttributeToSelect('name')
                                ->addAttributeToFilter('entity_id', $categoryIds)
                                ->setStoreId($storeId);
                            $productCategories = [];
                            foreach ($categoryCollection as $_category) {
                                $productCategories[] = $_category->getName();
                            }
                            $localizedPageNames = str_replace('"', '\"', implode("|", $productCategories));
                        }

                        $localizedUrl = $localizedProduct->getProductUrl();

                        $translation = new stdClass();
                        $translation->store = $storeId;
                        $translation->language = $lang;
                        $translation->name = $localizedName;
                        $translations[] = $translation;
                        $translation = new stdClass();
                        $translation->store = $storeId;
                        $translation->language = $lang;
                        $translation->description = $localizedDescription;
                        $translations[] = $translation;
                        $translation = new stdClass();
                        $translation->store = $storeId;
                        $translation->language = $lang;
                        $translation->page_names = $localizedPageNames;
                        $translations[] = $translation;
                        $translation = new stdClass();
                        $translation->store = $storeId;
                        $translation->language = $lang;
                        $translation->url = $localizedUrl;
                        $translations[] = $translation;

                        // $itemData[] = $localizedName;
                        // $itemData[] = $localizedDescription;
                        // $itemData[] = $localizedPageNames;
                        // $itemData[] = $localizedUrl;
                    }
                    // Add translations to the item data
                    if ($hasTraslation) {
                        $itemData[] = json_encode($translations);
                    }

                    $stream1->writeCsv($itemData, ";");
                }

                $currentPage++;
            } while ($itemsOnPage === $pageSize);

            $stream1->unlock();
            $stream1->close();

            if (!rename($tempFilePath, $filePath)) {
                $this->logger->error('Visidea - Failed to rename the file from ' . $tempFileName . ' to ' . $fileName);
                throw new \Exception('Visidea - Failed to rename the file from ' . $tempFileName . ' to ' . $fileName);
            }

            file_put_contents($hashFilePath, hash_file('sha256', $filePath));
            $this->logger->info('Visidea - File exported and renamed successfully: ' . $filePath);
        } catch (\Exception $e) {
            $this->logger->error('Visidea - Error exporting items: ' . $e->getMessage());
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
        }
    }

    private function exportInteractions($csvDirectory, $token_id)
    {
        $pageSize = 500;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $quoteCollectionFactory = $objectManager->get('\Magento\Quote\Model\ResourceModel\Quote\CollectionFactory');
        $orderCollectionFactory = $objectManager->get('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');

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

            // Paginate through carts (quotes)
            $currentPage = 1;
            do {
                $cartsCollection = $quoteCollectionFactory->create();
                $cartsCollection->addFieldToSelect(['entity_id', 'customer_id', 'updated_at']);
                $cartsCollection->addFieldToFilter('customer_id', ['neq' => 'NULL']);
                $cartsCollection->setPageSize($pageSize);
                $cartsCollection->setCurPage($currentPage);

                $itemsOnPage = count($cartsCollection);

                if ($itemsOnPage === 0) {
                    break;
                }

                foreach ($cartsCollection as $interaction) {
                    $interactionItems = $interaction->getAllVisibleItems();
                    foreach ($interactionItems as $interactionItem) {
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
                unset($cartsCollection);
                $currentPage++;
            } while ($itemsOnPage === $pageSize);

            // Paginate through orders
            $currentPage = 1;
            do {
                $ordersCollection = $orderCollectionFactory->create();
                $ordersCollection->addFieldToSelect(['entity_id', 'customer_id', 'updated_at']);
                $ordersCollection->addFieldToFilter('customer_id', ['neq' => 'NULL']);
                $ordersCollection->setPageSize($pageSize);
                $ordersCollection->setCurPage($currentPage);

                $itemsOnPage = count($ordersCollection);

                if ($itemsOnPage === 0) {
                    break;
                }

                foreach ($ordersCollection as $interaction) {
                    $interactionItems = $interaction->getAllVisibleItems();
                    foreach ($interactionItems as $interactionItem) {
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
                unset($ordersCollection);
                $currentPage++;
            } while ($itemsOnPage === $pageSize);

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
    }

    private function exportUsers($csvDirectory, $token_id)
    {
        $pageSize = 500;
        $currentPage = 1;

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
            $header = [];
            foreach ($columns as $column) {
                $header[] = $column;
            }
            $stream3->writeCsv($header, ";");

            do {
                $customerCollection = $this->helper->getUsersCollection();
                $customerCollection->addAttributeToSelect([
                    'firstname', 'lastname', 'email', 'dob', 'created_at'
                ]);
                $customerCollection->setPageSize($pageSize);
                $customerCollection->setCurPage($currentPage);

                $itemsOnPage = count($customerCollection);

                if ($itemsOnPage === 0) {
                    break;
                }

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
                    $userBirthday = $customer->getDob() ? date('Y-m-d H:i:s', strtotime($customer->getDob())) : '';
                    $userRegistrationDate = $customer->getCreatedAt() ? date('Y-m-d H:i:s', strtotime($customer->getCreatedAt())) : '';

                    // Use only default billing address if available
                    $address = $customer->getDefaultBillingAddress();
                    if ($address) {
                        $userAddress = implode(",", $address->getStreet());
                        $userCity = $address->getCity();
                        $userZip = $address->getPostcode();
                        $userState = $address->getRegion();
                        $userCountry = $address->getCountryId();
                    }

                    if (!empty($userId)) {
                        $userData = [
                            (int)$userId,
                            str_replace('"', '\"', $userEmail),
                            str_replace('"', '\"', $userName),
                            str_replace('"', '\"', $userSurname),
                            str_replace('"', '\"', $userAddress),
                            str_replace('"', '\"', $userCity),
                            str_replace('"', '\"', $userZip),
                            str_replace('"', '\"', $userState),
                            strtolower($userCountry),
                            $userBirthday,
                            $userRegistrationDate
                        ];
                        $stream3->writeCsv($userData, ";");
                    }
                }

                unset($customerCollection);
                $currentPage++;
            } while ($itemsOnPage === $pageSize);

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
    }

}
