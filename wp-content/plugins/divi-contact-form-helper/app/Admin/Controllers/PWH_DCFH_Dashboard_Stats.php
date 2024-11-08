<?php

namespace PWH_DCFH\App\Admin\Controllers;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Dashboard_Stats')) {
    class PWH_DCFH_Dashboard_Stats
    {

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            global $pagenow;
            if ('index.php' === $pagenow) {
                if (pwh_dcfh_helpers()::is_option_enabled('pwh_dcfh_stats_enabled')) {
                    add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
                }
            }
        }

        /**
         * Register Widget
         */
        public function add_dashboard_widget()
        {
            wp_add_dashboard_widget('pwh-dcfh-contact-form-stats', __('Divi Contact Form Statistics', pwh_dcfh_hc()::TEXT_DOMAIN), [$this, 'display']);
        }

        /**
         * Widget HTML Content
         */
        public function display()
        { ?>
            <table>
                <thead>
                <tr>
                    <th><?php esc_html_e('Yesterday', pwh_dcfh_hc()::TEXT_DOMAIN); ?></th>
                    <th><?php esc_html_e('Today', pwh_dcfh_hc()::TEXT_DOMAIN); ?></th>
                    <th><?php esc_html_e('Last Week', pwh_dcfh_hc()::TEXT_DOMAIN); ?></th>
                    <th><?php esc_html_e('Last Month', pwh_dcfh_hc()::TEXT_DOMAIN); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $stats = pwh_dcfh_db_handler()::get_contact_forms_dashboard_stats();
                if (!empty($stats)) {
                    foreach ($stats as $key => $stat) {
                        $yesterday = isset($stat['yesterday']) ? esc_html($stat['yesterday']) : 0;
                        $today = isset($stat['today']) ? esc_html($stat['today']) : 0;
                        $last_week = isset($stat['last_week']) ? esc_html($stat['last_week']) : 0;
                        $last_month = isset($stat['last_month']) ? esc_html($stat['last_month']) : 0;
                        $total = esc_html($yesterday + $today + $last_week + $last_month);
                        ?>
                        <tr>
                            <td colspan="4" class="text-left font-weight-600"><?php echo esc_html($key); ?></td>
                        </tr>
                        <tr>
                            <td><?php echo esc_html(number_format_i18n($yesterday)); ?></td>
                            <td><?php echo esc_html(number_format_i18n($today)); ?></td>
                            <td><?php echo esc_html(number_format_i18n($last_week)); ?></td>
                            <td><?php echo esc_html(number_format_i18n($last_month)); ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-right font-weight-600 font-italic">
                                <?php echo sprintf('Total Entries: %s', esc_html(number_format_i18n($total))); ?></td>
                        </tr>
                        <?php
                    }
                } else { ?>
                    <tr>
                        <td colspan="4" class="text-center"><?php esc_html_e('No entries found.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php
        }

    }
}