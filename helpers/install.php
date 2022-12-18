<?php

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'paddle_check_version', 5 );
add_filter( 'wpmu_drop_tables', 'paddle_wpmu_drop_tables' );

function paddle_check_version() {
    if ( defined( 'IFRAME_REQUEST' ) ) {
        return;
    }

    $version_option  = get_option( 'paddle_version' );
    $requires_update = version_compare( get_option( 'paddle_version' ), paddle_wc()->version, '<' );

    if ( ! $version_option || $requires_update ) {
        paddle_install();
        do_action( 'paddle_updated' );
    }
}

function paddle_install() {
    if ( ! is_blog_installed() ) {
        return;
    }

    // Check if we are not already running this routine.
    if ( 'yes' === get_transient( 'paddle_installing' ) ) {
        return;
    }

    // If we made it till here nothing is running yet, lets set the transient now.
    set_transient( 'paddle_installing', 'yes', MINUTE_IN_SECONDS * 10 );

    if ( ! defined( 'PADDLE_INSTALLING' ) ) {
        define( 'PADDLE_INSTALLING', true );
    }

    paddle_create_tables();
    paddle_update_version();
    paddle_maybe_update_db_version();

    delete_transient( 'paddle_installing' );

    flush_rewrite_rules();

    do_action( 'paddle_installed' );
}

function paddle_create_tables() {
    global $wpdb;

    $wpdb->hide_errors();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( paddle_get_schema() );
}

function paddle_get_schema() {
    global $wpdb;

    $collate = '';

    if ( $wpdb->has_cap( 'collation' ) ) {
        $collate = $wpdb->get_charset_collate();
    }

    $tables = "
CREATE TABLE {$wpdb->prefix}paddle_subscriptions (
id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
order_id BIGINT(20) UNSIGNED NOT NULL,
user_id BIGINT(20) UNSIGNED NOT NULL,
subscription_id varchar(255) NOT NULL,
subscription_plan_id varchar(255) NOT NULL,
paddle_user_id varchar(255) NOT NULL,
status varchar(20) NOT NULL,
cancel_url TEXT NOT NULL,
update_url TEXT NOT NULL,
next_bill_date DATETIME NOT NULL default '0000-00-00 00:00:00',
currency char(3) NOT NULL,
unit_price varchar(20) NOT NULL,
PRIMARY KEY (id),
UNIQUE KEY subscription_id (subscription_id(191)
) $collate;";

    return $tables;
}

function paddle_update_version() {
    update_option( 'paddle_version', paddle_wc()->version );
}

function paddle_maybe_update_db_version() {
    paddle_update_db_version();
}

function paddle_update_db_version( $version = null ) {
    update_option( 'paddle_db_version', is_null( $version ) ? paddle_wc()->version : $version );
}

function paddle_get_tables() {
    global $wpdb;

    $tables = array(
        "{$wpdb->prefix}paddle_subscriptions",
    );

    /**
     * Filter the list of known WooCommerce tables.
     *
     * If WooCommerce plugins need to add new tables, they can inject them here.
     *
     * @param array $tables An array of WooCommerce-specific database table names.
     */
    $tables = apply_filters( 'paddle_get_tables', $tables );

    return $tables;
}

function paddle_wpmu_drop_tables( $tables ) {
    return array_merge( $tables, paddle_get_tables() );
}
