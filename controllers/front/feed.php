<?php

class GoogleMerchantFeedFeedModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        header('Content-Type: application/xml; charset=UTF-8');

        $context  = Context::getContext();
        $id_lang  = (int)$context->language->id;
        $id_shop  = (int)$context->shop->id;
        $currency = Currency::getDefaultCurrency();

        $products = Product::getProducts(
            $id_lang,
            0,
            0,
            'id_product',
            'ASC',
            false,
            true
        );

        // Yerel envanter kaynağı için Google sadece belirli alanları kabul eder.
        $feedType = Tools::getValue('type', 'products');
        if ($feedType === 'local_inventory') {
            $this->renderLocalInventoryFeed($products, $context, $id_lang, $id_shop, $currency);
            exit;
        }

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss/>');
        $xml->addAttribute('version', '2.0');
        $xml->addAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        $channel = $xml->addChild('channel');
        $channel->addChild('title', 'Google Merchant Feed');
        $channel->addChild('link', 'https://' . $_SERVER['HTTP_HOST']);
        $channel->addChild('description', 'Prestashop Google Merchant Product Feed');

        foreach ($products as $product) {
            $p = new Product((int)$product['id_product'], false, $id_lang, $id_shop);

            // Ürün linki (HTTPS zorunlu)
            $link = $context->link->getProductLink(
                $p,
                null,
                null,
                null,
                null,
                $id_shop,
                null,
                true,
                true
            );

            if (strpos($link, '//') === 0) {
                $link = 'https:' . $link;
            }

            // Açıklama temizleme
            $desc = strip_tags($p->description_short ?: $p->description);
            $desc = html_entity_decode($desc, ENT_QUOTES, 'UTF-8');
            $desc = trim($desc);
            $desc = mb_substr($desc, 0, 500);
            $desc = preg_replace('/\s+\S*$/u', '', $desc);

            if (empty($desc)) {
                $desc = $p->name;
            }

            $item = $channel->addChild('item');

            // Zorunlu alanlar
            $item->addChild('g:id', $p->id, 'http://base.google.com/ns/1.0');
            $item->addChild('g:title', htmlspecialchars($p->name), 'http://base.google.com/ns/1.0');
            $item->addChild('g:description', htmlspecialchars($desc), 'http://base.google.com/ns/1.0');
            $item->addChild('g:link', $link, 'http://base.google.com/ns/1.0');

            // Görsel
            $cover = Product::getCover($p->id);
            if ($cover) {
                $image = $context->link->getImageLink(
                    $p->link_rewrite,
                    $cover['id_image'],
                    'large_default'
                );

                if (strpos($image, '//') === 0) {
                    $image = 'https:' . $image;
                }

                $item->addChild('g:image_link', $image, 'http://base.google.com/ns/1.0');
            }

            // Fiyat (KDV dahil)
            $price = Product::getPriceStatic($p->id, true, null, 2);
            $item->addChild(
                'g:price',
                number_format($price, 2, '.', '') . ' ' . $currency->iso_code,
                'http://base.google.com/ns/1.0'
            );

            // Stok
            $qty = StockAvailable::getQuantityAvailableByProduct($p->id, 0, $id_shop);
            $item->addChild(
                'g:availability',
                ($qty > 0 ? 'in stock' : 'out of stock'),
                'http://base.google.com/ns/1.0'
            );

            // Marka
            $brand = Manufacturer::getNameById($p->id_manufacturer);
            if (!empty($brand)) {
                $item->addChild('g:brand', htmlspecialchars($brand), 'http://base.google.com/ns/1.0');
            }

            // GTIN / MPN - ps_product.mpn (PrestaShop 1.7.7+), sonra reference, sonra kombinasyondan
            $ean13 = trim((string) $p->ean13);
            $mpn   = (isset($p->mpn) && (string) $p->mpn !== '')
                ? trim((string) $p->mpn)
                : trim((string) $p->reference);

            if (empty($mpn) && (int) Product::getDefaultAttribute($p->id) > 0) {
                $id_attr = (int) Product::getDefaultAttribute($p->id);
                $comb    = new Combination($id_attr);
                $combMpn = (isset($comb->mpn) && (string) $comb->mpn !== '')
                    ? trim((string) $comb->mpn)
                    : trim((string) $comb->reference);
                if (!empty($combMpn)) {
                    $mpn = $combMpn;
                }
                if (empty($ean13) && !empty($comb->ean13)) {
                    $ean13 = trim((string) $comb->ean13);
                }
            }

            if (!empty($ean13)) {
                $item->addChild('g:gtin', $ean13, 'http://base.google.com/ns/1.0');
            }

            if (!empty($mpn)) {
                $item->addChild('g:mpn', htmlspecialchars($mpn), 'http://base.google.com/ns/1.0');
            }

            // GTIN ve MPN yoksa
            if (empty($ean13) && empty($mpn)) {
                $item->addChild('g:identifier_exists', 'false', 'http://base.google.com/ns/1.0');
            }

            // Durum
            $item->addChild('g:condition', 'new', 'http://base.google.com/ns/1.0');
        }

        echo $xml->asXML();
        exit;
    }

    private function renderLocalInventoryFeed($products, $context, $id_lang, $id_shop, $currency)
    {
        $storeCode = trim((string) Tools::getValue('store_code', ''));
        if ($storeCode === '') {
            $storeCode = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) Configuration::get('PS_SHOP_NAME'));
        }

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss/>');
        $xml->addAttribute('version', '2.0');
        $xml->addAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        $channel = $xml->addChild('channel');
        $channel->addChild('title', 'Google Merchant Local Inventory Feed');
        $channel->addChild('link', 'https://' . $_SERVER['HTTP_HOST']);
        $channel->addChild('description', 'Prestashop Google Merchant Local Inventory Feed');

        foreach ($products as $product) {
            $p = new Product((int) $product['id_product'], false, $id_lang, $id_shop);
            $item = $channel->addChild('item');

            $item->addChild('g:id', $p->id, 'http://base.google.com/ns/1.0');
            $item->addChild('g:store_code', $storeCode, 'http://base.google.com/ns/1.0');

            $qty = StockAvailable::getQuantityAvailableByProduct($p->id, 0, $id_shop);
            $item->addChild(
                'g:availability',
                ($qty > 0 ? 'in stock' : 'out of stock'),
                'http://base.google.com/ns/1.0'
            );

            $price = Product::getPriceStatic($p->id, true, null, 2);
            $item->addChild(
                'g:price',
                number_format($price, 2, '.', '') . ' ' . $currency->iso_code,
                'http://base.google.com/ns/1.0'
            );

            if ($qty > 0) {
                $item->addChild('g:quantity', (int) $qty, 'http://base.google.com/ns/1.0');
            }
        }

        echo $xml->asXML();
    }
}
