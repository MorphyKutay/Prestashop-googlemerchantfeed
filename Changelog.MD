# Changelog

## [1.1.2] - 2026-04-01
### Added
- Added local inventory feed mode with query parameter: `type=local_inventory`.
- Added support for required local inventory field: `g:store_code` (via `store_code` query parameter).
- Added optional `g:quantity` output for in-stock products in local inventory feed.

### Fixed
- Fixed Merchant Center local inventory validation issues caused by sending product-feed-only attributes (`title`, `description`, `brand`, `image_link`, `mpn`, `condition`, etc.) in local inventory source.
- Local inventory feed now emits only supported attributes for local inventory processing.

### Changed
- Feed controller now supports two output modes:
    - Default product feed (`products`)
    - Local inventory feed (`local_inventory`)
- Added fallback store code sanitization from shop name when `store_code` is not provided.

### Notes
- `store_code` must match the Business Profile store code exactly; Merchant ID is not a valid store code.
- Use local inventory URL format:
  `.../feed?type=local_inventory&store_code=YOUR_STORE_CODE`

## [1.1.1] - 2025-09-30
### Added
- Added missing fields for Google Merchant Feed:
    - `g:brand` (brand)
    - `g:gtin` (EAN-13 barcode)
    - `g:mpn` (product reference code)
    - `g:condition` (product condition, new)
    - `g:shipping` (shipping information: country, service, and price)
- Prices are now sent with currency using `number_format` (`XX.XX TRY`).

### Fixed
- Cleaned `g:description` and `g:title` using `htmlspecialchars(strip_tags())` to remove HTML tags and special characters.
- Stock availability (`g:availability`) is now correctly set to `in stock` or `out of stock`.
- Free shipping assumption applied with shipping price (`g:shipping>g:price`).
- Missing information and misleading declarations issues were fixed according to Google Merchant policies.

### Changed
- XML feed structure optimized using `SimpleXMLElement`.
- Product titles and descriptions updated to avoid misleading users.

### Notes
- Products with empty brand, barcode, or reference fields will skip the corresponding tags.
- It is recommended to update the feed for products missing price or stock information.
