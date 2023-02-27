<?php

/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */
class Unirgy_RapidFlow_Model_Mysql4_Abstract extends Mage_Core_Model_Mysql4_Abstract
{
    const IMPORT_ROW_RESULT_ERROR    = 'error';
    const IMPORT_ROW_RESULT_SUCCESS  = 'success';
    const IMPORT_ROW_RESULT_NOCHANGE = 'nochange';
    const IMPORT_ROW_RESULT_DEPENDS  = 'depends';
    const IMPORT_ROW_RESULT_EMPTY    = 'empty';

    const TABLE_CATALOG_PRODUCT_BUNDLE_SELECTION       = 'bundle/selection';
    const TABLE_CATALOG_PRODUCT_BUNDLE_SELECTION_PRICE = 'bundle/selection_price';
    const TABLE_CATALOG_PRODUCT_ENTITY                 = 'catalog/product';
    const TABLE_CATALOG_PRODUCT_BUNDLE_OPTION          = 'bundle/option';
    const TABLE_CATALOG_PRODUCT_BUNDLE_OPTION_VALUE    = 'bundle/option_value';
    const TABLE_STORE_WEBSITE                          = 'core/website';

    const ERROR_DUPLICATE_ATTRIBUTES           = 'Duplicate attributes: %s';
    const ERROR_DUPLICATE_SKU                  = 'Duplicate SKU';
    const ERROR_EMPTY_INVALID_PRODUCT_TYPE     = 'Empty or invalid product type for a new product';
    const ERROR_EMPTY_OR_INVALID_ATTRIBUTE_SET = 'Empty or invalid attribute set for a new product';
    const ERROR_EMPTY_SKU                      = 'Empty SKU';
    const ERROR_INVALID_CATEGORY               = 'Invalid category: ';
    const ERROR_INVALID_DATETIME_VALUE         = 'Invalid datetime value';
    const ERROR_INVALID_DECIMAL_VALUE          = 'Invalid decimal value';
    const ERROR_INVALID_INT_VALUE              = 'Invalid int value';
    const ERROR_INVALID_OPTION                 = "Invalid option '%s'";
    const ERROR_MISSING_REQUIRED_VALUE         = "Missing required value for '%s'";
    const ERROR_MISSING_SKU                    = 'Missing SKU column';

    const WARNING_ATTRIBUTE_DOES_NOT_APPLY                = "The attribute '%s' does not apply to product type '%s', and will not be imported";
    const WARNING_ATTRIBUTE_DOES_NOT_APPLY_SET            = "The attribute '%s' does not apply to attribute set '%s', and will not be imported";
    const WARNING_ATTRIBUTE_NOT_USED_DYNAMIC              = "The attribute '%s' is not used, as it is a dynamic value in this product, and will not be imported";
    const WARNING_COLUMN_OUT_OF_BOUNDARIES                = 'Column is out of boundaries, ignored';
    const WARNING_CREATED_ATTRIBUTE_SET                   = "Created a new attribute set '%s'";
    const WARNING_CREATED_CATEGORY                        = "Created a new category '%s'";
    const WARNING_CREATED_NEW_OPTION_FOR_ATTRIBUTE        = "Created a new option '%s' for attribute '%s'";
    const WARNING_DOES_NOT_SEEM_LIKE_CATEGORY_ID          = "'%s' does not seem like category id, skipping.";
    const WARNING_EMPTY_TITLE                             = 'Empty title, the column will be ignored';
    const WARNING_FAILED_TO_INCREMENT_URL_KEY             = 'Failed to increment url_key in 100 attempts for SKU: %s';
    const WARNING_IF_ATTRIBUTE_NOT_USED_CONFIGURABLE      = "If the attribute '%s' will not used in configurable subproducts, this value might be missing";
    const WARNING_NOT_ALLOWED_ASSOCIATE_PRODUCTS_WEBSITES = 'You are not allowed to associate products with this websites: %s';
    const WARNING_UNKNOWN_FIELD                           = 'Unknown field: "%s", the column will be ignored';
    const WARNING_WILL_NOT_CHANGE_ATTRIBUTE_SET           = 'Will not change attribute set for an existing product';
    const WARNING_WILL_NOT_CHANGE_PRODUCT_TYPE            = 'Will not change product type for an existing product';

    protected $_exportImageRetainFolders;

    protected $_translateModule = 'Unirgy_RapidFlow';

    /**
     * @var Unirgy_RapidFlow_Model_Profile
     */
    protected $_profile;
    protected $_res;
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_write;

    protected $_encodingFrom;
    protected $_encodingTo;
    protected $_encodingIllegalChar;
    protected $_downloadRemoteImages;
    protected $_missingImageAction;
    protected $_existingImageAction;
    protected $_remoteImageSubfolderLevel;
    protected $_imagesMediaDir;
    protected $_deleteOldImage;
    protected $_deleteOldImageSkipUsageCheck;

    protected $_pageRowCount   = 500;
    protected $_pageSleepDelay = 0;

    protected $_curlConnectTimeout = 5;
    protected $_curlTimeout = 10;
    protected $_curlConnectTimeoutDownload = 5;
    protected $_curlTimeoutDownload = 10;
    protected $_curlUserAgent;
    protected $_curlHeaders;
    protected $_curlCustomRequest;

