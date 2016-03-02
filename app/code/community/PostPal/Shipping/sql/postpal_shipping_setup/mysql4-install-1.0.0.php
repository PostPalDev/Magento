<?php

$installer = $this;
$installer->startSetup();

$attributeGroup = 'PostPal Shipping';

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entity = 'catalog_product';

$setup->removeAttribute($entity, 'nopostpal');
$setup->addAttribute($entity, 'nopostpal', array(
    'group' => $attributeGroup,
    'position' => 1,
    'type' => 'int',
    'label' => 'PostPal delivery disabled',
    'input' => 'boolean',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'searchable' => 0,
    'filterable' => 0,
    'comparable' => 0,
    'visible_on_front' => 0,
    'visible_in_advanced_search' => 0,
    'unique' => 0,
    'is_configurable' => 0
));

$installer->endSetup();