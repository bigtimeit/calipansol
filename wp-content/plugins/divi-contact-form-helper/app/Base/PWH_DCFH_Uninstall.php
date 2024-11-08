<?php

namespace PWH_DCFH\App\Base;

use PWH_DCFH\App\Admin\Migration\PWH_DCFH_Options_Migration;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Uninstall')) {
    class PWH_DCFH_Uninstall
    {

        private static $_instance;

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_Uninstall
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Actions On Uninstall
         */
        public function uninstall()
        {
            if (function_exists('is_multisite') && is_multisite()) {
                $sites = get_sites();
                foreach ($sites as $site) {
                    switch_to_blog($site->blog_id);
                    PWH_DCFH_Options_Migration::instance()->delete_options();
                    PWH_DCFH_Options_Migration::instance()->delete_crons();
                    restore_current_blog();
                }
            } else {
                PWH_DCFH_Options_Migration::instance()->delete_options();
                PWH_DCFH_Options_Migration::instance()->delete_crons();
            }
        }

    }
}