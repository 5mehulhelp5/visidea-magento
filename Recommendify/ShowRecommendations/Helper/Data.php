<?php

namespace Recommendify\ShowRecommendations\Helper;

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

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_logger;
    protected $_storeManager;
    protected $orderManagement;
    protected $_objectManager;
    protected $scopeConfig;
    protected $file;
    protected $quoteFactory;
    protected $quoteModel;
    protected $quoteManagement;
    protected $dateTime;
    protected $csvProcessor;
    protected $_fileFactory;
    protected $directory;
    protected $_productCollectionFactory;
    protected $_customer;
    protected $_customerFactory;
    private $httpContext;

    const MODULE_ENABLED = 'recommendify_showrecommendations/general/enable';


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
    )
    {
        $this->_storeManager = $storeManager;
        $this->_logger = $context->getLogger();
        $this->orderManagement = $orderManagement;
        $this->_objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->file = $file;
        $this->dir = $dir;
        $this->quoteFactory = $quoteFactory;
        $this->quoteModel = $quoteModel;
        $this->quoteManagement = $quoteManagement;
        $this->dateTime = $dateTime;
        $this->csvProcessor = $csvProcessor;
        $this->_fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::PUB);
        $this->httpContext = $httpContext;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_customerFactory = $customerFactory;
        $this->_customer = $customers;
        parent::__construct($context);
    }


    public function isEnable()
    {
        return (int)$this->scopeConfig->getValue(self::MODULE_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getConfig($group, $field, $storeId = 0)
    {
        return $this->scopeConfig->getValue(
            'recommendify_showrecommendations/' . $group . '/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getReturnUrl($path)
    {
        return $this->_storeManager->getStore()->getBaseUrl() . $path;
    }

    public function getCronUrl()
    {
        $token_id = $this->getConfig('general', 'private_token');
        return $this->getReturnUrl('showrecommendations/csv/export/token_id/' . $token_id);
    }

    public function getInteractionExportUrl()
    {
        $token_id = $this->getConfig('general', 'private_token');
        return $this->getReturnUrl('pub/media/recommendify/csv/interactions_' . $token_id . '.csv');
    }

    public function getItemsExportUrl()
    {
        $token_id = $this->getConfig('general', 'private_token');
        return $this->getReturnUrl('pub/media/recommendify/csv/items_' . $token_id . '.csv');
    }

    public function getCustomerExportUrl()
    {
        $token_id = $this->getConfig('general', 'private_token');
        return $this->getReturnUrl('pub/media/recommendify/csv/users_' . $token_id . '.csv');
    }

    public function getUrl($route, $params = [])
    {
        return $this->_getUrl($route, $params);
    }

    public function getQuoteCollection()
    {
        $collection = $this->quoteFactory->create()->addFieldToSelect('*');
        $collection->addFieldToFilter('customer_id', ['neq' => 'NULL']);

        return $collection;
    }

    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        return $collection;
    }

    public function getCustomerCollection()
    {
        return $this->_customer->getCollection()
            ->addAttributeToSelect("*")
            ->load();
    }

    public function generateInteractionCsv($data)
    {
        $csvData = $data;
        $token_id = $this->getConfig('general', 'private_token');
        $fileName = 'interactions_' . $token_id . '.csv';
        $filepath = 'media/recommendify/csv/' . $fileName;
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


    public function generateItemCsv($data)
    {
        $csvData = $data;
        $token_id = $this->getConfig('general', 'private_token');
        $fileName = 'items_' . $token_id . '.csv';

        $filepath = 'media/recommendify/csv/' . $fileName;
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

    public function generateUserCsv($data)
    {
        $csvData = $data;
        $token_id = $this->getConfig('general', 'private_token');
        $fileName = 'users_' . $token_id . '.csv';
        $filepath = 'media/recommendify/csv/' . $fileName;
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

    public function createExportFolder()
    {
        $destPath2 = $this->dir->getPath('media') . '/recommendify/csv';
        if (!is_dir($destPath2)) {
            $this->file->mkdir($destPath2, 0777, true);
        }
    }

    public function getColumnHeader()
    {
        $headers = ['user_id', 'item_id', 'action', 'timestamp'];
        return $headers;
    }

    public function getUserColumnHeader()
    {
        $headers = ['user_id', 'email', 'name', 'surname', 'address', 'city', 'zip', 'state', 'country', 'birthday'];
        return $headers;
    }

    public function getItemColumnHeader()
    {
        $headers = ['item_id', 'name', 'brand_id', 'brand_name', 'price', 'market_price', 'discount', 'page_ids', 'page_names', 'url', 'images', 'stock'];
        return $headers;
    }

    public function isLoggedIn()
    {
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isLoggedIn;
    }
}
