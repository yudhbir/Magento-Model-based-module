<?php
class Silver_Softwareupload_Model_Resource_Companymodel extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('softwareupload/companymodel','id');
    }
}