    protected $_locale;

    /**
     * DB Table cache
     *
     * @var array
     */
    protected $_tables = array();

    /**
     * DB table names by attribute type
     *
     * @var array
     */
    protected $_tablesByType = array();

    /**
     * Current data row
     *
     * @var array
     */
    protected $_row;

    /**
     * Current row number
     *
     * @var int
     */
    protected $_rowNum;

    /**
     * Current SQL select object
     *
     * @var Zend_Db_Select
     */
    protected $_select;

    /**
     * Current filter
     *
     * @var mixed
     */
    protected $_filter;

    /**
     * SKU->ID cache
     *
     * @var array
     */
    protected $_skus = array();

    /**
     * Magento EAV configuration singleton
     *
     * @var Mage_Eav_Model_Config
     */
    protected $_eav;

    /**
     * Limit number of items in cache to avoid memory problems
     *
     * @var mixed
     */
    protected $_maxCacheItems = array(
        'sku'                     => 10000,
        'bundle_option'           => 1000,
        'custom_option'           => 1000,
        'custom_option_selection' => 1000,
    );

    protected $_rootCatId = null;

    /**
     * Cache of dropdown attribute value labels
     *
     * @var array
     */
    protected $_attrOptionsByValue = array();

    protected $_attrOptionsByLabel = array();

    protected $_customerGroups       = array();
    protected $_customerGroupsByName = array();

    /**
     * An optional method to call on each row export
     *
     * @var array
     */
    protected $_exportRowCallback = array();

    protected $_entityTypes = array();

    protected $_fieldsIdx = array();

    protected $_storeIds = null;

    protected $_galleryAttrId;
    protected $_attrByCode;

