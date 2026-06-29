Catalog Visibility Filter for WooCommerce

A small WooCommerce extension that adds a Catalog visibility filter to the Products admin list.

It helps store managers quickly filter products by their WooCommerce catalog visibility setting, including products set to Hidden.

Features

* Adds a Catalog visibility dropdown to Products → All Products
* Supports filtering by:
    * Visible: shop and search
    * Shop only
    * Search only
    * Hidden
* Uses WooCommerce’s existing product_visibility taxonomy
* Does not modify product data
* Lightweight, single-file plugin

Requirements

* WordPress 6.0 or newer
* WooCommerce 7.0 or newer
* PHP 7.4 or newer

Installation

1. Download the plugin ZIP from the latest GitHub release.
2. Go to Plugins → Add New → Upload Plugin in your WordPress admin.
3. Upload the ZIP file.
4. Activate the plugin.
5. Go to Products → All Products.
6. Use the Filter by catalog visibility dropdown.

How it works

WooCommerce stores catalog visibility using the product_visibility taxonomy.

For example, products set to Hidden are assigned both:

* exclude-from-catalog
* exclude-from-search

This plugin adds an admin filter that queries those visibility terms and returns the matching products in the Products list table.

Notes

This plugin only adds an admin-side filter. It does not change how products appear on the storefront, in search results, or in the WooCommerce Store API.

License

GPL-2.0-or-later