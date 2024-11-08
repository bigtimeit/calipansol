<?php

namespace PWH_DCFH\App\Base;

use PWH_DCFH\App\Admin\Migration\PWH_DCFH_Options_Migration;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Activate')) {
    class PWH_DCFH_Activate
    {

        /**
         * Process Actions When Plugin Activate
         */
        public static function activate($network_wide)
        {
            PWH_DCFH_Plugin_Requirements_Check::instance()->check();
            if (function_exists('is_multisite') && is_multisite()) {
                if ($network_wide) {
                    $sites = get_sites(); // default count is 100
                    foreach ($sites as $site) {
                        switch_to_blog($site->blog_id);
                        PWH_DCFH_Options_Migration::instance()->add_options();
                        restore_current_blog();
                    }
                } else {
                    PWH_DCFH_Options_Migration::instance()->add_options();
                }
            } else {

                PWH_DCFH_Options_Migration::instance()->add_options();
            }
        }

    }
}