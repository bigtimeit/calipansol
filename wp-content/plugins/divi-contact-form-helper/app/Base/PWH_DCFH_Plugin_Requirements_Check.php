<?php

namespace PWH_DCFH\App\Base;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Plugin_Requirements_Check')) {
    class PWH_DCFH_Plugin_Requirements_Check
    {

        private static $_instance;

        private static $php_version = '7.1';

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_Plugin_Requirements_Check
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Check Requirements
         */
        public function check()
        {
            if (!self::is_php_7x()) {
                deactivate_plugins(pwh_dcfh_hc()::BASENAME);
                wp_die('<p>'.sprintf(esc_html__('This plugin can not be activated because it requires at least PHP version %1$s. Please contact your server administrator to update server.',
                        esc_html(pwh_dcfh_hc()::TEXT_DOMAIN)), esc_html(self::$php_version)).'</p> <a href="'.esc_url(admin_url('plugins.php')).'">'.esc_html__('Go Back',
                        pwh_dcfh_hc()::TEXT_DOMAIN).'</a>');
            }
            if (!self::is_dom_document_module_installed()) {
                deactivate_plugins(pwh_dcfh_hc()::BASENAME);
                wp_die('<p>'.esc_html__('This plugin can not be activated because it requires DomDocument module to be installed. Please contact your server administrator to update server.',
                        esc_html(pwh_dcfh_hc()::TEXT_DOMAIN)).'</p> <a href="'.esc_url(admin_url('plugins.php')).'">'.esc_html__('Go Back',
                        pwh_dcfh_hc()::TEXT_DOMAIN).'</a>');
            }
            if (!self::is_divi()) {
                deactivate_plugins(pwh_dcfh_hc()::BASENAME);
                wp_die('<p>'.sprintf(esc_html__('This plugin can not be activated because it requires DIVI Theme. Your current theme is %1$s.', esc_html(pwh_dcfh_hc()::TEXT_DOMAIN)),
                        esc_html(wp_get_theme()->get('Name'))).'</p> <a href="'.esc_url(admin_url('plugins.php')).'">'.esc_html__('Go Back', pwh_dcfh_hc()::TEXT_DOMAIN).'</a>');
            }
        }

        /**
         * Check PHP Version
         *
         * @return bool
         */
        private static function is_php_7x()
        {
            $php_version = version_compare(PHP_VERSION, esc_html(self::$php_version), '>=');
            if ($php_version) {
                return true;
            }

            return false;
        }

        /**
         * Check Is DIVI
         *
         * @return bool
         */
        private static function is_divi()
        {
            $active_theme = wp_get_theme()->get('Name');
            $parent_theme = wp_get_theme()->get('Template');
            $divi_builder = is_plugin_active('divi-builder/divi-builder.php');
            $divi_ghoster = is_plugin_active('divi-ghoster/divi-ghoster.php');
            if (
                'divi' === strtolower($active_theme)
                || 'divi' === strtolower($parent_theme)
                || 'extra' === strtolower($active_theme)
                || 'extra' === strtolower($parent_theme)
                || $divi_builder
                || $divi_ghoster
            ) {
                return true;
            }

            return false;
        }

        /**
         * Check is DOM Document Module Exist
         *
         * @return bool
         */
        private function is_dom_document_module_installed()
        {
            if (class_exists('DOMDocument')) {
                return true;
            }

            return false;
        }

    }
}