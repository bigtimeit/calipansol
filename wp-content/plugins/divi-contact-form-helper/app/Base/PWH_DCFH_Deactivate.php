<?php

namespace PWH_DCFH\App\Base;

use PWH_DCFH\App\Admin\Migration\PWH_DCFH_Options_Migration;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Deactivate')) {
    class PWH_DCFH_Deactivate
    {

        /**
         * Process Actions When Plugin Dectivate
         */
        public static function deactivate($network_wide)
        {
            if (function_exists('is_multisite') && is_multisite()) {
                if ($network_wide) {
                    $sites = get_sites(); // default count is 100
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
            } else {
                PWH_DCFH_Options_Migration::instance()->delete_options();
                PWH_DCFH_Options_Migration::instance()->delete_crons();
            }
        }
    }
}