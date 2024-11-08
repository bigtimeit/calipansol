<?php

namespace PWH_DCFH\App\Admin\Pages;

use PWH_DCFH\App\Admin\Controllers\PWH_DCFH_Email_Templates_List;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Email_Templates')) {
    class PWH_DCFH_Email_Templates
    {

        private $_post_type;

        public $_table;

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            $this->_post_type = pwh_dcfh_hc()::POST_TYPE;
            $_page = isset($_GET['page']) ? sanitize_key($_GET['page']) : null; // phpcs:ignore
            add_action('admin_menu', [$this, 'menu']);
            if ('email_templates_list' === $_page) {
                add_action('admin_notices', [$this, 'register_admin_notice']);
            }
        }

        /**
         * Register Wp Admin Menu
         */
        public function menu()
        {
            $hook_suffix = add_submenu_page(pwh_dcfh_hc()::ADMIN_MENU_PAGE_SLUG, __('Email Templates', pwh_dcfh_hc()::TEXT_DOMAIN), __('Email Templates', pwh_dcfh_hc()::TEXT_DOMAIN),
                'manage_options', 'email_templates_list', [$this, 'display_form']);
            add_action("load-$hook_suffix", [$this, 'set_screen_options']);
        }

        /**
         * Set WP Screen Options
         */
        public function set_screen_options()
        {
            $this->_table = new PWH_DCFH_Email_Templates_List();
        }

        /**
         * Display Form HTML Content
         *
         */
        public function display_form()
        {
            $this->_table->prepare_items();
            ?>
            <div class="wrap" id="page-email-templates-list">
                <h1 class="wp-heading-inline"><?php esc_html_e(get_admin_page_title(), pwh_dcfh_hc()::TEXT_DOMAIN); ?></h1>
                <a href="<?php echo esc_url(add_query_arg(['post_type' => $this->_post_type, 'page' => 'create_email_template'],
                    admin_url('edit.php'))); ?>" class="page-title-action"><?php esc_html_e('Add New Template', pwh_dcfh_hc()::TEXT_DOMAIN); ?></a>
                <hr class="wp-header-end">
                <?php pwh_dcfh_helpers()::display_message(); ?>
                <span class="spinner"></span>
                <div class="js-response"></div>
                <form method="get" autocomplete="off">
                    <?php $this->_table->display(); ?>
                </form>
            </div>
            <?php
        }

        /**
         * Register Admin Notice
         */
        public function register_admin_notice()
        {
            if (isset($_GET['action'])) {                                                          // phpcs:ignore
                $success = isset($_GET['success']) ? sanitize_text_field($_GET['success']) : false;// phpcs:ignore
                $class = $success ? 'notice notice-success is-dismissible' : 'notice notice-error is-dismissible';
                $action = sanitize_text_field($_GET['action']);// phpcs:ignore
                if ('create' === $action) {
                    pwh_dcfh_helpers()::add_message(__('The template is created.', pwh_dcfh_hc()::TEXT_DOMAIN));
                } elseif ('edit' === $action) {
                    pwh_dcfh_helpers()::add_message(__('The template is updated.', pwh_dcfh_hc()::TEXT_DOMAIN));
                } elseif ('delete' === $action) {
                    pwh_dcfh_helpers()::add_message(__('The template is deleted.', pwh_dcfh_hc()::TEXT_DOMAIN));
                } elseif ('' == $action) {
                    pwh_dcfh_helpers()::add_error_message(__('Something went wrong. Please refresh and try again.', pwh_dcfh_hc()::TEXT_DOMAIN));
                }
            }
        }

    }
}