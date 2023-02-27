<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Block_Catalog_Product_View_Type_Configurablel
    extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    protected $_currentAttributes;
    protected $_jsonConfig;
    protected $_useSimplePrice;
    protected $_indexData;
    protected $_imageSizeAtCategoryPageX;
    protected $_imageSizeAtCategoryPageY;

    protected function _toHtml()
    {
        $html = parent::_toHtml();

        if ('product.info.options.configurable' == $this->getNameInLayout()) {
            $html = str_replace('super-attribute-select', 'no-display super-attribute-select', $html);

            $_product = $this->getProduct();
            $confData = $this->getSettingConfig();

            $this->_imageSizeAtCategoryPageX = Mage::getStoreConfig('amconf/list/main_image_list_size_x');
            $this->_imageSizeAtCategoryPageY = Mage::getStoreConfig('amconf/list/main_image_list_size_y');
            
            if ($this->_currentAttributes) {
                $this->_currentAttributes = array_unique($this->_currentAttributes);
                $simpleProducts = $this->getProduct()->getTypeInstance(true)->getUsedProducts(null, $_product);
                /** @var Mage_Catalog_Model_Product $simple */
                foreach ($simpleProducts as $simple) {
                    $this->generateChildConfig($simple, $confData);
                }

                $id = $_product->getEntityId();
                $html = '<script type="text/javascript"> 
                              confData[' . $id . '] = new AmConfigurableData(' . Zend_Json::encode($confData) . '); 
                         </script>'
                    . $html;
            }
        }

        return $html;
    }

    protected function getSettingConfig()
    {
        $this->_useSimplePrice = (Mage::helper('amconf')->getConfigUseSimplePrice() == 2
            || (Mage::helper('amconf')->getConfigUseSimplePrice() == 1
                AND $this->getProduct()->getData('amconf_simple_price')
            )
        ) ? true : false;

        $productUrl = $this->getProduct()->getProductUrl();
        $productUrl = substr($productUrl, strrpos($productUrl, "/"));
        $qtySwatches = null;
        if (Mage::getStoreConfig('amconf/list/enable_qty_swatches', Mage::app()->getStore()->getId())) {
            $qtySwatches = Mage::getStoreConfig('amconf/list/qty_swatches', Mage::app()->getStore()->getId());
        }

        $confData = array(
            'textNotAvailable'   => $this->__('Choose previous option please...'),
            'useSimplePrice'     => $this->_useSimplePrice,
            'showAllOptions'     => Mage::getStoreConfig('amconf/general/show_all'),
            'url'                => $productUrl,
            'amRequaredField'    => $this->__('&uarr;  This is a required field.'),
            'amQtySwatches'      => $qtySwatches,
            'amMoreColorsAvailable'    => $this->__('More Colors Available'),
            'onclick'            => Mage::helper('checkout/cart')->getAddUrl($this->getProduct())
        );

        return $confData;
    }

    /**
     * array key here is a combination of choosen options
     * @param Mage_Catalog_Model_Product $simple
     * @return string
     */
    protected function getProductKey(Mage_Catalog_Model_Product $simple)
    {
        $key = array();
        foreach ($this->_currentAttributes as $attributeCode) {
            $key[] = $simple->getData($attributeCode);
        }

        return implode(',', $key);
    }

    /**
     * Generate product information for child simple product
     * @param Mage_Catalog_Model_Product $simple
     * @param array $confData
     */
    protected function generateChildConfig(Mage_Catalog_Model_Product $simple, &$confData)
    {
        $strKey = $this->getProductKey($simple);
        $confData[$strKey] = array();
        $confData[$strKey]['not_is_in_stock'] = !$simple->isSaleable();

        if (!('no_selection' == $simple->getSmallImage() || '' == $simple->getSmallImage())) {

            $confData[$strKey]['small_image'] = $this->getImageDependOnSetting($simple);

            if (Mage::getStoreConfig('amconf/general/oneselect_reload')) {
                if (strpos($strKey, ',') !== FALSE) {
                    $k = substr($strKey, 0, strpos($strKey, ','));
                    if (!(array_key_exists($k, $confData) && array_key_exists('small_image', $confData[$k]))) {
                        $confData[$k]['small_image'] = $confData[$strKey]['small_image'];
                    }
                }
            }
        }

        if ($this->_useSimplePrice) {
            $tierPriceHtml = $this->getTierPriceHtml($simple);
            $search = 'product-price-' . $simple->getId();
            $replace = 'product-price-' . $this->getProduct()->getId();
            $confData[$strKey]['price_html'] = str_replace(
                $search,
                $replace,
                $this->getPriceHtml($simple) . $tierPriceHtml
            );
        }
        //for option count more 3
        if (Mage::getStoreConfig('amconf/general/oneselect_reload')) {
            $pos = strpos($strKey, ",");
            if ($pos) {
                $pos = strpos($strKey, ",", $pos + 1);
                if ($pos) {
                    $newKey = substr($strKey, 0, $pos);
                    $confData[$newKey] = $confData[$strKey];
                }
            }

        }
    }

    protected function getImageDependOnSetting($simple)
    {
        if (Mage::getStoreConfig('amconf/list/list_index')) {
            $indexData = $this->getIndexData();
            $src = isset($indexData['simples'][$simple->getId()]['small_image_url']) ?
                $indexData['simples'][$simple->getId()]['small_image_url'] : '';

        } else {
            $src = (string)($this->helper('catalog/image')->init($simple, 'small_image')
                ->resize($this->_imageSizeAtCategoryPageX, $this->_imageSizeAtCategoryPageY));
        }

        return $src;
    }

    protected function getIndexData()
    {
        if (!$this->_indexData) {
            $indexModel = Mage::getModel('amconf/indexer_super');
            if ($indexModel) {
                $this->_indexData = $indexModel->getPersistedDataById($this->getProduct()->getId(), 'configurable');
            }
        }

        return $this->_indexData;
    }

    public function getJsonConfig()
    {
        $jsonConfig = parent::getJsonConfig();
        $config = Zend_Json::decode($jsonConfig);
        $productImagesAttributes = $this->getImagesFromProductsAttributes();
        $productParent = $this->getProduct();
        $resModel = Mage::getResourceModel('catalog/product');

        foreach ($config['attributes'] as $attributeId => $attribute) {
            $this->_currentAttributes[] = $attribute['code'];
            
            $allowedCodes = trim(Mage::getStoreConfig('amconf/list/allowed_attr_code'));
            $allowedCodes = str_replace(' ', '', $allowedCodes);
            $allowedCodesArray = explode(',', $allowedCodes);

            if ($allowedCodes && !in_array($attribute['code'], $allowedCodesArray)) {
                continue;
            }

            $attr = Mage::getModel('amconf/attribute')->load($attributeId, 'attribute_id');

            if ($attr->getUseImage()) {
                $config['attributes'][$attributeId]['use_image'] = 1;
                $config['attributes'][$attributeId]['config'] = $attr->getData();

                $smWidth = $attr->getCatSmallWidth() != "0" ? $attr->getCatSmallWidth() : 25;
                $smHeight = $attr->getCatSmallHeight() != "0" ? $attr->getCatSmallHeight() : 25;
                $bigWidth = $attr->getCatBigWidth() != "0" ? $attr->getCatBigWidth() : 50;
                $bigHeight = $attr->getCatBigHeight() != "0" ? $attr->getCatBigHeight() : 50;

                foreach ($attribute['options'] as $i => $option) {
                    if (in_array($attributeId, $productImagesAttributes)) {
                        foreach ($option['products'] as $productId) {
                            $image = $resModel->getAttributeRawValue($productId, 'image', $productParent->getStore());
                            $config['attributes'][$attributeId]['options'][$i]['image'] =
                                (string)Mage::helper('catalog/image')->init($productParent, 'image', $image)
                                    ->resize($smWidth, $smHeight);
                            if (in_array($attr->getCatUseTooltip(), array("2", "3"))) {
                                $config['attributes'][$attributeId]['options'][$i]['bigimage'] =
                                    (string)Mage::helper('catalog/image')->init($productParent, 'image', $image)
                                        ->resize($bigWidth, $bigHeight);
                            }
                            break;
                        }
                    } else {
                        $imgUrl = Mage::helper('amconf')->getImageUrl($option['id'], $smWidth, $smHeight);
                        $tooltipUrl = Mage::helper('amconf')->getImageUrl($option['id'], $bigWidth, $bigHeight);
                        if ($imgUrl == "") {
                            $imgUrl = Mage::helper('amconf')->getPlaceholderUrl($attributeId, $smWidth, $smHeight);
                            $tooltipUrl = Mage::helper('amconf')->getPlaceholderUrl($attributeId, $bigWidth, $bigHeight);
                        }
                        $config['attributes'][$attributeId]['options'][$i]['image'] = $imgUrl;
                        if (in_array($attr->getCatUseTooltip(), array("2", "3")))
                            $config['attributes'][$attributeId]['options'][$i]['bigimage'] = $tooltipUrl;

                        $swatchModel = Mage::getModel('amconf/swatch')->load($option['id']);
                        $config['attributes'][$attributeId]['options'][$i]['color'] = $swatchModel->getColor();
                    }
                }
            }

        }
        $this->_jsonConfig = $config;
        return Zend_Json::encode($config);
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
            'AMASTY_CONF_CONFIGURABLE_CATEGORY',
            Mage::app()->getStore()->getId(),
            (int)Mage::app()->getStore()->isCurrentlySecure(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            Mage::getSingleton('customer/session')->isLoggedIn(),
            Mage::getSingleton('customer/session')->getCustomerGroupId(),
            $this->getProduct()->getId()
        );
    }

    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $products = array();
            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
                /**
                 * Should show all products (if setting set to Yes), but not allow "out of stock" to be added to cart
                 */
                if ($product->isSaleable() || Mage::getStoreConfig('amconf/general/out_of_stock')) {
                    if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                        if (in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
                            $product->getStockItem()->setData('is_in_stock', 1);
                            $products[] = $product;
                        }
                    }
                }
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

    protected function getImagesFromProductsAttributes()
    {
        $collection = Mage::getModel('amconf/product_attribute')->getCollection();
        $collection->addFieldToFilter('use_image_from_product', 1);

        $collection->getSelect()->join(array(
            'prodcut_super_attr' => $collection->getTable('catalog/product_super_attribute')),
            'main_table.product_super_attribute_id = prodcut_super_attr.product_super_attribute_id',
            array('prodcut_super_attr.attribute_id')
        );

        $collection->addFieldToFilter('prodcut_super_attr.product_id', $this->getProduct()->getEntityId());

        $attributes = $collection->getItems();
        $ret = array();

        foreach ($attributes as $attribute) {
            $ret[] = $attribute->getAttributeId();
        }

        return $ret;
    }

    public function getAddToCartUrl($product, $additional = array())
    {
        if ($this->hasCustomAddToCartUrl()) {
            return $this->getCustomAddToCartUrl();
        }
        if ($this->getRequest()->getParam('wishlist_next')) {
            $additional['wishlist_next'] = 1;
        }
        $addUrlKey = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
        $addUrlValue = Mage::getUrl('*/*/*', array('_use_rewrite' => true, '_current' => true));
        $additional[$addUrlKey] = Mage::helper('core')->urlEncode($addUrlValue);
        return $this->helper('checkout/cart')->getAddUrl($product, $additional);
    }

    public function getPriceJsonConfig()
    {
        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        $product = $this->product;
        $_request->setProductClassId($product->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($product->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_regularPrice = $product->getPrice();
        $_finalPrice = $product->getFinalPrice();
        $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true);
        $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice);
        $_tierPrices = array();
        $_tierPricesInclTax = array();
        foreach ($product->getTierPrice() as $tierPrice) {
            $_tierPrices[] = Mage::helper('core')->currency($tierPrice['website_price'], false, false);
            $_tierPricesInclTax[] = Mage::helper('core')->currency(
                Mage::helper('tax')->getPrice($product, (int)$tierPrice['website_price'], true),
                false, false);
        }
        $config = array(
            'productId' => $product->getId(),
            'priceFormat' => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax' => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax' => Mage::helper('tax')->displayPriceIncludingTax(),
            'showBothPrices' => Mage::helper('tax')->displayBothPrices(),
            'productPrice' => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice' => Mage::helper('core')->currency($_regularPrice, false, false),
            'priceInclTax' => Mage::helper('core')->currency($_priceInclTax, false, false),
            'priceExclTax' => Mage::helper('core')->currency($_priceExclTax, false, false),
            'skipCalculate' => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax' => $defaultTax,
            'currentTax' => $currentTax,
            'idSuffix' => '_clone',
            'oldPlusDisposition' => 0,
            'plusDisposition' => 0,
            'plusDispositionTax' => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition' => 0,
            'tierPrices' => $_tierPrices,
            'tierPricesInclTax' => $_tierPricesInclTax,
        );

        $responseObject = new Varien_Object();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object' => $responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                $config[$option] = $value;
            }
        }

        return Mage::helper('core')->jsonEncode($config);
    }

    public function isSalable($product = null)
    {
        $salable = parent::isSalable($product);

        if ($salable !== false) {
            $salable = false;
            if (!is_null($product)) {
                $this->setStoreFilter($product->getStoreId(), $product);
            }

            if (!Mage::app()->getStore()->isAdmin() && $product) {
                $collection = $this->getUsedProductCollection($product)
                    ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                    ->setPageSize(1);
                if ($collection->getFirstItem()->getId()) {
                    $salable = true;
                }
            } else {
                foreach ($this->getUsedProducts(null, $product) as $child) {
                    if ($child->isSalable()) {
                        $salable = true;
                        break;
                    }
                }
            }
        }

        return $salable;
    }

    protected function _construct()
    {
        parent::_construct();

        if (version_compare(Mage::getVersion(), '1.9', '>=')) {
            $productTag = 'catalog_product_' . $this->getProduct()->getId();

            $this->addData(array('cache_lifetime' => false));
            $this->addCacheTag(
                array(
                    Mage_Core_Model_Store::CACHE_TAG,
                    Mage_Core_Block_Abstract::CACHE_GROUP,
                    $productTag
                )
            );
        }
    }

    protected function _parentToHtml()
    {
        return parent::_toHtml();
    }
}
