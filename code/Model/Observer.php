<?php

class BenDechrai_MetadataManager_Model_Observer
{
    public function controller_action_layout_generate_blocks_after(Varien_Event_Observer $observer)
    {
        switch($observer->getEvent()->getAction()->getRequest()->getControllerName()) {
            case 'product':
                $this->_process_product($observer);
                break;
            case 'category':
                $this->_process_category($observer);
                break;
        }
        return $this;
    }

    private function _process_product(Varien_Event_Observer $observer)
    {
        if (Mage::registry('current_product')) {
            $product = Mage::registry('current_product');

            $description = ''; //trim($product->getMetaDescription());
            $keywords = ''; //trim($product->getMetaKeywords());
            if($description=='' || $keywords=='') {

                $autoDescription = '';
                $autoDescription = $product->getDescription() . ' ' . $product->getShortDescription();

                $autoKeywords = '';
                $autoKeywords .= preg_replace("#[\r\n]+#", ', ', $product->getEnhanceddataOemnumbersFilt());
                $autoKeywords .= ', ' . $product->getAttributeText('brand');
                $autoKeywords .= ', ' . $product->getName();

            }

            if($description == '') $observer->getLayout()->getBlock('head')->setDescription($autoDescription);
            if($keywords == '') $observer->getLayout()->getBlock('head')->setKeywords($autoKeywords);

        }
        return $this;
    }

    private function _process_category(Varien_Event_Observer $observer)
    {
        if (Mage::registry('current_category')) {
            $category = Mage::registry('current_category');

            $title = trim($category->getMetaTitle());
            $description = trim($category->getMetaDescription());
            $keywords = trim($category->getMetaKeywords());
            if($title=='' || $description=='' || $keywords=='') {

                $autoTitle = '';
                $autoDescription = '';
                $autoKeywords = '';

                $categoryGroups = $this->getCategoryGroups();
                $categoryData = array(
                  'Make' => null,
                  'Model' => null,
                  'Category' => null,
                  'Sub-Category' => null,
                );

                $categoryGroup = $categoryGroups[$category->getCategoryGroup()];
                $categoryData[$categoryGroup] = $category->getName();

                $parent = Mage::GetModel('catalog/category')->load($category->getParentId());
                while(intval($parent->getCategoryGroup())>0) {
                  $categoryGroup = $categoryGroups[$parent->getCategoryGroup()];
                  $categoryData[$categoryGroup] = $parent->getName();
                  $parent = Mage::GetModel('catalog/category')->load($parent->getParentId());
                }

                if(!is_null($categoryData['Make'])) {
                  $autoTitle .= $categoryData['Make'];
                  $autoDescription .= $categoryData['Make'];
                  $autoKeywords .= $categoryData['Make'] . ', ';
                }

                if(!is_null($categoryData['Model'])) {
                  $autoTitle .= ' ' . $categoryData['Model'];
                  $autoDescription .= ' ' . $categoryData['Model'];
                  $autoKeywords .= $categoryData['Model'] . ', ';
                }

                if(!is_null($categoryData['Category'])) {

                  if(!is_null($categoryData['Sub-Category'])) {
                    $autoTitle .= ' ' . $categoryData['Sub-Category'];
                    $autoKeywords .= $categoryData['Sub-Category'] . ', ';
                  } else {
                    $autoTitle .= ' ' . $categoryData['Category'];
                    $autoKeywords .= $categoryData['Category'] . ', ';
                  }

                }

                if(is_null($categoryData['Category'])) {
                  $autoTitle .= ' Car Parts Online';
                } elseif(is_null($categoryData['Make'])) {
                  $autoTitle .= ' - Buy Online';
                }

                $autoTitle = preg_replace('# +#', ' ', $autoTitle) . ' | Run Auto Parts';
                $autoDescription = "After $autoDescription spare parts? Run Auto Parts is Australia's leader in Euro car parts with a massive online range and free delivery available across Australia";
                $autoKeywords .= "car parts, auto parts, vehicle parts";
            }
            if($title == '') $observer->getLayout()->getBlock('head')->setTitle($autoTitle);
            if($description == '') $observer->getLayout()->getBlock('head')->setDescription($autoDescription);
            if($keywords == '') $observer->getLayout()->getBlock('head')->setKeywords($autoKeywords);

        }
        return $this;
    }

    private function getCategoryGroups() {
        static $categoryGroups = null;
        if(is_null($categoryGroups)) {
            $categoryGroups = array();
            $attributeCollection = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setCodeFilter('category_group')
                ->getFirstItem()
                ->getSource()
                ->getAllOptions(false);
            foreach($attributeCollection as $attribute) {
                $categoryGroups[$attribute['value']] = $attribute['label'];
            }
        }
        return $categoryGroups;
    }


}

