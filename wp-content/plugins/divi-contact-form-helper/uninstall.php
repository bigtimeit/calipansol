<?php
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
/**
 * Delete All Plugin Settings
 *
 * @return void
 */
if (!function_exists('pwh_dcfh_delete_plugin_data')):
    function pwh_dcfh_delete_plugin_data()
    {
        $options = get_option('et_divi');
        if (!empty($options) && isset($options['pwh_dcfh_delete_plugin_data']) && 'on' === $options['pwh_dcfh_delete_plugin_data']) {
            $filtered_options = array_filter($options, function ($key) {
                return strpos($key, 'pwh_dcfh_') !== 0;
            }, ARRAY_FILTER_USE_KEY);
            if (!empty($filtered_options)) {
                update_option('et_divi', $filtered_options);
            }
        }
    }

    pwh_dcfh_delete_plugin_data();
endif;
/**
 * Delete All Registered Crons
 *
 * @return void
 */
if (!function_exists('pwh_dcfh_delete_plugin_cron_jobs')):
    function pwh_dcfh_delete_plugin_cron_jobs()
    {
        $cron_jobs = [
            'pwh_dcfh_entries_auto_backup_daily',
            'pwh_dcfh_entries_auto_backup_hourly',
            'pwh_dcfh_entries_auto_backup_twicedaily',
            'pwh_dcfh_entries_auto_backup_weekly',
            'pwh_dcfh_entries_auto_backup_monthly',
            'pwh_dcfh_delete_tmp_files',
        ];
        foreach ($cron_jobs as $cron_job) {
            if (wp_next_scheduled($cron_job)) {
                wp_clear_scheduled_hook($cron_job);
            }
        }
    }

    pwh_dcfh_delete_plugin_cron_jobs();
endif;