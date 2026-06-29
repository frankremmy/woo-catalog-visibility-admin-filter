<?php


/**
 * Plugin Name: Catalog Visibility Filter for WooCommerce
 * Plugin URI:  https://github.com/YOUR-GITHUB-USERNAME/woo-catalog-visibility-admin-filter
 * Description: Adds a Catalog visibility filter to the WooCommerce Products admin list, including a Hidden products option.
 * Version:     1.0.0
 * Author:      Frank Remmy
 * Author URI:  https://profiles.wordpress.org/frankremmy/
 * License:     GPL-2.0-or-later
 * Text Domain: woo-catalog-visibility-admin-filter
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 */

defined('ABSPATH') || exit;

final class Woo_Catalog_Visibility_Admin_Filter
{
    /**
     * Query-string key used by the Products list filter.
     */
    private const QUERY_VAR = 'catalog_visibility_filter';

    /**
     * Bootstrap hooks.
     */
    public static function init(): void
    {
        add_action('restrict_manage_posts', array(__CLASS__, 'render_filter_dropdown'), 25, 2);
        add_action('pre_get_posts', array(__CLASS__, 'filter_products_admin_query'));
    }

    /**
     * Add the dropdown beside the existing Products list filters.
     *
     * @param string $post_type Current post type.
     * @param string $which Filter location. Usually top or bottom.
     */
    public static function render_filter_dropdown(string $post_type, string $which): void
    {
        if ('product' !== $post_type || 'top' !== $which) {
            return;
        }

        $current = isset($_GET[self::QUERY_VAR]) ? sanitize_key(wp_unslash($_GET[self::QUERY_VAR])) : '';

        $options = array(
            '' => __('Filter by catalog visibility', 'woo-catalog-visibility-admin-filter'),
            'visible' => __('Visible: shop and search', 'woo-catalog-visibility-admin-filter'),
            'catalog' => __('Shop only', 'woo-catalog-visibility-admin-filter'),
            'search' => __('Search only', 'woo-catalog-visibility-admin-filter'),
            'hidden' => __('Hidden', 'woo-catalog-visibility-admin-filter'),
        );

        echo '<select name="' . esc_attr(self::QUERY_VAR) . '" id="catalog-visibility-filter">';

        foreach ($options as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($current, $value, false) . '>' . esc_html($label) . '</option>';
        }

        echo '</select>';
    }

    /**
     * Apply the selected catalog visibility filter to the Products admin query.
     *
     * WooCommerce stores catalog visibility as terms in the product_visibility taxonomy:
     * - exclude-from-catalog
     * - exclude-from-search
     *
     * Hidden products are assigned to both terms.
     *
     * @param WP_Query $query Current query instance.
     */
    public static function filter_products_admin_query(WP_Query $query): void
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        global $pagenow;

        if ('edit.php' !== $pagenow || 'product' !== $query->get('post_type')) {
            return;
        }

        $visibility = isset($_GET[self::QUERY_VAR]) ? sanitize_key(wp_unslash($_GET[self::QUERY_VAR])) : '';

        if ('' === $visibility) {
            return;
        }

        $visibility_tax_query = self::get_visibility_tax_query($visibility);

        if (empty($visibility_tax_query)) {
            return;
        }

        $existing_tax_query = (array)$query->get('tax_query');

        if (empty($existing_tax_query)) {
            $query->set('tax_query', $visibility_tax_query);
            return;
        }

        $combined_tax_query = array('relation' => 'AND');

        foreach ($existing_tax_query as $key => $tax_query_clause) {
            if ('relation' === $key) {
                continue;
            }

            $combined_tax_query[] = $tax_query_clause;
        }

        foreach ($visibility_tax_query as $key => $tax_query_clause) {
            if ('relation' === $key) {
                continue;
            }

            $combined_tax_query[] = $tax_query_clause;
        }

        $query->set('tax_query', $combined_tax_query);
    }

    /**
     * Build the product_visibility tax_query for a catalog visibility value.
     *
     * @param string $visibility Catalog visibility value.
     * @return array<int|string, mixed>
     */
    private static function get_visibility_tax_query(string $visibility): array
    {
        $exclude_from_catalog = 'exclude-from-catalog';
        $exclude_from_search = 'exclude-from-search';

        switch ($visibility) {
            case 'visible':
                return array(
                    array(
                        'taxonomy' => 'product_visibility',
                        'field' => 'slug',
                        'terms' => array($exclude_from_catalog, $exclude_from_search),
                        'operator' => 'NOT IN',
                    ),
                );

            case 'catalog':
                return array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'product_visibility',
                        'field' => 'slug',
                        'terms' => array($exclude_from_search),
                        'operator' => 'IN',
                    ),
                    array(
                        'taxonomy' => 'product_visibility',
                        'field' => 'slug',
                        'terms' => array($exclude_from_catalog),
                        'operator' => 'NOT IN',
                    ),
                );

            case 'search':
                return array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'product_visibility',
                        'field' => 'slug',
                        'terms' => array($exclude_from_catalog),
                        'operator' => 'IN',
                    ),
                    array(
                        'taxonomy' => 'product_visibility',
                        'field' => 'slug',
                        'terms' => array($exclude_from_search),
                        'operator' => 'NOT IN',
                    ),
                );

            case 'hidden':
                return array(
                    array(
                        'taxonomy' => 'product_visibility',
                        'field' => 'slug',
                        'terms' => array($exclude_from_catalog, $exclude_from_search),
                        'operator' => 'AND',
                    ),
                );
        }

        return array();
    }
}

add_action('plugins_loaded', array('Woo_Catalog_Visibility_Admin_Filter', 'init'));