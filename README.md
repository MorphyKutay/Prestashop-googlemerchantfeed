# PrestaShop Google Merchant Feed Module

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

## Overview
This PrestaShop module generates a **dynamic Google Merchant Center XML feed** for your store.  
It includes all required attributes for Google Shopping and updates automatically whenever the feed is accessed.  

### Key Features
- Dynamic XML feed with all required Google Merchant fields:
  - `id`, `title`, `description`, `link`, `image_link`, `availability`, `price`, `brand`, `gtin`, `mpn`, `condition`, `shipping`
- Supports all active products in your PrestaShop store
- Ready for **cronjob scheduling** for automatic daily updates
- Easy to install and configure

## Installation
1. Download or clone the module folder `googlemerchantfeed`.
2. Ensure the folder structure is:
    ```
    googlemerchantfeed/
    ├── googlemerchantfeed.php
    └── controllers/
        └── front/
            └── feed.php
    ```
3. Compress the folder into a ZIP file: `googlemerchantfeed.zip`.
4. Go to PrestaShop Admin → **Modules > Module Manager > Upload a module**.
5. Upload the ZIP file and enable the module.

## Usage
After installation, your feed URL will be:
`https://yourstore.com/index.php?fc=module&module=googlemerchantfeed&controller=feed`


- Copy this URL to **Google Merchant Center** as a **Scheduled Fetch feed**.
- Recommended fetch frequency: **once or twice per day**.

## Cron Job (Optional)
To automatically update the feed on your server:
```bash
0 3 * * * wget -q -O /dev/null "https://yourstore.com/index.php?fc=module&module=googlemerchantfeed&controller=feed"

## Notes

1. Ensure product GTIN, MPN, and brand data are correctly set in your PrestaShop products for full Google compliance.

2. Free shipping is set by default; adjust shipping settings in feed.php if necessary.

