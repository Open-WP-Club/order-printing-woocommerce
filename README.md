# Order Printing for WooCommerce

Print WooCommerce orders as clean A4 PDF documents directly from the admin panel.

## Features

- One-click PDF generation from the order edit screen and orders list
- Clean, minimal A4 layout with company branding, addresses, line items, and totals
- Displays site logo (from Customizer) when available, falls back to site name
- HPOS (High-Performance Order Storage) compatible
- Bulgarian translation included

## Requirements

- WordPress 6.0+
- WooCommerce 7.0+
- PHP 7.4+

## Installation

1. Download the latest release zip from [Releases](https://github.com/Open-WP-Club/order-printing-woocommerce/releases)
2. Go to **Plugins > Add New > Upload Plugin** and upload the zip
3. Activate the plugin

### From source

```bash
git clone https://github.com/Open-WP-Club/order-printing-woocommerce.git
cd order-printing-woocommerce
composer install --no-dev
```

## Usage

- **Order edit screen:** Use the "Print Order" meta box and click "Download PDF"
- **Orders list:** Select "Print PDF" from the row actions or bulk actions

## License

GPL-2.0-or-later
