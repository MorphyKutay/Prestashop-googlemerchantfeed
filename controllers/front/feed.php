<?php
class GoogleMerchantFeedFeedModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        header("Content-Type: application/xml; charset=utf-8");

        $context = Context::getContext();
        $id_lang = (int)$context->language->id;
        $id_shop = (int)$context->shop->id;
        $currency = Currency::getDefaultCurrency();

        $products = Product::getProducts(
            $id_lang,
            0, 0, 'id_product', 'ASC',
            false, true
        );

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss/>');
        $xml->addAttribute('version', '2.0');
        $xml->addAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        $channel = $xml->addChild('channel');
        $channel->addChild('title', 'Google Merchant Feed');
        $channel->addChild('link', _PS_BASE_URL_);
        $channel->addChild('description', 'Prestashop Google Merchant Product Feed');

        foreach ($products as $product) {
            $p = new Product((int)$product['id_product'], false, $id_lang, $id_shop);

            $item = $channel->addChild('item');

            // Temel bilgiler
            $item->addChild('g:id', $p->id, 'http://base.google.com/ns/1.0');
            $item->addChild('g:title', htmlspecialchars($p->name), 'http://base.google.com/ns/1.0');
            $item->addChild('g:description', htmlspecialchars(strip_tags($p->description_short)), 'http://base.google.com/ns/1.0');
            $item->addChild('g:link', $context->link->getProductLink($p), 'http://base.google.com/ns/1.0');

            // Görsel
            $cover = Product::getCover($p->id);
            if ($cover) {
                $item->addChild('g:image_link',
                    $context->link->getImageLink($p->link_rewrite, $cover['id_image']),
                    'http://base.google.com/ns/1.0'
                );
            }

            // Fiyat (KDV dahil)
            $price = Product::getPriceStatic($p->id, true, null, 2);
            $item->addChild('g:price', number_format($price, 2, '.', '') . ' ' . $currency->iso_code, 'http://base.google.com/ns/1.0');

            // Stok durumu
            $qty = StockAvailable::getQuantityAvailableByProduct($p->id, 0, $id_shop);
            $item->addChild('g:availability', ($qty > 0 ? 'in stock' : 'out of stock'), 'http://base.google.com/ns/1.0');

            // Marka
            $brand = Manufacturer::getNameById($p->id_manufacturer);
            if ($brand) {
                $item->addChild('g:brand', htmlspecialchars($brand), 'http://base.google.com/ns/1.0');
            }

            // GTIN / MPN
            if (!empty($p->ean13)) {
                $item->addChild('g:gtin', $p->ean13, 'http://base.google.com/ns/1.0');
            }
            if (!empty($p->reference)) {
                $item->addChild('g:mpn', $p->reference, 'http://base.google.com/ns/1.0');
            }

            // Durum
            $item->addChild('g:condition', 'new', 'http://base.google.com/ns/1.0');

            // Shipping (kargo)
            $shipping = $item->addChild('g:shipping', '', 'http://base.google.com/ns/1.0');
            $shipping->addChild('g:country', 'TR', 'http://base.google.com/ns/1.0');
            $shipping->addChild('g:service', 'Standard', 'http://base.google.com/ns/1.0');
            $shipping->addChild('g:price', '0.00 ' . $currency->iso_code, 'http://base.google.com/ns/1.0');

            // Ekstra: Google Merchant politikalarına uyum için
            $item->addChild('g:tax', '', 'http://base.google.com/ns/1.0'); // gerekirse vergi oranını ekleyebilirsin
            $item->addChild('g:shipping_weight', '', 'http://base.google.com/ns/1.0'); // ürün ağırlığı varsa
        }

        echo $xml->asXML();
        exit;
    }
}
