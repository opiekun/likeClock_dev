<?php

class Unirgy_RapidFlow_Model_Mysql4_Abstract_Fixed
    extends Unirgy_RapidFlow_Model_Mysql4_Abstract
{
    protected $_dataType;
    protected $_rowTypeSelect = array();
    protected $_rowTypeCount = array();
    protected $_totalCount = 0;
    protected $_rowNum = 0;
    protected $_cnt = array();
    protected $_newRows = array();
    protected $_newRowTypes = array();
    protected $_newRowActions = array();
    protected $_newRowMethods = array();
    protected $_newRefreshHoRoPids = array();
    protected $_isLastPage = false;
    protected $_rowTypes = array();
    protected $_rowTypeFields = array();

    protected $_exportConvertFields = array();
    protected $_startLine;

    public function import()
    {
        $benchmark = false;

        $profile = $this->_profile;
        $logger = $profile->getLogger();

        $tune = Mage::getStoreConfig('urapidflow/finetune');
        $this->setFinetuneParams($tune);

        $this->_cnt = array();
        $rowNum = 0;

        $useTransactions = $profile->getUseTransactions();

        $this->_profile->activity('Fetching number of rows');

        $profile->ioOpenRead();
        $count = 0;
        while ($profile->ioRead()) {
            $count++;
        }
        $profile->setRowsFound($count)->setStartedAt(now())->sync(true, array('rows_found', 'started_at'), false);
        $profile->ioSeekReset();

        $this->_rowTypes = (array)$profile->getData('options/row_types');

        $this->_prepareRowTypeData();

        $eventVars = array(
            'profile' => &$this->_profile,
            'skus' => &$this->_skus,
        );

        $this->_profile->activity('Importing');
#memory_get_usage(true);
if ($benchmark) Mage::log("============================= IMPORT START: ".memory_get_usage(true).', '.memory_get_peak_usage(true));

        $this->_isLastPage = false;

        $this->_getStoreIds();

        $count = 0;
        // data will loaded page by page to conserve memory
        for ($page = 0; ; $page++) {
            $this->_startLine = 1 + $page * $this->_pageRowCount;

            try {

            $this->_checkLock();

            if ($useTransactions) {
                $this->_write->beginTransaction();
            }

#memory_get_usage(true);
if ($benchmark) Mage::log("================ PAGE START: ".memory_get_usage(true).', '.memory_get_peak_usage(true));

            $this->_importResetPageData();
#memory_get_usage(true);
if ($benchmark) Mage::log("_importResetPageData: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
            $this->_importFetchNewData();
#memory_get_usage(true);
if ($benchmark) Mage::log("_importFetchNewData: ".memory_get_usage(true).', '.memory_get_peak_usage(true));
            $this->_importProcessNewData();
#memory_get_usage(true);
if ($benchmark) Mage::log("_importProcessNewData: ".memory_get_usage(true).', '.memory_get_peak_usage(true));

            $this->_checkLock();

            Mage::dispatchEvent('urapidflow_product_extra_import_after_fetch', array('vars'=>$eventVars));

            $this->_importSaveRows();

            $this->_refreshHasOptionsRequiredOptions($this->_newRefreshHoRoPids);

            Mage::dispatchEvent('urapidflow_product_extra_import_after_save', array('vars'=>$eventVars));
#memory_get_usage(true);
if ($benchmark) Mage::log("_importSaveRows: ".memory_get_usage(true).', '.memory_get_peak_usage(true));

            $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
                ->setSnapshotAt(now())->sync();

            if ($useTransactions) {
                $this->_write->commit();
            }
            } catch (Exception $e) {
                if ($useTransactions) {
                    $this->_write->rollBack();
                }
#print_r($e);
                throw $e;
            }

            if ($this->_isLastPage) {
                break;
            }
            if ($this->_pageSleepDelay) {
                sleep($this->_pageSleepDelay);
            }
        }

        $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
            ->setSnapshotAt(now())->sync();

        $this->_profile->activity($this->__('Running after import procedures'));

        $this->_afterImport($this->_cnt);

        $profile->ioClose();

        return $this;
    }

    protected function _prepareRowTypeData()
    {
        $rowTypes = Mage::getSingleton('urapidflow/config')->getRowTypes($this->_dataType);

        $this->_rowTypeFields = array();
        foreach ($rowTypes as $rowType => $rowNode) {
            foreach ($rowNode->columns as $fieldName => $fieldNode) {
                $this->_rowTypeFields[$rowType][$fieldName] = $fieldNode;
            }
        }
    }

    protected function _importResetPageData()
    {
        $this->_newRows = array();
        $this->_newRowTypes = array();
        $this->_newRowActions = array();
        $this->_newRowMethods = array();
        $this->_newRefreshHoRoPids = array();
    }

    protected function _importFetchNewData()
    {
        $profile = $this->_profile;

        for ($i1 = 0; $i1 < $this->_pageRowCount; $i1++) {
            $row = $profile->ioRead();
            if (!$row) {
                // last row
                $this->_isLastPage = true;
#var_dump($this->_newData);
                return true;
            }
            if ($row[0]==='' || empty($row[0])) {
                $profile->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_PROCESSED)
                        ->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_EMPTY);
                continue;
            }

            $method = false;
            $rowType = strtoupper(trim($row[0]));
            $rowAction = $rowType[0];
            switch ($rowAction) {
            case '#': // comment
                break;

            case '-': // delete
                $rowType = substr($rowType, 1);
                $method = '_deleteRow'.$rowType;
                break;

            case '%': // rename
                $rowType = substr($rowType, 1);
                $method = '_renameRow'.$rowType;
                break;

            case '+': // add/update
                $rowType = substr($rowType, 1);
                $method = '_importRow'.$rowType;
                break;

            default: // add/update
                $rowAction = '+';
                $method = '_importRow' . $rowType;
            }
            if ($method === false) {
                $profile->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_EMPTY);
                continue;
            }
            if (!is_callable(array($this, $method))) {
                $profile->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_PROCESSED);
                Mage::throwException($this->__('Invalid row type: %s', $rowType));
            }
            if ($this->_rowTypes && !in_array($rowType, $this->_rowTypes)) {
                $profile->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_PROCESSED)->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_NOCHANGE);
                continue;
            }

            $lineNum = $this->_startLine + $i1;
            $this->_newRows[$lineNum] = $row;
            $this->_newRowTypes[$lineNum] = $rowType;
            $this->_newRowActions[$lineNum] = $rowAction;
            $this->_newRowMethods[$lineNum] = $method;
        }
        return false;
    }

    protected function _importProcessNewData()
    {
        //placeholder
    }

    protected function _importSaveRows()
    {
        $profile = $this->_profile;
        $logger = $profile->getLogger();

        foreach ($this->_newRows as $lineNum=>$row) {
            try {
                $profile->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_PROCESSED);
                $logger->setLine($lineNum)->setColumn(0);

                $result = null;
                #echo '<br/>'.$rowNum.', '.$row[0].', ';
                #echo $rowNum.' ';
                $this->_row = $row;

                $method = $this->_newRowMethods[$lineNum];
                $result = $this->$method($row);
                $this->_cnt[$row[0]] = empty($this->_cnt[$row[0]]) ? 1 : $this->_cnt[$row[0]]+1;

                switch ($result) {
                case self::IMPORT_ROW_RESULT_SUCCESS:
                    $profile->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_SUCCESS);
                    $logger->success();
                    break;
                case self::IMPORT_ROW_RESULT_NOCHANGE:
                    $profile->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_NOCHANGE);
                    break;
                case self::IMPORT_ROW_RESULT_EMPTY:
                    $profile->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_EMPTY);
                    break;
                case self::IMPORT_ROW_RESULT_DEPENDS:
                    $profile->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_DEPENDS);
                    break;
                case self::IMPORT_ROW_RESULT_ERROR:
                    $profile->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_ERRORS)
                            ->addValue(Unirgy_RapidFlow_Model_Profile::NUM_ERRORS);
                    break;
                }
            } catch (Exception $e) {
                $result = self::IMPORT_ROW_RESULT_ERROR;
                $profile->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_ERRORS)
                        ->addValue(Unirgy_RapidFlow_Model_Profile::NUM_ERRORS);
                $logger->error($e->getMessage());
            }
            $this->_cnt[$result] = empty($this->_cnt[$result]) ? 1 : $this->_cnt[$result]+1;
        }

        return $this;
    }

    /**
     * Export one or multiple row types
     *
     * @throws Unirgy_RapidFlow_Exception_Stop
     */
    public function export()
    {
        $profile = $this->_profile;

        $tune = Mage::getStoreConfig('urapidflow/finetune');
        $this->setFinetuneParams($tune);

        $this->_profile->activity($this->__('Preparing data'));

        $profile->ioOpenWrite();
        $rowTypes = $profile->getData('options/row_types');
        if (!$rowTypes) {
            $rowTypes = array_keys(Mage::getSingleton('urapidflow/config')->getRowTypes($profile->getDataType()));
        }

        $totalCount = 0;
        foreach ($rowTypes as $rowType) {
            $method = '_exportInit'.$rowType;
            if (!is_callable(array($this, $method))) {
                Mage::log('Not callable: '.$rowType.': '.$method);
                continue;
            }
            // initialize export select query
            $this->$method();
            if (!$this->_select) {
                Mage::log('No select: '.$method);
                continue;
            }
            $this->_rowTypeCount[$rowType] = $this->_fetchRowCount($this->_select);
            if ($this->_rowTypeCount[$rowType]) {
                $this->_rowTypeSelect[$rowType] = $this->_select;
                $totalCount += 1+$this->_rowTypeCount[$rowType];
            }
        }
        $profile->setRowsFound($totalCount)->setStartedAt(now())->sync(true, array('rows_found', 'started_at'), false);

        $this->_rowNum = 0;
        foreach ($this->_rowTypeSelect as $rowType=>$select) {
            $this->_checkLock();

            $profile->activity($this->__('Exporting: %s', $rowType));

            $this->_select = $select;
            $this->_exportRowType($rowType);

            if ($this->_pageSleepDelay) {
                sleep($this->_pageSleepDelay);
            }
        }
        $profile->ioClose();
    }

    protected function _fetchRowCount($select)
    {
        $countSelect = clone $select;

        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

        if (method_exists($countSelect, 'columns')) {
            $countSelect->reset(Zend_Db_Select::COLUMNS)->columns('count(*)', 'main');
            $count = $this->_read->fetchOne($countSelect);
        } else {
            $sql = $countSelect->__toString();
            $sql = preg_replace('/^select\s+.+?\s+from\s+/is', 'select count(*) from ', $sql);
            $count = $this->_read->fetchOne($sql);
        }

        return $count;
    }

    protected function _exportRowType($rowType)
    {
        $profile = $this->_profile;
        $logger = $profile->getLogger();

        $this->_exportConvertFields = array();

        $cbMethod = !empty($this->_exportRowCallback[$rowType]) ? $this->_exportRowCallback[$rowType] : null;

        // fetch rows data
        $result = $this->_select->query();
        $row = $result->fetch();
        $columns = array();
        if ($row) {
            $columns = $this->getRowTypeColumns($rowType);
            $header = array_keys($columns);
            array_unshift($header, '##'.$rowType);
            $profile->ioWriteHeader($header);
            $logger->setLine(++$this->_rowNum);
            $profile->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_PROCESSED)->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_SUCCESS);
        }
        $count = 0;
        while ($row) {
            $logger->setLine(++$this->_rowNum);
            $count++;
            if ($cbMethod) {
                try {
                    if ($this->$cbMethod($row) === false) {
                        --$this->_rowNum;
                        $row = $result->fetch();
                        continue;
                    }
                } catch(Exception $e) {
                    $profile->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_ERRORS);
                    $logger->error($e->getMessage());
                }
            }
            foreach ($this->_exportConvertFields as $k) {
                $row[$k] = $this->_convertEncoding($row[$k]);
            }
            $r = array($rowType);
            foreach ($columns as $k => $c) {
                if (!isset($row[$k])) {
                    $r[] = '';
                    continue;
                }
                $v   = $row[$k];
                $r[] = isset($this->_attrOptionsByValue[$k][$v])? $this->_attrOptionsByValue[$k][$v]: $v;
            }

            $r = $this->_convertEncoding($r);
            $profile->ioWrite($r);
            $profile->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_PROCESSED)->addValue(Unirgy_RapidFlow_Model_Profile::ROWS_SUCCESS);

            if ($count == $this->_pageRowCount) {
                $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
                        ->setSnapshotAt(now())->sync();

                $this->_checkLock();

                if ($this->_pageSleepDelay) {
                    sleep($this->_pageSleepDelay);
                }
                $count = 0;
            }

            $row = $result->fetch();
        }

        $profile->setMemoryUsage(memory_get_usage(true))->setMemoryPeakUsage(memory_get_peak_usage(true))
            ->setSnapshotAt(now())->sync();
    }

    protected function _afterImport($cnt)
    {

    }

    protected function _skipStore($sId, $column=0, $noEmpty=true)
    {
        if ($noEmpty && !$sId) {
            $this->_profile->getLogger()->setColumn($column);
            Mage::throwException($this->__('Invalid store'));
        }
        return $this->_storeIds && !in_array($sId, $this->_storeIds);
    }

    /**
     * @param $rowType
     * @return mixed
     */
    protected function getRowTypeColumns($rowType)
    {
        return Mage::getSingleton('urapidflow/config')->getRowTypeColumns($rowType);
    }
}
