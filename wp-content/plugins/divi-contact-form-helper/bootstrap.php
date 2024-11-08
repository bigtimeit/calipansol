<?php

use PWH_DCFH\App\Controllers\PWH_DCFH_HC;
use PWH_DCFH\App\Helpers\PWH_DCFH_Helpers;
use PWH_DCFH\App\Admin\Controllers\PWH_DCFH_Email_Template_Handler;
use PWH_DCFH\App\Controllers\PWH_DCFH_DB_Handler;
use PWH_DCFH\App\Admin\Controllers\PWH_DCFH_User_Handler;
use PWH_DCFH\App\Admin\Controllers\PWH_DCFH_Post_Meta_Handler;
use PWH_DCFH\App\Admin\Logger\PWH_DCFH_Logger;
use PWH_DCFH\App\Frontend\Controllers\PWH_DCFH_Email_Merge_Data_Tags;
use PWH_DCFH\App\Helpers\PWH_DCFH_Module_Helpers;

// Auto Load Vendor
require_once __DIR__.'/vendor/autoload.php';
/**
 * General Helpers
 *
 * @return PWH_DCFH_Helpers
 */
if (!function_exists('pwh_dcfh_helpers')):
    function pwh_dcfh_helpers()
    {
        return PWH_DCFH_Helpers::instance();
    }
endif;
/**
 * General Helpers
 *
 * @return PWH_DCFH_Module_Helpers
 */
if (!function_exists('pwh_dcfh_module_helpers')):
    function pwh_dcfh_module_helpers()
    {
        return PWH_DCFH_Module_Helpers::instance();
    }
endif;
/**
 * Contants
 *
 * @return PWH_DCFH_HC
 */
if (!function_exists('pwh_dcfh_hc')):
    function pwh_dcfh_hc()
    {
        return PWH_DCFH_HC::instance();
    }
endif;
/**
 * Contants
 *
 * @return PWH_DCFH_Logger
 */
if (!function_exists('pwh_dcfh_logger')):
    function pwh_dcfh_logger()
    {
        return PWH_DCFH_Logger::instance();
    }
endif;
/**
 * Database Query Handler
 *
 * @return PWH_DCFH_DB_Handler
 */
if (!function_exists('pwh_dcfh_db_handler')):
    function pwh_dcfh_db_handler()
    {
        return PWH_DCFH_DB_Handler::instance();
    }
endif;
/**
 * Post Handler
 *
 * @return PWH_DCFH_Post_Meta_Handler
 */
if (!function_exists('pwh_dcfh_post_meta_handler')):
    function pwh_dcfh_post_meta_handler()
    {
        return PWH_DCFH_Post_Meta_Handler::instance();
    }
endif;
/**
 * Email Handler
 *
 * @return PWH_DCFH_User_Handler
 */
if (!function_exists('pwh_dcfh_user_handler')):
    function pwh_dcfh_user_handler()
    {
        return PWH_DCFH_User_Handler::instance();
    }
endif;
/**
 * Keywords Handler
 *
 * @return PWH_DCFH_Email_Merge_Data_Tags
 */
if (!function_exists('pwh_dcfh_email_tags_handler')):
    function pwh_dcfh_email_tags_handler()
    {
        return PWH_DCFH_Email_Merge_Data_Tags::instance();
    }
endif;
/**
 * Keywords Handler
 *
 * @return PWH_DCFH_Email_Template_Handler
 */
if (!function_exists('pwh_dcfh_email_tpl_handler')):
    function pwh_dcfh_email_tpl_handler()
    {
        return PWH_DCFH_Email_Template_Handler::instance();
    }
endif;
/**
 * Print Data
 *
 * @param $data
 * @param bool $exit
 */
if (!function_exists('pwh_dcfh_dd')):
    function pwh_dcfh_dd($data = '', $console = true, $exit = true)
    {
        pwh_dcfh_helpers()::dd($data, $console, $exit);
    }
endif;
/**
 * Log Data
 *
 * @param $data
 * @param bool $db
 * @param false $delete
 * @param string $file_name
 */
if (!function_exists('pwh_dcfh_log')):
    function pwh_dcfh_log($data, $delete = false, $db = false, $file_name = '')
    {
        pwh_dcfh_helpers()::log($data, $delete, $db, $file_name);
    }
endif;
/**
 * Register Confirmation Email Action
 *
 * @param $is_sent
 * @param $to
 * @param $cc
 * @param $bcc
 * @param $subject
 * @param $body
 * @param $from
 * @param $extra
 */
if (!function_exists('pwh_dcfh_confirmation_email')):
    function pwh_dcfh_confirmation_email($is_sent, $to, $cc, $bcc, $subject, $body, $from, $extra)
    {
        do_action('pwh_dcfh_confirmation_email', $is_sent, $to, $cc, $bcc, $subject, $body, $from, $extra);
    }
endif;