<?php

namespace PWH_DCFH\App\Controllers;

use PWH_DCFH\App\Base\PWH_DCFH_Plugin_Meta_Links;
use PWH_DCFH\App\Base\PWH_DCFH_Public_Enqueue;
use PWH_DCFH\App\Base\PWH_DCFH_Admin_Enqueue;
use PWH_DCFH\App\Admin\CPT\PWH_DCFH_Contact_Form_Entries;
use PWH_DCFH\App\Admin\CPT\PWH_DCFH_Contact_Form_Metaboxes;
use PWH_DCFH\App\Admin\Pages\PWH_DCFH_Create_Post;
use PWH_DCFH\App\Admin\Pages\PWH_DCFH_Create_Email_Template;
use PWH_DCFH\App\Admin\Pages\PWH_DCFH_Email_Templates;
use PWH_DCFH\App\Admin\Pages\PWH_DCFH_Send_Email;
use PWH_DCFH\App\Admin\Pages\PWH_DCFH_Export_CSV;
use PWH_DCFH\App\Admin\Controllers\PWH_DCFH_Dashboard_Stats;
use PWH_DCFH\App\Admin\Controllers\PWH_DCFH_SMTP;
use PWH_DCFH\App\Admin\Cron\PWH_DCFH_Entries_Auto_Backup;
use PWH_DCFH\App\Admin\Settings\PWH_DCFH_Epanel_Settings;
use PWH_DCFH\App\Admin\Logger\PWH_DCFH_Logger;
use PWH_DCFH\App\Admin\Cron\PWH_DCFH_Delete_Tmp_Files;
use PWH_DCFH\App\Admin\Pages\PWH_DCFH_Contact_Forms;
use PWH_DCFH\App\Frontend\DiviContactForm\PWH_DCFH_Filter_Contact_Form;
use PWH_DCFH\App\Frontend\DiviContactForm\PWH_DCFH_Filter_Contact_Form_Fields;
use PWH_DCFH\App\Frontend\Request\PWH_DCFH_Save_Request;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Load')) {
    class PWH_DCFH_Load
    {

        /**
         * Initializer Of The Class.
         */
        public function __construct()
        {
            add_action('init', [$this, 'actions']);
        }

        /**
         * Get Class Instance
         *
         * @param $class
         *
         * @return mixed
         */
        private static function instantiate($class)
        {
            return new $class();
        }

        /**
         * When Plugin Loaded
         */
        public function actions()
        {
            pwh_dcfh_helpers()::create_dir(pwh_dcfh_hc()::UPLOAD_DIR.'/'.pwh_dcfh_hc()::TEMP_UPLOAD_DIR);
        }

        /**
         * Plugin Classes
         *
         * @return string[]
         */
        private static function services()
        {
            $services = [
                PWH_DCFH_Public_Enqueue::class,
                PWH_DCFH_Contact_Form_Entries::class,
                PWH_DCFH_Filter_Contact_Form::class,
                PWH_DCFH_Filter_Contact_Form_Fields::class,
                PWH_DCFH_Save_Request::class,
                PWH_DCFH_Entries_Auto_Backup::class,
                PWH_DCFH_Delete_Tmp_Files::class,
                PWH_DCFH_SMTP::class,
            ];
            if (is_admin()) {
                array_push($services, PWH_DCFH_Plugin_Meta_Links::class);
                array_push($services, PWH_DCFH_Admin_Enqueue::class);
                array_push($services, PWH_DCFH_Contact_Form_Metaboxes::class);
                array_push($services, PWH_DCFH_Create_Post::class);
                array_push($services, PWH_DCFH_Create_Email_Template::class);
                array_push($services, PWH_DCFH_Email_Templates::class);
                array_push($services, PWH_DCFH_Send_Email::class);
                array_push($services, PWH_DCFH_Export_CSV::class);
                array_push($services, PWH_DCFH_Contact_Forms::class);
                array_push($services, PWH_DCFH_Dashboard_Stats::class);
                array_push($services, PWH_DCFH_Epanel_Settings::class);
                array_push($services, PWH_DCFH_Logger::class);
            }

            return $services;
        }

        /**
         * Register Plugin Classes
         */
        public static function register_services()
        {
            foreach (self::services() as $class) {
                $service = self::instantiate($class);
                if (method_exists($service, 'init')) {
                    $service->init();
                }
            }
        }

    }
}