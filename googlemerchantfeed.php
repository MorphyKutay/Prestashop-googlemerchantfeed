<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class GoogleMerchantFeed extends Module
{
    public function __construct()
    {
        $this->name = 'googlemerchantfeed';
        $this->tab = 'advertising_marketing';
        $this->version = '1.1.2';
        $this->author = 'MorphyKutay';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Google Merchant Feed');
        $this->description = $this->l('Generate Google Merchant Center XML feed with all required attributes.');
    }

    public function install()
    {
        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }
}
