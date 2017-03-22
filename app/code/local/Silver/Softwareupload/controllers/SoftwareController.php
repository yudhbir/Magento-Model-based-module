<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Adesh
 * @package     Mymodule_Customerpage
 * @author      Adesh
 * @Website     adeshsuryan.in
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
 
class Silver_Softwareupload_SoftwareController extends Mage_Core_Controller_Front_Action {
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }      
    public function indexAction()
    {
        $this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__("Upload Software"));
        $this->renderLayout();
    }
	/**
     * upload product image action
     * @return string|int
     */
    public function uploadPhotoAction() {
        $io = new Varien_Io_File();
        $io->checkAndCreateFolder(Mage::getBaseDir('media').DS.'catalog'.DS.'product'.DS.'images');

        $uploaddir = Mage::getBaseDir() . '/media/catalog/product/images/';
        $file_name = time() . '-' . $_FILES['image']['name'];
        $file = $uploaddir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $file)) {
            return $file_name;
        } else {
            return 0;
        }
    }
	public function saveproductAction()
	{
		$data=$this->getRequest()->getPost();
		//echo "<pre>";print_r($data);echo "<pre>";
		if (isset($_FILES['image']) && !empty($_FILES['image']))
		 {
			if ($filename = $this->uploadPhotoAction())
			{
				$file_name=$filename;
				$uploaddir = Mage::getBaseDir() . '/media/catalog/product/images/';
				$image = $uploaddir.$filename;
			}
				
		}
		$product_id="";
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);			
		$product = Mage::getModel('catalog/product');
		if(!$product->getIdBySku($data['sku']))
		{
			$product
				->setWebsiteIds(array(1)) //website ID the product is assigned to, as an array
				->setAttributeSetId(4) //ID of a attribute set named 'default'
				->setTypeId('simple') //product type
				->setCreatedAt(strtotime('now')) //product creation time
				->setSku($data['sku']) //SKU
				->setName($data['name']) //product name
				->setWeight($data['weight'])				
				->setStatus(1) //product status (1 - enabled, 2 - disabled)
				->setTaxClassId(2) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
				->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) //catalog and search visibility						
				->setPrice($data['price']) //price in form 11.22
				->setMetaTitle($data['name'])
				->setMetaKeyword($data['name'])
				->setMetaDescription($data['name'])
				->setDescription($data['description'])
				->setShortDescription($data['short_description'])
				
				->setMediaGallery (array('images'=>array (), 'values'=>array ())) //media gallery initialization								
				->addImageToMediaGallery($image, array('image','thumbnail','small_image'), false, false)
				->setData('software_video', $data['software_video'])
				->setData('software_url', $data['software_url'])								
				->setCategoryIds(array($data['categories']));
			$product->save();
			$product_id=$product->getId();
			
			$stockItem = Mage::getModel('cataloginventory/stock_item');
            $stockItem->assignProduct($product);
            $stockItem->setData('is_in_stock', 1);
            $stockItem->setData('stock_id', 1);
            $stockItem->setData('store_id', 1);
            $stockItem->setData('manage_stock', 1);
            $stockItem->setData('use_config_manage_stock', 0);
            $stockItem->setData('min_sale_qty', 1);
            $stockItem->setData('use_config_min_sale_qty', 0);
            $stockItem->setData('max_sale_qty', 1000);
            $stockItem->setData('use_config_max_sale_qty', 0);
            $stockItem->setData('qty', 10);
            $stockItem->save();
			
		 }
		 $model = Mage::getModel('softwareupload/softwaremodel');
		 $res_data = array('user_name'=>$data['customer_name'],'user_id'=>$data['customer_id'],'product_id'=>$product_id,'product_image'=>$file_name);
		 $model->setData($res_data);
		 $insertId = $model->save()->getId();
		 if(!empty($insertId))
		 {
			 Mage::getSingleton('core/session')->addSuccess('Software has been added Successfully');
			  $this->_redirect("*/*/index");
		 }
		 
	}
	public function companyAction()
	{
		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__("Company Profile"));		
        $this->renderLayout();
	}
	public function savecompanyAction()
	{
		 $data=$this->getRequest()->getPost();
		 $model = Mage::getModel('softwareupload/companymodel');
		 $res_data = array('company_name'=>$data['cname'],'company_info'=>$data['company_info'],'user_id'=>$data['user_id']);
		 if(!empty($data['company_id']))
		 {
			 $id=$data['company_id'];
			 $model->load($id)->addData($res_data);
			 $result=$model->setId($id)->save();
			 if(!empty($result))
			 {
				 Mage::getSingleton('core/session')->addSuccess('Company Information has been Updated Successfully');
			 }
		 }else
		 {
			 $model->setData($res_data);
			 $insertId = $model->save()->getId();
			 if(!empty($insertId))
			 {
				 Mage::getSingleton('core/session')->addSuccess('Company Information has been Added Successfully');
			 } 
		 }
		 $this->_redirect("*/*/company");
		
	}
	public function softwarelistAction()
	{
		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__("Software List"));		
        $this->renderLayout();
	}
	
}  