# Changelog

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
