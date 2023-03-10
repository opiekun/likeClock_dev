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

class Unirgy_RapidFlow_Helper_Data extends Mage_Core_Helper_Data
{
    protected $_ee_gws_filter;
    protected $_moduleActive;

    public function run($profileId, $stopIfRunning=true, array $updateData=array())
    {
        $profile = $this->getProfile($profileId);

        if ($stopIfRunning) {
            try { $profile->stop(); } catch (Exception $e) { };
        }

        if (!empty($updateData)) {
            foreach ($updateData as $k=>$v) {
                if (is_array($v)) {
                    $profile->setData($k, array_merge_recursive($profile->getData($k), $v));
                } else {
                    $profile->setData($k, $v);
                }
            }
        }

        $profile->start()->save()->run();

        return $profile;
    }

    /**
     * @param int|string $profileId profile id or profile title
     * @return mixed
     * @throws \Mage_Core_Exception
     */
    public function getProfile($profileId)
    {
        /* @var $profile Unirgy_RapidFlow_Model_Profile */
        $profile = Mage::getModel('urapidflow/profile');

        if (is_numeric($profileId)) {
            $profile->load($profileId);
        } else {
            $profile->load($profileId, 'title');
        }

        if (!$profile->getId()) {
            Mage::throwException($this->__('Invalid Profile ID'));
        }

        $profile = $profile->factory();
        return $profile;
    }

