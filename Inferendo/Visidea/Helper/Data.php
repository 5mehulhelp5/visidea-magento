<?php

/**
 * Manage data.
 *
 * @category  Visidea
 * @package   Inferendo_Visidea
 * @author    Inferendo SRL <hello@visidea.ai>
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */

namespace Inferendo\Visidea\Helper;

use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir;
use Magento\Framework\File\Csv;

/**
 * Data class
 * 
 * @category  Visidea
 * @package   Inferendo_Visidea
 * @author    Inferendo SRL <hello@visidea.ai>
 * @copyright 2022 Inferendo SRL
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 * @link      https://visidea.ai/
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $logger;
    protected $storeManager;
    protected $orderManagement;
    protected $objectManager;
    protected $scopeConfig;
    protected $file;
    protected $quoteFactory;
    protected $quoteModel;
    protected $quoteManagement;
    protected $dateTime;
    protected $csvProcessor;
    protected $fileFactory;
    protected $directory;
    protected $productCollectionFactory;
    protected $customer;
    protected $customerFactory;
    private $_httpContext;

    const MODULE_ENABLED = 'inferendo_visidea/general/enable';

    /**
     * Method __construct
     *
     * @param \Magento\Framework\App\Helper\Context                          $context                  context
     * @param \Magento\Store\Model\StoreManagerInterface                     $storeManager             storeManager
     * @param OrderManagementInterface                                       $orderManagement          orderManagement
     * @param \Magento\Framework\ObjectManagerInterface                      $objectManager            objectManager
     * @param ScopeConfigInterface                                           $scopeConfig              scopeConfig
     * @param \Magento\Framework\Filesystem\Io\File                          $file                     file
     * @param \Magento\Framework\Filesystem\DirectoryList                    $dir                      dir
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory     $quoteFactory             quoteFactory
     * @param \Magento\Quote\Model\Quote                                     $quoteModel               quoteModel
     * @param \Magento\Quote\Model\QuoteManagement                           $quoteManagement          quoteManagement
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                    $dateTime                 dateTime
     * @param \Magento\Framework\App\Response\Http\FileFactory               $fileFactory              fileFactory
     * @param \Magento\Framework\Filesystem                                  $filesystem               filesystem
     * @param Csv                                                            $csvProcessor             csvProcessor
     * @param \Magento\Framework\App\Http\Context                            $httpContext              httpContext
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory productCollectionFactory
     * @param \Magento\Customer\Model\CustomerFactory                        $customerFactory          customerFactory
     * @param \Magento\Customer\Model\Customer                               $customers                customers
     * 
     * @return void no return
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        OrderManagementInterface $orderManagement,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteFactory,
        \Magento\Quote\Model\Quote $quoteModel,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        Csv $csvProcessor,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer $customers
    ) {
        $this->storeManager = $storeManager;
        $this->logger = $context->getLogger();
        $this->orderManagement = $orderManagement;
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->file = $file;
        $this->dir = $dir;
        $this->quoteFactory = $quoteFactory;
        $this->quoteModel = $quoteModel;
        $this->quoteManagement = $quoteManagement;
        $this->dateTime = $dateTime;
        $this->csvProcessor = $csvProcessor;
        $this->fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::PUB);
        $this->_httpContext = $httpContext;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->customerFactory = $customerFactory;
        $this->customer = $customers;
        parent::__construct($context);
    }

    /**
     * Method isEnable
     *
     * @return int return if the module if enabled
     */
    public function isEnable()
    {
        return 1;
    }

    /**
     * Method getConfig
     *
     * @param string $group   group
     * @param string $field   field
     * @param int    $storeId storeId
     *
     * @return array         return the confif
     */
    public function getConfig($group, $field, $storeId = 0)
    {
        return $this->scopeConfig->getValue(
            'inferendo_visidea/' . $group . '/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Method getReturnUrl
     *
     * @param string $path path
     *
     * @return string         return full path
     */
    public function getReturnUrl($path)
    {
        return $this->storeManager->getStore()->getBaseUrl() . $path;
    }

    /**
     * Method getCronUrl
     *
     * @return string         return full path
     */
    public function getCronUrl()
    {
        $token_id = $this->getConfig('general', 'private_token');
        return $this->getReturnUrl('visidea/csv/export/token_id/' . $token_id);
    }

    /**
     * Method getInteractionExportUrl
     *
     * @return string         return url
     */
    public function getInteractionExportUrl()
    {
        $token_id = $this->getConfig('general', 'private_token');
        return $this->getReturnUrl('pub/media/visidea/csv/interactions_' . $token_id . '.csv');
    }

    /**
     * Method getItemsExportUrl
     *
     * @return string         return url
     */
    public function getItemsExportUrl()
    {
        $token_id = $this->getConfig('general', 'private_token');
        return $this->getReturnUrl('pub/media/visidea/csv/items_' . $token_id . '.csv');
    }

    /**
     * Method getCustomerExportUrl
     *
     * @return string         return url
     */
    public function getCustomerExportUrl()
    {
        $token_id = $this->getConfig('general', 'private_token');
        return $this->getReturnUrl('pub/media/visidea/csv/users_' . $token_id . '.csv');
    }

    /**
     * Method getUrl
     *
     * @param string $route  route
     * @param array  $params params
     *
     * @return string               return url
     */
    public function getUrl($route, $params = [])
    {
        return $this->_getUrl($route, $params);
    }

    /**
     * Method getQuoteCollection
     *
     * @return array return quote
     */
    public function getQuoteCollection()
    {
        $collection = $this->quoteFactory->create()->addFieldToSelect('*');
        $collection->addFieldToFilter('customer_id', ['neq' => 'NULL']);

        return $collection;
    }

    /**
     * Method getProductCollection
     *
     * @return array return collection
     */
    public function getProductCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        return $collection;
    }

    /**
     * Method getCustomerCollection
     *
     * @return array return collection
     */
    public function getCustomerCollection()
    {
        return $this->customer->getCollection()
            ->addAttributeToSelect("*")
            ->load();
    }

    /**
     * Method generateInteractionCsv
     *
     * @param array $data data
     *
     * @return void
     */
    public function generateInteractionCsv($data)
    {
        $csvData = $data;
        $token_id = $this->getConfig('general', 'private_token');
        $fileName = 'interactions_' . $token_id . '.csv';
        $filepath = 'media/visidea/csv/' . $fileName;
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $columns = $this->getColumnHeader();
        foreach ($columns as $column) {
            $header[] = $column;
        }

        $stream->writeCsv($header, ";");

        foreach ($csvData as $item) {
            if (isset($item['item_id'])) {
                $itemData = [];
                $itemData[] = (int)$item['user_id'];
                $itemData[] = $item['item_id'];
                $itemData[] = $item['action'];
                $itemData[] = $item['timestamp'];
                $stream->writeCsv($itemData, ";");
            }
        }
    }

    /**
     * Method generateItemCsv
     *
     * @param array $data data
     *
     * @return void
     */
    public function generateItemCsv($data)
    {
        $csvData = $data;
        $token_id = $this->getConfig('general', 'private_token');
        $fileName = 'items_' . $token_id . '.csv';

        $filepath = 'media/visidea/csv/' . $fileName;
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $columns = $this->getItemColumnHeader();
        foreach ($columns as $column) {
            $header[] = $column;
        }

        $stream->writeCsv($header, ";");

        foreach ($csvData as $item) {
            if (isset($item['item_id'])) {
                $itemData = [];
                $itemData[] = (int)$item['item_id'];
                $itemData[] = $item['name'];
                $itemData[] = $item['brand_id'];
                $itemData[] = $item['brand_name'];
                $itemData[] = $item['price'];
                $itemData[] = $item['market_price'];
                $itemData[] = $item['discount'];
                $itemData[] = $item['page_ids'];
                $itemData[] = $item['page_names'];
                $itemData[] = $item['url'];
                $itemData[] = $item['images'];
                $itemData[] = $item['stock'];
                $stream->writeCsv($itemData, ";");
            }
        }
    }

    /**
     * Method generateUserCsv
     *
     * @param array $data data
     *
     * @return void
     */
    public function generateUserCsv($data)
    {
        $csvData = $data;
        $token_id = $this->getConfig('general', 'private_token');
        $fileName = 'users_' . $token_id . '.csv';
        $filepath = 'media/visidea/csv/' . $fileName;
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $columns = $this->getUserColumnHeader();
        foreach ($columns as $column) {
            $header[] = $column;
        }

        $stream->writeCsv($header, ";");

        foreach ($csvData as $item) {
            if (isset($item['user_id'])) {
                $itemData = [];
                $itemData[] = (int)$item['user_id'];
                $itemData[] = $item['email'];
                $itemData[] = $item['name'];
                $itemData[] = $item['surname'];
                $itemData[] = $item['address'];
                $itemData[] = $item['city'];
                $itemData[] = $item['zip'];
                $itemData[] = $item['state'];
                $itemData[] = $item['country'];
                $itemData[] = $item['birthday'];
                $stream->writeCsv($itemData, ";");
            }
        }
    }

    /**
     * Method createExportFolder
     *
     * @return void
     */
    public function createExportFolder()
    {
        $destPath2 = $this->dir->getPath('media') . '/visidea/csv';
        if (!is_dir($destPath2)) {
            $this->file->mkdir($destPath2, 0777, true);
        }
    }

    /**
     * Method getColumnHeader
     *
     * @return array return headers
     */
    public function getColumnHeader()
    {
        $headers = ['user_id', 'item_id', 'action', 'timestamp'];
        return $headers;
    }

    /**
     * Method getUserColumnHeader
     *
     * @return array return headers
     */
    public function getUserColumnHeader()
    {
        $headers = ['user_id', 'email', 'name', 'surname', 'address', 'city', 'zip', 'state', 'country', 'birthday'];
        return $headers;
    }

    /**
     * Method getItemColumnHeader
     *
     * @return array return headers
     */
    public function getItemColumnHeader()
    {
        $headers = ['item_id', 'name', 'brand_id', 'brand_name', 'price', 'market_price', 'discount', 'page_ids', 'page_names', 'url', 'images', 'stock'];
        return $headers;
    }

    /**
     * Method isLoggedIn
     *
     * @return bool return true if logged in
     */
    public function isLoggedIn()
    {
        $isLoggedIn = $this->_httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isLoggedIn;
    }
}
