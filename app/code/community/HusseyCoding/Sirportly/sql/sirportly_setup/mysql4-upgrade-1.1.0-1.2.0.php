<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('admin/user'), 'sirportly_restrict', 'TEXT NULL');
$installer->getConnection()->addColumn($this->getTable('admin/user'), 'sirportly_user', 'TEXT NULL');

$installer->endSetup();