    public function addAdminhtmlVersion($module='Unirgy_RapidFlow')
    {
        $layout = Mage::app()->getLayout();
        $version = (string)Mage::getConfig()->getNode("modules/{$module}/version");

        $layout->getBlock('before_body_end')->append($layout->createBlock('core/text')->setText('
            <script type="text/javascript">$$(".legality")[0].insert({after:"'.$module.' ver. '.$version.', "});</script>
        '));

        return $this;
    }

    public function formatUrlKey($str)
    {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

    public function isModuleActive($code)
    {
        if(!isset($this->_moduleActive[$code])){
            $module = Mage::getConfig()->getNode( "modules/$code" );
            $model  = Mage::getConfig()->getNode( "global/models/$code" );
            $this->_moduleActive[$code] = $module && $module->is( 'active' ) || $model;
        }
        return $this->_moduleActive[$code];
    }
    public function compareMageVer($ceVer, $eeVer=null, $op='>=')
    {
        return $this->isModuleActive('Enterprise_Enterprise')
            ? version_compare(Mage::getVersion(), !is_null($eeVer) ? $eeVer : $ceVer, $op)
            : version_compare(Mage::getVersion(), $ceVer, $op);
    }

    protected $_hasMageFeature = array();

    public function hasMageFeature($feature)
    {
        if (!isset($this->_hasMageFeature[$feature])) {
            $flag = false;
            switch ($feature) {
            case 'sales_flat':
                $flag = $this->compareMageVer('1.4.1.0', '1.8.0', '>=');
                break;
            case 'attr.used_in_product_listing':
                $flag = $this->compareMageVer('1.3.0');
                break;

            case 'attr.is_used_for_promo_rules':
                $flag = $this->compareMageVer('1.4.1.0', '1.9.0.0');
                break;

            case 'flat_catalog':
            case 'cpsap.website_id':
                $flag = $this->compareMageVer('1.3.1');
                break;

            case 'product.required_options':
                $flag = $this->compareMageVer('1.3.2');
                break;

            case 'attr.is_used_for_price_rules':
                $flag = $this->compareMageVer('1.4.0');
                break;

            case 'product.category_ids':
                $flag = $this->compareMageVer('1.4', '1.6', '<');
                break;

            case 'indexer_1.4':
            case 'table.product_relation':
            case 'table.eav_attribute_label':
            case 'table.catalog_eav_attribute':
            case 'attr.is_wysiwyg_enabled':
                $flag = $this->compareMageVer('1.4', '1.6');
                break;

            case 'catalog.swatches':
                $flag = $this->compareMageVer('1.9.1', '1.14.1');
                break;
            case 'category.include_in_menu':
                $flag = $this->compareMageVer('1.4.1', '1.8');
                break;
            case 'indexer_1.13':
            case 'no_url_path':
                $flag = $this->isModuleActive('Enterprise_Enterprise') && $this->compareMageVer('1.13');
                break;
            case 'unique_url_key':
                $flag = $this->isModuleActive('Enterprise_Enterprise') && $this->compareMageVer('1.13.0.2');
                break;
            case 'multiple_uploader':
                $flag = $this->isModuleActive('Mage_Uploader');
                break;
            }
            $this->_hasMageFeature[$feature] = $flag;
        }
        return $this->_hasMageFeature[$feature];
    }

    public function getMultiCheckSql($casesResults, $defaultValue = null)
    {
        if (empty($casesResults)) {
            return is_null($defaultValue) ? new Zend_Db_Expr("''") : $defaultValue;
        }
        reset($casesResults);
        $key = key($casesResults);
        $value = array_shift($casesResults);
        return $this->getCheckSql($key, $value, $this->getMultiCheckSql($casesResults, $defaultValue));
    }
    public function getCaseSql($valueName, $casesResults, $defaultValue = null)
    {
        $expression = 'CASE ' . $valueName;
        foreach ($casesResults as $case => $result) {
            $expression .= ' WHEN ' . $case . ' THEN ' . $result;
        }
        if ($defaultValue !== null) {
            $expression .= ' ELSE ' . $defaultValue;
        }
        $expression .= ' END';

        return new Zend_Db_Expr($expression);
    }

    public function getCheckSql($expression, $true, $false)
    {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
        } else {
            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
        }

        return new Zend_Db_Expr($expression);
    }

    public function getIfNullSql($expression, $value = 0)
    {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IFNULL((%s), %s)", $expression, $value);
        } else {
            $expression = sprintf("IFNULL(%s, %s)", $expression, $value);
        }

        return new Zend_Db_Expr($expression);
    }

    protected $_isoToPhpFormatConvertRegex;
    protected $_isoToPhpFormatConvert;
    protected $_phpToIsoFormatConvert = array('d' => 'dd'  , 'D' => 'EE'  , 'j' => 'd'   , 'l' => 'EEEE', 'N' => 'e'   , 'S' => 'SS'  ,
                         'w' => 'eee' , 'z' => 'D'   , 'W' => 'ww'  , 'F' => 'MMMM', 'm' => 'MM'  , 'M' => 'MMM' ,
                         'n' => 'M'   , 't' => 'ddd' , 'L' => 'l'   , 'o' => 'YYYY', 'Y' => 'yyyy', 'y' => 'yy'  ,
                         'a' => 'a'   , 'A' => 'a'   , 'B' => 'B'   , 'g' => 'h'   , 'G' => 'H'   , 'h' => 'hh'  ,
                         'H' => 'HH'  , 'i' => 'mm'  , 's' => 'ss'  , 'e' => 'zzzz', 'I' => 'I'   , 'O' => 'Z'   ,
                         'P' => 'ZZZZ', 'T' => 'z'   , 'Z' => 'X'   , 'c' => 'yyyy-MM-ddTHH:mm:ssZZZZ',
                         'r' => 'r'   , 'U' => 'U');
    public function convertIsoToPhpDateFormat($isoFormat)
    {
        if (null === $this->_isoToPhpFormatConvertRegex) {
            uasort($this->_phpToIsoFormatConvert, array($this, 'sortByLengthDescCallback'));
            $this->_isoToPhpFormatConvertRegex = sprintf('/%s/', implode('|',
                                                                         array_map('preg_quote', $this->_phpToIsoFormatConvert)
            ));
        }
        return preg_replace_callback(
            $this->_isoToPhpFormatConvertRegex,
            array($this, 'regexIsoToPhpDateFormatCallback'),
            $isoFormat
        );
    }

    public function sortByLengthDescCallback($a, $b)
    {
        $a = strlen($a);
        $b = strlen($b);
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? 1 : -1;
    }

    public function regexIsoToPhpDateFormatCallback($matches)
    {
        if (null === $this->_isoToPhpFormatConvert) {
            $this->_isoToPhpFormatConvert = array_flip($this->_phpToIsoFormatConvert);
        }
        return isset($this->_isoToPhpFormatConvert[$matches[0]]) ? $this->_isoToPhpFormatConvert[$matches[0]] : $matches[0];
    }

    public function hasEeGwsFilter()
    {
        if(null == $this->_ee_gws_filter){
            $this->_ee_gws_filter = Mage::helper('urapidflow')->isModuleActive('Enterprise_AdminGws')
                        && Mage::getSingleton('admin/session')->isLoggedIn()
                        && !Mage::getSingleton('enterprise_admingws/role')->getIsAll();
        }
        return $this->_ee_gws_filter;
    }

    public function filterEeGwsStoreIds($sIds)
    {
        if ($this->hasEeGwsFilter()) {
            return array_intersect($sIds, Mage::getSingleton('enterprise_admingws/role')->getStoreIds());
        }
        return $sIds;
    }

    public function filterEeGwsWebsiteIds($wIds)
    {
        if ($this->hasEeGwsFilter()) {
            return array_intersect($wIds, Mage::getSingleton('enterprise_admingws/role')->getWebsiteIds());
        }
        return $wIds;
    }

    public function getEeGwsWebsiteIds()
    {
        if ($this->hasEeGwsFilter()) {
            return Mage::getSingleton('enterprise_admingws/role')->getWebsiteIds();
        }
        return array_keys(Mage::app()->getWebsites(true));
    }

    public function getEeGwsStoreIds()
    {
        if ($this->hasEeGwsFilter()) {
            return Mage::getSingleton('enterprise_admingws/role')->getStoreIds();
        }
        return array_keys(Mage::app()->getStores(true));
    }

}
