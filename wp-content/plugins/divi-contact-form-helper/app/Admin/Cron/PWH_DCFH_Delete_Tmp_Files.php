<?php

namespace PWH_DCFH\App\Admin\Cron;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Delete_Tmp_Files')) {
    class PWH_DCFH_Delete_Tmp_Files
    {

        const EXPIRY_TIME = 7200; // Two Hours

        private $hook = 'pwh_dcfh_delete_tmp_files';

        private $recurrence = 'pwh_dcfh_q4h';

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            add_filter('cron_schedules', [$this, 'add_cron_schedules']);
            add_action('init', [$this, 'next_scheduled']);
            add_action($this->hook, [$this, 'schedule_event']);
        }

        /**
         * Add Custom Cron Interval
         *
         * @param $schedules
         *
         * @return mixed
         */
        public function add_cron_schedules($schedules)
        {

            $schedules[$this->recurrence] = [
                'interval' => 14400, // Fours Hours
                'display' => __('Every 4 Hours', pwh_dcfh_hc()::TEXT_DOMAIN),
            ];

            return $schedules;
        }

        /**
         * Regsiter Cron Event In WP
         */
        public function next_scheduled()
        {
            // Every Hour
            if (!wp_next_scheduled($this->hook)) {
                wp_schedule_event(time(), $this->recurrence, $this->hook);
            }
        }

        /**
         *  Delete Hourly Temp Files
         */
        public function schedule_event()
        {
            if (!function_exists('list_files')) {
                require_once(ABSPATH.'wp-admin/includes/file.php');
            }
            $upload_temp_dir = pwh_dcfh_helpers()::get_temp_upload_dir();
            $files = list_files($upload_temp_dir, 100, ['index.php', '.htaccess']);
            if (!empty($files)) {
                usort($files, function ($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                $now = time();
                foreach ($files as $file) {
                    if (is_file($file) && file_exists($file)) {
                        $file_explode = explode('-', pathinfo($file, PATHINFO_FILENAME));
                        if (is_array($file_explode)) {
                            $file_timestamp = end($file_explode);
                            if (1 === preg_match('~^[1-9][0-9]*$~', $file_timestamp)) {
                                if (($now - $file_timestamp) > self::EXPIRY_TIME) {
                                    wp_delete_file($file);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}