    /**
     * Translate a phrase
     *
     * @return string
     */
    public function __()
    {
        $args = func_get_args();
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), $this->_translateModule);
        array_unshift($args, $expr);

        return Mage::app()->getTranslator()->translate($args);
    }

    protected function _construct()
    {
        $this->_init('urapidflow/profile', 'profile_id');

        $this->_res   = $this->_resources;
        $this->_read  = $this->_getReadAdapter();
        $this->_write = $this->_getWriteAdapter();

        $this->_locale = Mage::getSingleton('core/locale');
    }

    /**
     * @param  Unirgy_RapidFlow_Model_Mysql4_Abstract $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->_profile = $profile;
        $profileType = $profile->getProfileType();

        $this->_encodingFrom                 = $profile->getData('options/encoding/from');
        $this->_encodingTo                   = $profile->getData('options/encoding/to');
        $this->_encodingIllegalChar          = $profile->getData('options/encoding/illegal_char');
        $this->_downloadRemoteImages         = $profile->getData('options/' . $profileType . '/image_files_remote');
        $this->_missingImageAction           = (string) $profile->getData('options/' . $profileType . '/image_missing_file');
        $this->_existingImageAction          = (string) $profile->getData('options/' . $profileType . '/image_existing_file');
        $this->_remoteImageSubfolderLevel    = $profile->getData('options/' . $profileType . '/image_remote_subfolder_level');
        $this->_imagesMediaDir               = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product';
        $this->_deleteOldImage               = $profile->getData('options/' . $profileType . '/image_delete_old');
        $this->_deleteOldImageSkipUsageCheck = $profile->getData('options/' . $profileType . '/image_delete_skip_usage_check');

        return $this;
    }

    public function log($s, $type = 'info')
    {
        return;
        $logger = $this->_profile->getLogger();
        if ($logger == 'text') {
            //echo $s . "\n";
        } elseif ($logger == 'html') {
            //echo nl2br(htmlspecialchars($s)) . '<br/>';
        } elseif ($logger == 'db') {
            //$htis->_res->insert($this->t('profile_log'), array(
        } elseif (is_object($logger)) {
            $logger->log($s, $type);
        }

        return $this;
    }

    /**
     * @return array|null
     */
    protected function _getStoreIds()
    {
        if (null === $this->_storeIds) {
            $ids = $this->_profile->getData('options/store_ids');
            if(empty($ids)){
                $this->_storeIds = array();
                return $this->_storeIds;
            }
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
            }
            $this->_storeIds = $ids;
            if (Mage::helper('urapidflow')->hasEeGwsFilter()) {
                $this->_storeIds = Mage::helper('urapidflow')->filterEeGwsStoreIds($this->_storeIds);
            }
        }

        return $this->_storeIds;
    }

    /**
     * Get and validate store ID
     *
     * @param string|int $id
     * @param boolean $allowDefault
     * @return int
     */
    protected function _getStoreId($id, $allowDefault = false)
    {
        $store = Mage::app()->getStore($id);
        if (!$store || !$allowDefault && $store->isAdmin()) {
            Mage::throwException($this->__('Invalid store'));
        }

        return $store->getId();
    }

    protected function _getWebsiteId($id, $allowDefault = false)
    {
        $website = Mage::app()->getWebsite($id);
        if (!$allowDefault && $website->getId() == 0) {
            Mage::throwException($this->__('Invalid website'));
        }

        return $website->getId();
    }

    /**
     * Maintain table name cache
     *
     * @param string $table
     * @return string
     */
    protected function _t($table)
    {
        if (empty($this->_tables[$table])) {
            try {
                $this->_tables[$table] = $this->_res->getTableName($table);
            } catch(Exception $e) {
                $this->_tables[$table] = false;
            }
        }

        return $this->_tables[$table];
    }

    protected function _isChangeRequired(array $a, array $b)
    {
        foreach ($a as $k => $v) {
            if (isset($b[$k]) && $b[$k] != $v) {
                return true;
            }
        }

        return false;
    }

    protected function _checkLock()
    {
        if (!$this->_profile->isLocked()) {
            throw new Unirgy_RapidFlow_Exception_Stop();
        }
    }

    protected function _getRootCatId()
    {
        if (is_null($this->_rootCatId)) {
            $storeId = $this->_profile->getStoreId();
            if ($storeId) {
                $this->_rootCatId = Mage::app()->getStore($storeId)->getGroup()->getRootCategoryId();
            } else {
                $this->_rootCatId = $this->_read->fetchOne("select g.root_category_id from {$this->_t('core/website')} w inner join {$this->_t('core/store_group')} g on g.group_id=w.default_group_id where w.is_default=1");
            }
        }

        return $this->_rootCatId;
    }

    /**
     * Maintain product SKU->ID cache
     *
     * @param string $sku
     * @return int
     */
    protected function _getIdBySku($sku)
    {
        // in case we got already resoled id
        if (is_int($sku)) {
            return $sku;
        }
        if (empty($this->_skus[$sku])) {
            $id = $this->_read->fetchOne("select entity_id from {$this->_t('catalog/product')} where sku=?", $sku);
            // keep only last used 10000 skus to avoid memory problems
            if (sizeof($this->_skus) >= $this->_maxCacheItems['sku']) {
                reset($this->_skus);
                unset($this->_skus[key($this->_skus)]);
            }
            $this->_skus[$sku] = $id;
        }
        if (empty($this->_skus[$sku])) {
            Mage::throwException($this->__('Invalid SKU (%s)', $sku));
        }

        return $this->_skus[$sku];
    }

    protected function _getAttributeId($attrCode, $entityType = 'catalog_product')
    {
        $attr = $this->_getAttr($attrCode, $entityType);

        return $attr->getAttributeId();
    }

    /**
     * @param $attrCode
     * @param $entityType
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     * @throws \Mage_Core_Exception
     */
    protected function _getAttr($attrCode, $entityType)
    {
        if(isset($this->_attrByCode[$entityType][$attrCode])){
            return $this->_attrByCode[$entityType][$attrCode];
        }

        $attr = Mage::getSingleton('eav/config')->getAttribute($entityType, $attrCode);
        if (!$attr || !$attr->getAttributeId()) {
            Mage::throwException($this->__('Invalid attribute: %s', $attrCode));
        }
        $this->_attrByCode[$entityType][$attrCode] = $attr;
        return $attr;
    }

    protected function _getEntityType($entityTypeCode, $field = null)
    {
        if (empty($this->_entityTypes[$entityTypeCode])) {
            $entityType = Mage::getSingleton('eav/config')->getEntityType($entityTypeCode);
            if (!$entityType) {
                Mage::throwException($this->__('Invalid entity type: %s', $entityTypeCode));
            }
            if (is_object($entityType)) {
                $entityType = $entityType->toArray();
            }
            $this->_entityTypes[$entityTypeCode] = $entityType;
        }

        return !is_null($field)? $this->_entityTypes[$entityTypeCode][$field]: $this->_entityTypes[$entityTypeCode];
    }

    protected function _fetchAttributeOptions($attrCode, $entityType = 'catalog_product')
    {
        if (isset($this->_attrOptionsStatus[$attrCode])) {
            return $this->_attrOptionsStatus[$attrCode];
        }
        $attr = Mage::getSingleton('eav/config')->getAttribute($entityType, $attrCode);
        if (!$attr) {
            Mage::throwException($this->__('Invalid attribute: %s', $attrCode));
        }
        $aId = $attr->getAttributeId();
        if (!isset($this->_attrOptionsByValue[$aId])) {
            if (!$attr->usesSource()) {
                $this->_attrOptionsStatus[$attrCode] = false;

                return false;
            }
            $options = $attr->getSource()->getAllOptions();
            foreach ($options as $o) {
                if (!$o['value']) {
                    continue;
                }
                $this->_attrOptionsByValue[$aId][$o['value']]             = $o['label'];
                $this->_attrOptionsByLabel[$aId][strtolower($o['label'])] = $o['value'];
            }
        }
        $this->_attrOptionsStatus[$attrCode] = true;

        return true;
    }

    /**
     * Apply product filter...
     *
     */
    protected function _applyProductFilter($attr = 'main.entity_id')
    {
        if (!empty($this->_filter['product_ids'])) {
            $this->_select->where("{$attr} in (?)", $this->_filter['product_ids']);
        }
        $productIds = $this->_profile->getConditionsProductIds();
        if (is_array($productIds)) {
            $this->_select->where("{$attr} in (?)", $productIds);
        }
    }

    protected function _getCustomerGroup($key, $byName = false)
    {
        if (!$this->_customerGroups) {
            $rows                  = $this->_read->fetchAll("select * from {$this->_t('customer/customer_group')}");
            $this->_customerGroups = array();
            foreach ($rows as $r) {
                $this->_customerGroups[$r['customer_group_id']]                     = $r['customer_group_code'];
                $this->_customerGroupsByName[strtolower($r['customer_group_code'])] = $r['customer_group_id'];
            }
        }
        $errorMsg = Mage::helper('urapidflow')->__('Invalid customer group: %s', $key);
        if ($byName) {
            if (!isset($this->_customerGroupsByName[strtolower($key)])) {
                throw new Unirgy_RapidFlow_Exception_Row($errorMsg);
            }

            return $this->_customerGroupsByName[strtolower($key)];
        } else {
            if (!isset($this->_customerGroups[$key])) {
                throw new Unirgy_RapidFlow_Exception_Row($errorMsg);
            }

            return $this->_customerGroups[$key];
        }
    }

    protected function _getGalleryAttrId()
    {
        if (!$this->_galleryAttrId) {
            $this->_galleryAttrId = $this->_write->fetchOne("select attribute_id from {$this->_t('eav/attribute')} where attribute_code='media_gallery' and frontend_input='gallery'");
        }

        return $this->_galleryAttrId;
    }

    /**
     * @var array map of urls to local file names
     */
    protected $_remoteImagesCache = array();

    /**
     * @param string $fromDir
     * @param string $toDir
     * @param string $filename
     * @param bool   $import
     * @param null   $oldValue
     * @param bool   $noCopyFlag
     * @return bool
     * @throws Unirgy_RapidFlow_Exception_Row
     */
    protected function _copyImageFile(
        $fromDir, $toDir, &$filename,
        $import = false, $oldValue = null,
        $noCopyFlag = false
    )
    {
        $ds = '/';

        $remote = preg_match('#^https?:#', $filename);
        if ($remote && !$this->_downloadRemoteImages) {
            // when image is remote, and remote images are not allowed, do nothing and reset imported value
            $this->_profile->getLogger()->warning($this->__('Skipping: %s, remote images download is disabled.', $filename));
            $this->_profile->addValue(Unirgy_RapidFlow_Model_Profile::NUM_WARNINGS);
            $filename = '';
            return false;
        }
        $basename = basename($filename);
        if ($remote) {
            $basename = basename(parse_url($filename, PHP_URL_PATH));
        }

        $fromDir = rtrim($fromDir, '/\\');
        $toDir   = rtrim($toDir, '/\\');
        if (null === $this->_exportImageRetainFolders) {
            $this->_exportImageRetainFolders = $this->_profile->getData('options/export/image_retain_folders')?: false;
        }
        if (!$import && $this->_exportImageRetainFolders) {
            $prefix = substr($filename, 0, -strlen($basename));
            $toDir  = $toDir . $ds . trim($prefix, '/\\');
        }

        if ($import && $remote) {
            $slashPos     = false;
            $fromFilename = $filename;
            $fromExists   = true;
            $fromRemote   = true;
            // if remote image and it has been already downloaded, use the existing file instead of downloading
            if (isset($this->_remoteImagesCache[$fromFilename])) {
                $filename     = $this->_remoteImagesCache[$fromFilename]['name'];
                $this->_profile->getLogger()->warning($this->__('%s is downloaded already, using local file: %s.',
                    $fromFilename, $filename));
                $fromFilename = $this->_remoteImagesCache[$fromFilename]['path'];
                $fromRemote   = false;
                $fromExists   = is_readable($fromFilename);
                $slashPos     = strpos($filename, $ds);
            } else {  // remote file is not yet downloaded
                if ($this->_remoteImageSubfolderLevel) {
                    $filenameArr = explode('/', $filename);
                    array_pop($filenameArr);
                    $filename = $basename;
                    for ($i = 0; $i < $this->_remoteImageSubfolderLevel; $i++) {
                        $filename = array_pop($filenameArr) . $ds . $filename;
                    }
                    $slashPos = strpos($filename, $ds);
                } else {
                    $filename = $basename;
                }
                $filename = str_replace(
                    array(' ', '%'),
                    '-',
                    urldecode($filename));
            }
        } else {
            $slashPos     = strpos($filename, $ds);
            $fromFilename = $fromDir . $ds . ltrim($filename, $ds);
            /*
            if ($import && $slashPos===0) {
                // if importing and filename starts with slash, use only basename for source file
                $fromFilename = $fromDir.$ds.basename($filename);
            }
            */
            $fromExists = is_readable($fromFilename);
            $fromRemote = false;
        }

        if (is_dir($fromFilename)) {
            // swatch images are media type attribute but do not have actual image most of the time
            $this->_profile->getLogger()->warning($this->__('%s is not valid file, skipping copy', $fromFilename));

            return true;
        }

        $warning = '';
        $origBasename = basename($filename);
        $cleanBasename = Mage_Core_Model_File_Uploader::getCorrectFileName($origBasename);
        if ($origBasename!=$cleanBasename) {
            $filename = str_replace($origBasename, $cleanBasename, $filename);
            $warning .= __(' Corrected image name: %1.', $filename);
        }

        $toFilename = $toDir . $ds . ltrim($filename, $ds);
        if ($import) {
            if ($slashPos === false) {
                $prefix = str_replace('\\', $ds, Varien_File_Uploader::getDispretionPath($filename));
                $toDir .= $ds . ltrim($prefix, $ds);
                $toFilename = rtrim($toDir, $ds) . $ds . ltrim($filename, $ds);
                $filename   = $prefix . $ds . $filename;
            } elseif ($dirname = dirname($filename)) {
                $toDir .= $ds . ltrim($dirname, $ds);
            }
        } elseif (!$import && $slashPos === 0) {
            $toFilename = $toDir . $ds . basename($filename);
        }
        $toExists = is_readable($toFilename);

        $filename = $ds . ltrim($filename, $ds);

        if ($noCopyFlag) {
            return true;
        }

        if ($import && $toExists && $this->_existingImageAction) {
            $this->_profile->addValue(Unirgy_RapidFlow_Model_Profile::NUM_WARNINGS);
            $warning .= $this->__('Imported image file already exists.');
            if ($filename === $oldValue) {
                // new file name is same as current value
                $warning .= $this->__(' %s is same as current value, %s.', $filename, $oldValue);
            } else {
                switch ($this->_existingImageAction) {
                    case 'skip':
                        $warning .= $this->__(' Skipping field update');
                        $this->_profile->getLogger()->warning($warning);
                        return false;
                        break;
                    case 'replace' :
                        // basically just notify user that there is
                        $warning .= $this->__(' Replacing existing image');
                        break;
                    case 'save_new':
                        $warning     .= $this->__(' Updating image name and saving as new image.');
                        $toFilename  = $this->_getUniqueImageName($toFilename);
                        $newBasename = basename($toFilename);
                        $oldBasename = basename($filename);
                        if ($newBasename !== $oldBasename) {
                            $filename = str_replace($oldBasename, $newBasename, $filename);
                            $warning  .= $this->__(' New image name: %s', $filename);
                        }
                        break;
                }
            }
            $this->_profile->getLogger()->warning($warning);
        } else if ($import && !$toExists) {
            // if have to import, but image is new
            $this->_getUniqueImageName($toFilename);
        }

        if (!$fromExists) {
            $warning .= $this->__('Source image file does not exist: %s', $fromFilename);
            if ($this->_missingImageAction === 'error') {
                throw new Unirgy_RapidFlow_Exception_Row($warning);
            } else {
                $result = false;
                switch ($this->_missingImageAction) {
                    case '':
                    case 'warning_save':
                        $result = true;
                        break;

                    case 'warning_skip':
                        $warning .= '. ' . $this->__('Image field was not updated');
                        $filename = $oldValue; // set import value to be same as old value and avoid update
                        break;

                    case 'warning_empty':
                        $warning .= '. ' . $this->__('Image field was reset');
                        $filename = '';
                        $result   = true;
                        break;
                }
                $this->_profile->addValue(Unirgy_RapidFlow_Model_Profile::NUM_WARNINGS);
                $this->_profile->getLogger()->warning($warning);

                return $result;
            }
        } elseif ($toExists && $fromExists && !$fromRemote
                  && $filename === $oldValue
                  && filesize($fromFilename) === filesize($toFilename)
        ) {
            // no need to copy
            return false;
        }

        Mage::getConfig()->createDirIfNotExists($toDir);

        if ($fromRemote) {
            if (!($ch = curl_init($fromFilename))) {
                $error = $this->__('Unable to open remote file: %s', $fromFilename);
            } else {
                /*
                $dumpFilename = realpath(Mage::getBaseDir('var')).'/log/rf_dwnl_dump';
                $dumpFp = fopen($dumpFilename, 'a');
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_STDERR, $dumpFp);
                */
                if ($this->_curlCustomRequest) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->_curlCustomRequest);
                if ($this->_curlUserAgent) curl_setopt($ch, CURLOPT_USERAGENT, $this->_curlUserAgent);
                if ($this->_curlHeaders) {
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_curlHeaders);
                }
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_curlConnectTimeout);
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->_curlTimeout);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
                curl_setopt($ch, CURLOPT_NOBODY, 1);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $headResult = curl_exec($ch);
                if ($headResult === false) {
                    $error = $this->__('Unable to fetch remote file: %s', $fromFilename);
                } elseif ($headResult === false || false !== strpos($headResult, '404 Not Found')) {
                    $error = $this->__('"404 Not Found" response for remote file: %s', $fromFilename);
                } elseif ($headResult === false || false !== strpos($headResult, '403 Forbidden')) {
                    $error = $this->__('"403 Forbidden" response for remote file: %s', $fromFilename);
                } else {
                    if (!($fp = fopen($toFilename, 'w'))) {
                        $error = $this->__('Unable to open local file for writing: %s', $toFilename);
                    } else {
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_curlConnectTimeoutDownload);
                        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_curlTimeoutDownload);
                        curl_setopt($ch, CURLOPT_NOBODY, 0);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        if (false === curl_exec($ch)) {
                            $error = $this->__('Unable to fetch remote file: %s', $fromFilename);
                        } else {
                            $this->_remoteImagesCache[$fromFilename]['name'] = $filename;
                            $this->_remoteImagesCache[$fromFilename]['path'] = $toFilename;
                        }
                    }
                }
            }
            if ($ch) {
                curl_close($ch);
            }
            if (!empty($fp)) {
                fclose($fp);
            }
            if (!empty($dumpFp)) {
                fclose($dumpFp);
            }
            if (!empty($error)) {
                $this->_profile->addValue(Unirgy_RapidFlow_Model_Profile::NUM_WARNINGS);
                $this->_profile->getLogger()->warning($error);

                return false;
            }
        } else {
            if ($fromFilename === $toFilename && filesize($fromFilename) === filesize($toFilename)) {
                return true; // do not try to copy same image over itself
            }
            if ($toExists) {
                @unlink($toFilename);
            }
            if (!@copy($fromFilename, $toFilename)) {
                $errors = error_get_last();
                $error = 'COPY ERROR: ';
                if($errors && array_key_exists('type', $errors)){
                    $error .= $errors['type'];
                }
                if($errors && array_key_exists('message', $errors)){
                    $error .= PHP_EOL . $errors['message'];
                }
                $this->_profile->addValue(Unirgy_RapidFlow_Model_Profile::NUM_WARNINGS);
                $this->_profile->getLogger()->warning($this->__('Was not able to copy image file: %s', $error));

                return false;
            }
        }
        $eventVars = array(
            'basename'      => $basename,
            'filename'      => $filename,
            'from_dir'      => $fromDir,
            'from_filename' => $fromFilename,
            'from_remote'   => $fromRemote,
            'to_dir'        => $toDir,
            'to_exists'     => $toExists,
            'import'        => $import,
            'profile'       => $this->_profile,
            'old_value'     => $oldValue,
        );

        Mage::dispatchEvent('urapidflow_copy_image_file_success', $eventVars);

        return true;
    }

    protected function _validateImageFile(&$filename, $toDir, $noCopyFlag = false)
    {
        $ds = '/';
        if (($slashPos = strpos($filename, $ds)) !== false) {
            $filename = ltrim($filename, $ds);
        }
        $result = false;
        if (file_exists($toDir . $ds . $filename)) {
            $filename = $ds . ltrim($filename, $ds);
            $result   = true;
        } else if ($slashPos === false) {
            $prefix   = str_replace('\\', $ds, Varien_File_Uploader::getDispretionPath($filename));
            $filename = $ds . trim($prefix, $ds) . $ds . ltrim($filename, $ds);
            if (file_exists($toDir . $filename)) {
                $result = true;
            }
        } else {
            $filename = $ds . ltrim($filename, $ds);
        }

        if ($result || $noCopyFlag) {
            return $result;
        }

        $warning = $this->__('Related file image does not exist');
        if ($this->_missingImageAction === 'error') {
            throw new Unirgy_RapidFlow_Exception_Row($warning);
        }
        $result = false;
        switch ($this->_missingImageAction) {
            case '':
            case 'warning_save':
                $result = true;
                break;

            case 'warning_skip':
                $warning .= '. ' . $this->__('Image field was not updated');
                break;

            case 'warning_empty':
                $warning .= '. ' . $this->__('Image field was reset');
                $filename = null;
                $result   = true;
                break;
        }
        $this->_profile->addValue(Unirgy_RapidFlow_Model_Profile::NUM_WARNINGS);
        $this->_profile->getLogger()->warning($warning);

        return $result;
    }

    protected function _convertEncoding($value)
    {
        if ($value && $this->_encodingFrom && $this->_encodingTo && $this->_encodingFrom != $this->_encodingTo) {
            /*
            $from = $this->_encodingFrom;
            if ($this->_encodingFrom=='auto') {
                $from = mb_detect_encoding($value.'a', 'auto');
                if (!$from) {
                    $from = 'UTF-8';
                }
            }
            */
            if (is_array($value)) {
                foreach ($value as $i => $v) {
                    $value[$i] = $this->_convertEncoding($v);
                }
            } else {
                $encodingTo = $this->_encodingTo . ($this->_encodingIllegalChar? '//' . $this->_encodingIllegalChar: '');
                try {
                    $value1 = iconv($this->_encodingFrom, $encodingTo, $value);
                } catch(Exception $e) {
                    if (strpos($e->getMessage(), 'Detected an illegal character in input string') !== false) {
                        $this->_profile->addValue(Unirgy_RapidFlow_Model_Profile::NUM_WARNINGS);
                        $this->_profile->getLogger()->warning($this->__('Illegal character in string: %s', $value));
                        $value1 = $value;
                    } else {
                        throw $e;
                    }
                }
                $value = $value1;
            }
        }

        return $value;
    }

    protected function _refreshHasOptionsRequiredOptions($pIds, $useKeys = true)
    {
        if (!empty($pIds)) {
            if ($useKeys) {
                $pIds = array_keys($pIds);
            }
            $horoSelect = $this->_write->select()
                                       ->from(array('p' => $this->_t('catalog/product')), array('entity_id'))
                                       ->joinLeft(array('po' => $this->_t('catalog/product_option')),
                                           'po.product_id=p.entity_id', array())
                                       ->where('p.entity_id in (?)', $pIds)
                                       ->group('p.entity_id')
                                       ->columns('sum(IF(po.option_id is not null, 1, 0)) as has_options');
            if (Mage::helper('urapidflow')->hasMagefeature('product.required_options')) {
                $horoSelect->columns('sum(IF(po.option_id is not null and po.is_require!=0, 1, 0)) as required_options');
            }
            $horoRows = $this->_write->fetchAll($horoSelect);

            $horoSelect = $this->_write->select()
                                       ->from(array('p' => $this->_t('catalog/product')), array('entity_id'))
                                       ->joinLeft(array('po' => $this->_t('catalog/product_super_attribute')),
                                           'po.product_id=p.entity_id', array())
                                       ->where('p.entity_id in (?)', $pIds)
                                       ->where('p.type_id=?', 'configurable')
                                       ->group('p.entity_id')
                                       ->columns('sum(IF(po.product_super_attribute_id is not null, 1, 0)) as has_options');
            if (Mage::helper('urapidflow')->hasMagefeature('product.required_options')) {
                $horoSelect->columns('sum(IF(po.product_super_attribute_id is not null, 1, 0)) as required_options');
            }

            foreach ($this->_write->fetchAll($horoSelect) as $horo) {
                foreach ($horoRows as &$_horo) {
                    if ($_horo['entity_id'] == $horo['entity_id']) {
                        $_horo['has_options'] = max($_horo['has_options'], $horo['has_options']);
                        if (Mage::helper('urapidflow')->hasMagefeature('product.required_options')) {
                            $_horo['required_options'] = max($_horo['required_options'], $horo['required_options']);
                        }
                        break;
                    }
                }
                unset($_horo);
            }

            $horoSelect = $this->_write->select()
                                       ->from(array('p' => $this->_t('catalog/product')), array('entity_id'))
                                       ->joinLeft(array('po' => $this->_t('bundle/option')), 'po.parent_id=p.entity_id',
                                           array())
                                       ->where('p.entity_id in (?)', $pIds)
                                       ->where('p.type_id=?', 'bundle')
                                       ->group('p.entity_id')
                                       ->columns('sum(IF(po.option_id is not null, 1, 0)) as has_options');
            if (Mage::helper('urapidflow')->hasMagefeature('product.required_options')) {
                $horoSelect->columns('sum(IF(po.option_id is not null and po.required!=0, 1, 0)) as required_options');
            }
            foreach ($this->_write->fetchAll($horoSelect) as $horo) {
                foreach ($horoRows as &$_horo) {
                    if ($_horo['entity_id'] == $horo['entity_id']) {
                        $_horo['has_options'] = max($_horo['has_options'], $horo['has_options']);
                        if (Mage::helper('urapidflow')->hasMagefeature('product.required_options')) {
                            $_horo['required_options'] = max($_horo['required_options'], $horo['required_options']);
                        }
                        break;
                    }
                }
                unset($_horo);
            }

            $query = 'UPDATE ' . $this->_t('catalog/product') . ' SET ';
            $hoSql = '`has_options`=CASE `entity_id`';
            if (Mage::helper('urapidflow')->hasMagefeature('product.required_options')) {
                $roSql = ', `required_options`=CASE `entity_id`';
            }
            foreach ($horoRows as $horo) {
                $hoSql .= $this->_write->quoteInto(' WHEN ? ', $horo['entity_id']);
                $hoSql .= $this->_write->quoteInto(' THEN ? ', $horo['has_options'] > 0? 1: 0);
                if (Mage::helper('urapidflow')->hasMagefeature('product.required_options')) {
                    $roSql .= $this->_write->quoteInto(' WHEN ? ', $horo['entity_id']);
                    $roSql .= $this->_write->quoteInto(' THEN ? ', $horo['required_options'] > 0? 1: 0);
                }
            }
            $hoSql .= ' ELSE `has_options` END';
            if (Mage::helper('urapidflow')->hasMagefeature('product.required_options')) {
                $roSql .= ' ELSE `required_options` END';
            }

            $query .= $hoSql;
            if (Mage::helper('urapidflow')->hasMagefeature('product.required_options')) {
                $query .= $roSql;
            }
            $query .= $this->_write->quoteInto(' WHERE `entity_id` IN (?)', $pIds);

            $this->_write->query($query);

        }

        return $this;
    }

    /**
     * @param array  $row
     * @param string $dataKey
     * @param string $parentKey
     * @return string
     */
    protected function catBuildPath($row, $dataKey = "url_key", $parentKey = "url_key")
    {
        $path     = null;
        $eId      = isset($row['entity_id'])? $row['entity_id']: null;
        $rcID     = $this->_getRootCatId();
        if (isset($row['root_category_path'])) {
            $rootPath = $row['root_category_path'].'/';
        } else {
            $rootPath = $rcID ? '1/' . $rcID . '/' : '1/';
        }
        $entities = $this->_getCategoryUrlEntities();
        if (!empty($row[$dataKey])) {
            //$rootPath    = $this->_rootCatId ? '1/' . $this->_rootCatId . '/' : '1/';
            $urlKeys     = array();
            if (!isset($row['root_category_path']) || $row['path']!=$row['root_category_path']) {
                $ancestorIds = explode('/', str_replace($rootPath, '', $row['path']));
                $eId         = array_pop($ancestorIds); // remove current cat id
                foreach ($ancestorIds as $aid) {
                    if (!isset($entities[$aid])) {
                        $this->_profile->getLogger()
                                       ->warning(sprintf("Parent category with id: %d not found. Category id: %d", $aid,
                                           $eId));

                        return $path;
                    }
                    $ancestor  = $entities[$aid];
                    $urlKeys[] = isset($ancestor[$parentKey])? $ancestor[$parentKey]: @$ancestor[0][$parentKey];
                }
            }
            $urlKeys[] = $row[$dataKey];
            if (!empty($urlKeys)) {
                $path = implode("/", $urlKeys);
            }
        } else {
            $this->_profile->getLogger()->warning(sprintf("Category: %s is missing url_key", $eId? $eId: 'N/A'));
        }

        return $path;
    }

    /**
     * @param array  $attr
     * @param string $baseTable
     * @return string
     */
    protected function getAttrType($attr, $baseTable = "catalog_product_entity")
    {
        $type = $attr['backend_type'];
        $baseTable = $this->_t($baseTable);
        if (!empty($attr['backend_table'])) {
            $attrTable = $this->_t($attr['backend_table']);
            $diff = str_ireplace($baseTable . "_", "", $attrTable);
            if (!empty($diff)) {
                $type = $diff;
            }
        }
        if (empty($this->_tablesByType[$type])) {
            $this->_tablesByType[$type] = !empty($attrTable) ? $attrTable : "{$baseTable}_{$attr['backend_type']}";
        }
        return $type;
    }

    protected $_categoryUrlEntities;

    protected function _getCategoryUrlEntities()
    {
        if (!$this->_categoryUrlEntities) {
            $eav        = Mage::getSingleton('eav/config');
            $categories = array();
            $storeId    = $this->_profile->getStoreId();
            $sortDir = 'desc';
            if ($storeId==0) {
                $storeId = Mage::app()->getDefaultStoreView()->getId();
                $sortDir = 'asc';
            }
            $table      = $this->_t('catalog/category');
            foreach (array('url_key', 'url_path') as $k) {
                $attrId = $eav->getAttribute('catalog_category', $k)->getAttributeId();
                // fetch attribute values for all categories
                $tableName = ($k == 'url_key')? $table . '_url_key': $table . '_varchar';
                $sql       = "select entity_id, value from {$tableName} where attribute_id={$attrId} and store_id in (0, {$storeId}) order by store_id ".$sortDir;
                $rows      = $this->_read->fetchAll($sql);
                foreach ($rows as $r) {
                    // load values for specific store OR default
                    if (empty($categories[$r['entity_id']][$k])) {
                        $categories[$r['entity_id']][$k] = $r['value'];
                    }
                }
            }
            $this->_categoryUrlEntities = $categories;
        }

        return $this->_categoryUrlEntities;
        // todo, fetch data with category entity_id, url_key, url_path columns
    }

    /**
     * @param string $toFilename
     *
     * @return mixed|string
     */
    protected function _getUniqueImageName($toFilename)
    {
        $fileInfo   = pathinfo($toFilename);
        $newName    = Varien_File_Uploader::getNewFileName($toFilename);
        $toFilename = str_replace($fileInfo['filename'] . '.' . $fileInfo['extension'], $newName, $toFilename);

        return $toFilename;
    }

    public function setFinetuneParams($tune)
    {
        if (!empty($tune['export_page_size']) && $tune['export_page_size']>0) {
            $this->_pageRowCount = (int)$tune['export_page_size'];
        }
        if (!empty($tune['page_sleep_delay'])) {
            $this->_pageSleepDelay = (int)$tune['page_sleep_delay'];
        }
        if (!empty($tune['curl_connect_timeout'])) {
            $this->_curlConnectTimeout = (int)$tune['curl_connect_timeout'];
        }
        if (!empty($tune['curl_timeout'])) {
            $this->_curlTimeout = (int)$tune['curl_timeout'];
        }
        if (!empty($tune['curl_connect_timeout_download'])) {
            $this->_curlConnectTimeoutDownload = (int)$tune['curl_connect_timeout_download'];
        }
        if (!empty($tune['curl_timeout_download'])) {
            $this->_curlTimeoutDownload = (int)$tune['curl_timeout_download'];
        }
        if (!empty($tune['curl_useragent'])) {
            $this->_curlUserAgent = $tune['curl_useragent'];
        }
        if (!empty($tune['curl_customrequest'])) {
            $this->_curlCustomRequest = $tune['curl_customrequest'];
        }
        if (!empty($tune['curl_headers'])) {
            $this->_curlHeaders = array_filter(preg_split("/\r\n|\n\r|\r|\n/", $tune['curl_headers']));
        }
    }
}
