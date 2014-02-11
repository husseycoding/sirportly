<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('admin/user'), 'sirportly_view', 'TEXT NULL');

$installer->endSetup();