<?php

namespace PWH_DCFH\App\Admin\Pages;

use PWH_DCFH\App\Admin\Controllers\PWH_DCFH_Contact_Forms_List;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Contact_Forms')) {
    class PWH_DCFH_Contact_Forms
    {

        public $_page;
        public $_table;

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {

            $this->_page = isset($_GET['page']) ? sanitize_key($_GET['page']) : null; // phpcs:ignore
            add_action('admin_menu', [$this, 'menu']);
        }

        /**
         * Register Wp Admin Menu
         */
        public function menu()
        {
            $hook_suffix = add_submenu_page(pwh_dcfh_hc()::ADMIN_MENU_PAGE_SLUG, __('Contact Forms', pwh_dcfh_hc()::TEXT_DOMAIN), __('Forms', pwh_dcfh_hc()::TEXT_DOMAIN),
                'manage_options', 'contact_forms', [$this, 'display_form']);
            add_action("load-$hook_suffix", [$this, 'set_screen_options']);
        }

        /**
         * Set WP Screen Options
         */
        public function set_screen_options()
        {
            $this->_table = new PWH_DCFH_Contact_Forms_List();
        }

        /**
         * Display Form HTML Content
         *
         */
        public function display_form()
        { ?>
            <div class="wrap" id="page-email-templates-list">
                <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
                <hr class="wp-header-end">
                <span class="spinner"></span>
                <div class="js-response"></div>
                <form method="get" autocomplete="off">
                    <?php
                    $this->_table->prepare_items();
                    $this->_table->display();
                    ?>
                    <input type="hidden" name="post_type" value="pwh_dcfh"/>
                    <input type="hidden" name="page" value="<?php echo esc_html($this->_page); ?>"/>
                    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('bulk-action-'.$this->_page); // phpcs:ignore
                    ?>"/>
                </form>
            </div>
            <?php
        }

    }
}