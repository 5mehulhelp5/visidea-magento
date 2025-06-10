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
    protected $objectManager;

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
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
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

            $storeManager = $this->objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $stores = $storeManager->getStores();
            $primaryStoreId = $storeManager->getDefaultStoreView()->getId();

            // Prepare columns for default and additional store views
            $columns = $this->helper->getItemsColumnsHeader();
            $headers = [];
            foreach ($columns as $column) {
                $headers[] = $column;
            }
            $headers[] = 'attributes';
            $nonPrimaryStores = [];
            $hasTraslation = false;
            foreach ($stores as $store) {
                if ($store->getId() != $primaryStoreId) {
                    $nonPrimaryStores[] = $store;
                    if (!$hasTraslation)
                        $headers[] = 'translations';
                    $hasTraslation = true;
                }
            }
            $stream1->writeCsv($headers, ";");

            $productCollectionFactory = $this->objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
            do {
                $this->logger->info('Visidea - Processing page: ' . $currentPage);
                $productsCollection = $productCollectionFactory->create();
                $productsCollection->addAttributeToSelect([
                    'name', 'description', 'manufacturer', 'price', 'final_price', 'sku', 'barcode', 'mpn', 'visibility', 'media_gallery', 'url_key'
                ]);
                $productsCollection->addAttributeToFilter('type_id', ['in' => ['simple', 'virtual', 'downloadable', 'configurable']]);
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
                        $parentIds = $this->objectManager
                            ->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')
                            ->getParentIdsByChild($product->getId());
                        if (!empty($parentIds)) {
                            continue;
                        }
                    }

                    if ($product->getVisibility() == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE) {
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
                        $categoryCollection = $this->objectManager->create('Magento\Catalog\Model\ResourceModel\Category\Collection');
                        $categoryCollection->addAttributeToSelect('name')
                            ->addAttributeToFilter('entity_id', $categoryIds)
                            ->setStoreId($primaryStoreId);
                        foreach ($categoryCollection as $_category) {
                            $categoryNames[] = $_category->getName();
                        }
                    }
                    $itemPageIds = implode("|", $categoryIds);
                    $itemPageNames = implode("|", $categoryNames);

                    // Product URL
                    $itemUrl = $product->getProductUrl();

                    // Images
                    $mediaGallery = $product->getMediaGalleryImages();
                    $images = [];
                    if (!$mediaGallery || count($mediaGallery) === 0) {
                        // Fallback: reload product for images
                        $productWithImages = $this->objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
                        $mediaGallery = $productWithImages->getMediaGalleryImages();
                    }
                    if ($mediaGallery) {
                        foreach ($mediaGallery as $img) {
                            $images[] = $img->getUrl();
                        }
                    }
                    $itemImages = implode("|", $images);

                    // Stock
                    $stockQty = $product->isSaleable() ? 1 : 0;

                    // Gender (if attribute exists)
                    $itemGender = $product->getResource()->getAttribute('gender')
                        ? $product->getAttributeText('gender')
                        : '';
                    if (is_array($itemGender)) {
                        $itemGender = implode('|', $itemGender);
                    }

                    $itemData = [
                        (int)$itemId,
                        $itemName,
                        $itemDescription,
                        $itemBrandId,
                        $itemBrandName,
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

                    // Build attributes for all stores (including primary)
                    $attributes = [];
                    foreach ($stores as $store) {
                        $storeId = $store->getId();
                        $currency = $store->getCurrentCurrencyCode();

                        // Load product in store context
                        $storeProduct = $this->objectManager->create(\Magento\Catalog\Model\Product::class)
                            ->setStoreId($storeId)
                            ->load($product->getId());

                        // Check if product is assigned to the website of this store
                        $websiteIds = $storeProduct->getWebsiteIds();
                        $storeWebsiteId = $store->getWebsiteId();
                        $isInWebsite = in_array($storeWebsiteId, $websiteIds);

                        // Check if product is enabled in this store
                        $status = $storeProduct->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;

                        // Check visibility in this store
                        $visibility = $storeProduct->getVisibility() != \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE;

                        // Final visible flag: must be in website, enabled, and visible
                        $visible = $isInWebsite && $status && $visibility;

                        // Prices
                        if ($storeProduct->getTypeId() === 'configurable') {
                            $childProducts = $storeProduct->getTypeInstance()->getUsedProducts($storeProduct);
                            $minPriceSimple = null;
                            $minPriceFinal = null;
                            foreach ($childProducts as $child) {
                                $childInStore = $this->objectManager->create(\Magento\Catalog\Model\Product::class)
                                    ->setStoreId($storeId)
                                    ->load($child->getId());
                                $childPriceSimple = $childInStore->getPrice();
                                $childPriceFinal = $childInStore->getFinalPrice();
                                if ($minPriceSimple === null || ($childPriceSimple !== null && $childPriceSimple < $minPriceSimple)) {
                                    $minPriceSimple = $childPriceSimple;
                                }
                                if ($minPriceFinal === null || ($childPriceFinal !== null && $childPriceFinal < $minPriceFinal)) {
                                    $minPriceFinal = $childPriceFinal;
                                }
                            }
                            // Fallback to default store if needed
                            if ($minPriceSimple === null || $minPriceFinal === null) {
                                $defaultProduct = $this->objectManager->create(\Magento\Catalog\Model\Product::class)
                                    ->setStoreId($primaryStoreId)
                                    ->load($product->getId());
                                $childProductsDefault = $defaultProduct->getTypeInstance()->getUsedProducts($defaultProduct);
                                foreach ($childProductsDefault as $child) {
                                    $childPriceSimple = $child->getPrice();
                                    $childPriceFinal = $child->getFinalPrice();
                                    if ($minPriceSimple === null || ($childPriceSimple !== null && $childPriceSimple < $minPriceSimple)) {
                                        $minPriceSimple = $childPriceSimple;
                                    }
                                    if ($minPriceFinal === null || ($childPriceFinal !== null && $childPriceFinal < $minPriceFinal)) {
                                        $minPriceFinal = $childPriceFinal;
                                    }
                                }
                            }
                            $mSimplePrice = (float)$minPriceSimple;
                            $mMarketPrice = (float)$minPriceFinal;
                        } else {
                            // Simple, virtual, downloadable
                            $mSimplePrice = $storeProduct->getPrice();
                            $mMarketPrice = $storeProduct->getFinalPrice();
                            if ($mSimplePrice === null || $mSimplePrice === '' || $mSimplePrice === false) {
                                $defaultProduct = $this->objectManager->create(\Magento\Catalog\Model\Product::class)
                                    ->setStoreId($primaryStoreId)
                                    ->load($product->getId());
                                $mSimplePrice = $defaultProduct->getPrice();
                            }
                            if ($mMarketPrice === null || $mMarketPrice === '' || $mMarketPrice === false) {
                                if (!isset($defaultProduct)) {
                                    $defaultProduct = $this->objectManager->create(\Magento\Catalog\Model\Product::class)
                                        ->setStoreId($primaryStoreId)
                                        ->load($product->getId());
                                }
                                $mMarketPrice = $defaultProduct->getFinalPrice();
                            }
                            $mSimplePrice = (float)$mSimplePrice;
                            $mMarketPrice = (float)$mMarketPrice;
                        }

                        $discount = ($mMarketPrice < $mSimplePrice && $mSimplePrice > 0)
                            ? 100 - round(($mMarketPrice / $mSimplePrice) * 100)
                            : 0;

                        // Stock
                        $stockQty = $storeProduct->isSaleable() ? 1 : 0;

                        // URL only if visible
                        $url = '';
                        if ($visible) {
                            $url = $storeProduct->getProductUrl();
                        }

                        $attributes["visible_{$storeId}"] = $visible;
                        $attributes["currency_{$storeId}"] = $currency;
                        $attributes["price_{$storeId}"] = round($mSimplePrice, 2);
                        $attributes["market_price_{$storeId}"] = round($mMarketPrice, 2);
                        $attributes["discount_{$storeId}"] = $discount;
                        $attributes["stock_{$storeId}"] = $stockQty;
                        $attributes["url_{$storeId}"] = $url;
                    }

                    // Add attributes column
                    $itemData[] = json_encode($attributes);

                    $translations = [];
                    // Add additional language columns
                    foreach ($nonPrimaryStores as $store) {
                        $storeId = $store->getId();
                        $locale = $store->getConfig('general/locale/code'); // e.g., 'en_US'
                        $lang = substr($locale, 0, 2); // 'en'
                        
                        // Clone the product object to avoid affecting the original
                        $localizedProduct = $this->objectManager->create(\Magento\Catalog\Model\Product::class)
                            ->setStoreId($storeId)
                            ->load($product->getId());

                        // Now use $localizedProduct for localized data
                        $localizedName = $localizedProduct->getName();
                        $localizedDescription = $localizedProduct->getDescription();

                        // Category names for this store
                        $localizedPageNames = '';
                        if (!empty($categoryIds)) {
                            $categoryCollection = $this->objectManager->create('Magento\Catalog\Model\ResourceModel\Category\Collection');
                            $categoryCollection->addAttributeToSelect('name')
                                ->addAttributeToFilter('entity_id', $categoryIds)
                                ->setStoreId($storeId);
                            $productCategories = [];
                            foreach ($categoryCollection as $_category) {
                                $productCategories[] = $_category->getName();
                            }
                            $localizedPageNames = implode("|", $productCategories);
                        }

                        $localizedUrl = $localizedProduct->getProductUrl();

                        $this->addTranslation($translations, $storeId, $lang, 'name', $localizedName);
                        $this->addTranslation($translations, $storeId, $lang, 'description', $localizedDescription);
                        $this->addTranslation($translations, $storeId, $lang, 'page_names', $localizedPageNames);
                        $this->addTranslation($translations, $storeId, $lang, 'url', $localizedUrl);
                    }
                    // Add translations to the item data
                    if ($hasTraslation) {
                        $itemData[] = json_encode($translations);
                    }

                    // Quote and escape all fields
                    foreach ($itemData as $k => $v) {
                        $itemData[$k] = $this->escapeCsvField($v);
                    }
                    
                    // Add the item data to the CSV line
                    $csvLine = implode(';', $itemData) . "\n";

                    // Write to CSV
                    $stream1->write($csvLine);
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

    function addTranslation(&$translations, $storeId, $lang, $field, $value)
    {
        $obj = new \stdClass();
        $obj->store = $storeId;
        $obj->language = $lang;
        $obj->$field = $value;
        $translations[] = $obj;
    }

    private function exportInteractions($csvDirectory, $token_id)
    {
        $pageSize = 500;

        $quoteCollectionFactory = $this->objectManager->get('\Magento\Quote\Model\ResourceModel\Quote\CollectionFactory');
        $orderCollectionFactory = $this->objectManager->get('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');

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
                            $csvLine = implode(';', $interactionData) . "\n";
                            $stream2->write($csvLine);
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
                            $csvLine = implode(';', $interactionData) . "\n";
                            $stream2->write($csvLine);
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
                    $userBirthday = $customer->getDob() ? date('Y-m-d', strtotime($customer->getDob())) : '';
                    $userRegistrationDate = $customer->getCreatedAt() ? date(DATE_ISO8601, strtotime($customer->getCreatedAt())) : '';

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
                            $userEmail,
                            $userName,
                            $userSurname,
                            $userAddress,
                            $userCity,
                            $userZip,
                            $userState,
                            strtolower($userCountry),
                            $userBirthday,
                            $userRegistrationDate
                        ];
                        // Escape all fields
                        foreach ($userData as $k => $v) {
                            $userData[$k] = $this->escapeCsvField($v);
                        }
                        $csvLine = implode(';', $userData) . "\n";
                        $stream3->write($csvLine);
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

    // Utility function to escape double quotes with backslash
    private function escapeCsvField($value) {
        if (is_null($value)) {
            return '""';
        }
        if (is_numeric($value) || is_bool($value)) {
            return $value;
        }
        // Convert to string
        $value = (string)$value;
        // Escape backslashes with another backslash
        $value = str_replace('\\', '\\\\', $value);
        // Escape double quotes with backslash
        $value = str_replace('"', '\\"', $value);
        // Wrap in double quotes
        return '"' . $value . '"';
    }

}
