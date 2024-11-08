<?php

namespace PWH_DCFH\App\Admin\Migration;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Options_Migration')) {
    class PWH_DCFH_Options_Migration
    {

        private static $_instance;

        private static $divi_option_key = 'et_divi';

        private static $options = [];

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_Options_Migration
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }
            self::$options = [
                'pwh_dcfh_backup_enabled' => 'off',
                'pwh_dcfh_backup_email' => get_option('admin_email'),
                'pwh_dcfh_backup_schedule' => 'weekly',
                'pwh_dcfh_stats_enabled' => 'off',
                'pwh_dcfh_smtp_enabled' => 'off',
                'pwh_dcfh_enable_sent_email_log' => 'off',
                'pwh_dcfh_enable_clone_log' => 'off',
                'pwh_dcfh_smtp_host' => '',
                'pwh_dcfh_smtp_encryption' => 'none',
                'pwh_dcfh_smtp_port' => '25',
                'pwh_dcfh_smtp_autotls' => 'off',
                'pwh_dcfh_smtp_authentication' => '',
                'pwh_dcfh_smtp_username' => '',
                'pwh_dcfh_smtp_password' => '',
                'pwh_dcfh_smtp_from_email' => '',
                'pwh_dcfh_smtp_from_name' => '',
            ];

            return self::$_instance;
        }

        /**
         * Add Default Options To Database
         */
        public function add_options()
        {
            $old_options = get_option(self::$divi_option_key);
            foreach (self::$options as $key => $option) {
                if (!isset($old_options[$key])) {
                    $old_options[$key] = $option;
                }
            }
            update_option(self::$divi_option_key, $old_options);
        }

        /**
         * Delete Options From Database
         */
        public function delete_options($delete = false)
        {
            if ($delete) {
                $old_options = get_option(self::$divi_option_key);
                foreach (self::$options as $key => $option) {
                    if (isset($old_options[$key])) {
                        unset($old_options[$key]);
                    }
                }
                update_option(self::$divi_option_key, $old_options);
            }
        }

        /**
         * Delete Registered Cron Jobs
         */
        public function delete_crons()
        {
            $crons = [
                'pwh_dcfh_entries_auto_backup_daily',
                'pwh_dcfh_entries_auto_backup_hourly',
                'pwh_dcfh_entries_auto_backup_twicedaily',
                'pwh_dcfh_entries_auto_backup_weekly',
                'pwh_dcfh_entries_auto_backup_monthly',
                'pwh_dcfh_delete_tmp_files',
            ];
            foreach ($crons as $cron) {
                if (wp_next_scheduled($cron)) {
                    wp_clear_scheduled_hook($cron);
                }
            }
        }

    }
}