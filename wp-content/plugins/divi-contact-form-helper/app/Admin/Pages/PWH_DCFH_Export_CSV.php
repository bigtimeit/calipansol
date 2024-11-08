<?php

namespace PWH_DCFH\App\Admin\Pages;

use PWH_DCFH\App\Helpers\PWH_DCFH_Helpers;
use WP_Query;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Export_CSV')) {
    class PWH_DCFH_Export_CSV
    {

        private $_post_type;

        private $contact_form_data;

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            $this->_post_type = pwh_dcfh_hc()::POST_TYPE;
            $_page = isset($_GET['page']) ? sanitize_key($_GET['page']) : null; // phpcs:ignore
            add_action('admin_menu', [$this, 'menu']);
            if ('export_as_csv' === $_page) {
                $this->contact_form_data = pwh_dcfh_db_handler()::get_contact_form_csv_data();
                add_action('admin_init', function () {
                    ob_start();
                });
                add_action('view_export_as_csv', [$this, 'display_form'], 10, 1);
                add_filter(pwh_dcfh_hc()::FILTER_PREFIX.'admin_localizations', [$this, 'load_csv_data']);
            }
        }

        /**
         * Register Wp Admin Menu
         */
        public function menu()
        {
            add_submenu_page(pwh_dcfh_hc()::ADMIN_MENU_PAGE_SLUG, esc_html__('Export CSV', pwh_dcfh_hc()::TEXT_DOMAIN), esc_html__('Export CSV', pwh_dcfh_hc()::TEXT_DOMAIN), 'manage_options',
                'export_as_csv', [$this, 'process_form']);
        }

        /**
         * Filter Admin Localizations
         *
         * @return array
         */
        public function load_csv_data()
        {
            $localizations['csvData'] = wp_json_encode($this->contact_form_data);

            return $localizations;
        }

        /**
         * Process Form Actions
         */
        public function process_form()
        {
            $contact_form_id = isset($_GET['contact_form_id']) ? sanitize_text_field($_GET['contact_form_id']) : null; // phpcs:ignore
            $post_request = [
                'contact_form_id' => $contact_form_id,
                'contact_form_fields' => '',
                'file_name' => '',
            ];
            if (isset($_POST['btn_export_as_csv']) && isset($_POST['_wpnonce'])) {
                $_wpnonce = sanitize_text_field($_POST['_wpnonce']);
                if (!wp_verify_nonce($_wpnonce, 'action_export_as_csv')) {
                    wp_die(esc_html__('Unable to submit this form, please refresh and try again.', pwh_dcfh_hc()::TEXT_DOMAIN));
                }
                $validate_request = true;
                $wp_count_posts = wp_count_posts($this->_post_type);
                $total_posts = intval($wp_count_posts->draft + $wp_count_posts->private);
                if ($total_posts == 0) {
                    pwh_dcfh_helpers()::add_error_message(__('No record found.', pwh_dcfh_hc()::TEXT_DOMAIN));
                    $validate_request = false;
                }
                $file_name = isset($_POST['file_name']) ? sanitize_file_name($_POST['file_name']) : null;
                $contact_form_id = isset($_POST['contact_form_id']) ? sanitize_text_field($_POST['contact_form_id']) : null;
                $contact_form_fields = isset($_POST['contact_form_fields']) ? $_POST['contact_form_fields'] : null;                   // phpcs:ignore
                $entries_type = isset($_POST['entries_type']) ? $_POST['entries_type'] : null;                                       // phpcs:ignore
                if (empty($file_name)) {
                    pwh_dcfh_helpers()::add_error_message(__('Please enter csv file name.', pwh_dcfh_hc()::TEXT_DOMAIN));
                    $validate_request = false;
                }
                if (empty($contact_form_id)) {
                    pwh_dcfh_helpers()::add_error_message(__('Please select form type.', pwh_dcfh_hc()::TEXT_DOMAIN));
                    $validate_request = false;
                }
                if (empty($contact_form_fields)) {
                    pwh_dcfh_helpers()::add_error_message(__('Please select at least one keyword.', pwh_dcfh_hc()::TEXT_DOMAIN));
                    $validate_request = false;
                }
                if (empty($entries_type)) {
                    pwh_dcfh_helpers()::add_error_message(__('Please select at least entries type to export.', pwh_dcfh_hc()::TEXT_DOMAIN));
                    $validate_request = false;
                }
                $post_request = $_POST;
                if ($validate_request) {
                    $fields_label = pwh_dcfh_db_handler()::get_contact_form_fields_label($contact_form_id);
                    $csv_headers = [];
                    foreach ($contact_form_fields as $field_id) {
                        if (isset($fields_label[$field_id])) {
                            $csv_headers[] = $fields_label[$field_id];
                        } else {
                            $csv_headers[] = $field_id;
                        }
                    }
                    $csv_headers = array_map([PWH_DCFH_Helpers::class, 'clean_string'], $csv_headers);
                    $found_posts_obj = new WP_Query([
                        'post_type' => pwh_dcfh_hc()::POST_TYPE,
                        'post_status' => $entries_type,
                        'meta_key' => pwh_dcfh_hc()::CF_FORM_ID_META_KEY,                // phpcs:ignore
                        'meta_value' => $contact_form_id                            // phpcs:ignore
                    ]);
                    $total_posts = $found_posts_obj->found_posts;
                    $posts_per_page = 200;
                    header('Content-Encoding: UTF-8');
                    header('Content-type: text/csv; charset=UTF-8');
                    header('Content-Disposition: attachment; filename="'.$file_name.'.csv"');
                    header('Pragma: no-cache');
                    header('Expires: 0');
                    $f_handle = fopen("php://output", "w");
                    if (ob_get_length() > 0) {
                        ob_clean();
                    }
                    fputcsv($f_handle, $csv_headers);
                    for ($i = 0; $i < $total_posts; $i += $posts_per_page) {
                        $post_obj = new WP_Query([
                            'post_type' => pwh_dcfh_hc()::POST_TYPE,
                            'post_status' => $entries_type,
                            'posts_per_page' => $posts_per_page,
                            'offset' => $i,
                            'meta_key' => pwh_dcfh_hc()::CF_FORM_ID_META_KEY,          // phpcs:ignore
                            'meta_value' => $contact_form_id                          // phpcs:ignore
                        ]);
                        $csv_data = [];
                        $counter = 0;
                        for ($k = 0; $k < $post_obj->found_posts; $k++) {
                            $posts = $post_obj->posts;
                            if (isset($posts[$k]->ID)) {
                                $post_id = $posts[$k]->ID;
                                $post_excerpt = $posts[$k]->post_excerpt;
                                if (is_serialized($post_excerpt)) {
                                    $page_id = pwh_dcfh_post_meta_handler()::get_page_id_meta_value($post_id);
                                    $form_entries = pwh_dcfh_helpers()::maybe_unserialize($post_excerpt);
                                    if (!empty($form_entries) && is_array($form_entries)) {
                                        foreach ($form_entries as $form_entry) {
                                            $csv_data[$counter][$form_entry['id']] = $form_entry;
                                        }
                                        foreach ($contact_form_fields as $field) {
                                            if ('entry_number' === $field) {
                                                $csv_data[$counter]['entry_number'] = [
                                                    'type' => 'meta',
                                                    'value' => $post_id,
                                                ];
                                            }
                                            if ('read_by' === $field) {
                                                if ('0000-00-00 00:00:00' !== $posts[$k]->post_modified) {
                                                    $csv_data[$counter]['read_by'] = [
                                                        'type' => 'meta',
                                                        'value' => pwh_dcfh_helpers()::get_author_name($posts[$k]->post_author),
                                                    ];
                                                } else {
                                                    $csv_data[$counter]['read_by'] = [
                                                        'type' => 'meta',
                                                        'value' => '-',
                                                    ];
                                                }
                                            }
                                            if ('submitter' === $field) {
                                                $csv_data[$counter]['submitter'] = [
                                                    'type' => 'meta',
                                                    'value' => pwh_dcfh_helpers()::get_submitter_name($posts[$k]->post_author),
                                                ];
                                            }
                                            if ('page_title' === $field) {
                                                $csv_data[$counter]['page_title'] = [
                                                    'type' => 'meta',
                                                    'value' => get_the_title($page_id),
                                                ];
                                            }
                                            if ('page_url' === $field) {
                                                $csv_data[$counter]['page_url'] = [
                                                    'type' => 'meta',
                                                    'value' => get_the_permalink($page_id),
                                                ];
                                            }
                                            if ('date' === $field) {
                                                $csv_data[$counter]['date'] = [
                                                    'type' => 'meta',
                                                    'value' => $posts[$k]->post_date,
                                                ];
                                            }
                                            if ('ip_address' === $field) {
                                                $ip_address = pwh_dcfh_post_meta_handler()::get_ip_address_meta_value($post_id);
                                                $csv_data[$counter]['ip_address'] = [
                                                    'type' => 'meta',
                                                    'value' => '' === $ip_address ? '-' : $ip_address,
                                                ];
                                            }
                                            if ('browser' === $field || 'platform' === $field) {
                                                $user_agent_meta = pwh_dcfh_post_meta_handler()::get_user_agent_meta_value($post_id);
                                                if ('browser' === $field) {
                                                    $csv_data[$counter]['browser'] = [
                                                        'type' => 'meta',
                                                        'value' => isset($user_agent_meta['browser']) ? ucfirst($user_agent_meta['browser']) : '-',
                                                    ];
                                                }
                                                if ('platform' === $field) {
                                                    $csv_data[$counter]['platform'] = [
                                                        'type' => 'meta',
                                                        'value' => isset($user_agent_meta['platform']) ? ucfirst($user_agent_meta['platform']) : '-',
                                                    ];
                                                }
                                            }
                                        }
                                        $counter++;
                                    }
                                }
                            }
                        }
                        if (!empty($csv_data)) {
                            foreach ($csv_data as $k => $v) {
                                $csv_rows = [];
                                foreach ($contact_form_fields as $field) {
                                    if (isset($csv_data[$k][$field])) {
                                        $form_data = $csv_data[$k][$field];
                                        if (isset($form_data['type'])) {
                                            if ('file' === $form_data['type']) {
                                                $subdir = isset($form_data['subdir']) ? esc_html($form_data['subdir']) : '';
                                                $files = explode(',', $form_data['value']);
                                                $files = array_filter($files);
                                                if (!empty($files) && !empty($subdir)) {
                                                    $media_links = '';
                                                    foreach ($files as $file) {
                                                        $upload_dir = pwh_dcfh_helpers()::get_form_upload_dir($contact_form_id, $subdir, $file);
                                                        if (is_file($upload_dir) && file_exists($upload_dir)) {
                                                            $upload_url = pwh_dcfh_helpers()::get_form_upload_url($contact_form_id, $subdir, $file);
                                                            $media_links .= $upload_url.',';
                                                        }
                                                    }
                                                    $media_links = rtrim($media_links, ',');
                                                    if ($media_links) {
                                                        $csv_rows[] = rtrim($media_links, ',');
                                                    }
                                                } else {
                                                    $csv_rows[] = '-';
                                                }
                                            } else {
                                                $csv_rows[] = '' !== $form_data['value'] ? $form_data['value'] : '-';
                                            }
                                        }
                                    } else {
                                        $csv_rows[] = '-';
                                    }
                                }
                                fputcsv($f_handle, $csv_rows);
                            }
                        }
                    }
                    $csv_footer = [
                        ["path" => "\n\n"],
                        ["path" => __("Total Rows: ".$total_posts, pwh_dcfh_hc()::TEXT_DOMAIN)],
                        ["path" => __("Created on: ".date_i18n("F j, Y, g:i a"), pwh_dcfh_hc()::TEXT_DOMAIN)],
                        ["path" => __("Generated By: ".ucwords(pwh_dcfh_helpers()::get_author_name()), pwh_dcfh_hc()::TEXT_DOMAIN)],
                    ];
                    foreach ($csv_footer as $footer) {
                        fputcsv($f_handle, $footer);
                    }
                    if (ob_get_length() > 0) {
                        ob_flush();
                    }
                    fclose($f_handle); // phpcs:ignore
                    exit;
                }
            }
            do_action('view_export_as_csv', $post_request);
        }

        /**
         * Display Form HTML Content
         *
         * @param $post_request
         */
        public function display_form($post_request)
        {
            $contact_forms_data = $this->contact_form_data;
            ?>
            <div class="wrap pwh-dcfh-page" id="page-export-as-csv">
                <h1 class="wp-heading-inline"><?php esc_html_e(get_admin_page_title(), pwh_dcfh_hc()::TEXT_DOMAIN); ?></h1>
                <hr class="wp-header-end">
                <?php pwh_dcfh_helpers()::display_message(); ?>
                <span class="spinner"></span>
                <div class="js-response"></div>
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div class="postbox-container">
                            <div class="postbox">
                                <div class="inside">
                                    <form method="POST" autocomplete="off" enctype="application/x-www-form-urlencoded">
                                        <table class="form-table">
                                            <tbody>
                                            <tr>
                                                <td colspan="2" class="text-right">
                                                    <b><?php esc_html_e('Total Entries:', pwh_dcfh_hc()::TEXT_DOMAIN); ?></b><span></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php esc_html_e('CSV File Name:', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
                                                    <span class="required">*</span></th>
                                                <td>
                                                    <input title="" type="text" name="file_name" id="file_name" class="form-control" value="<?php echo esc_html($post_request['file_name']);
                                                    ?>" placeholder="<?php esc_html_e('CSV File Name', pwh_dcfh_hc()::TEXT_DOMAIN); ?>" required>
                                                    <p class="helper"><?php esc_html_e('Write csv file name.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php esc_html_e('Contact Form:', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
                                                    <span class="required">*</span></th>
                                                <td>
                                                    <select title="" name="contact_form_id" id="contact_form_id" class="form-control" required>
                                                        <option value=""><?php esc_html_e('Please Select Contact Form', pwh_dcfh_hc()::TEXT_DOMAIN); ?></option>
                                                        <?php if (!empty($contact_forms_data)) {
                                                            foreach ($contact_forms_data as $key => $value) { ?>
                                                                <option value="<?php echo esc_html($key); ?>" <?php selected($key,
                                                                    $post_request['contact_form_id']); ?>><?php echo esc_html($value['contact_form_title']); ?></option>
                                                            <?php }
                                                        } ?>
                                                    </select>
                                                    <p class="helper"><?php esc_html_e('Choose contact form name.', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php esc_html_e('Contact Form Fields:', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
                                                    <span class="required">*</span></th>
                                                <td>
                                                    <div class="merge-data-tsgs-list"></div>
                                                    <p class="helper"><?php esc_html_e('Choose contact form fields to export.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php esc_html_e('Include:', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
                                                    <span class="required">*</span></th>
                                                <td>
                                                    <!--private-->
                                                    <label class="bulk-select-button" for="read">
                                                        <input type="checkbox" id="read" name="entries_type[]" class="bulk-select-switcher" value="private" checked>
                                                        <span class="bulk-select-button-label"><?php esc_html_e('Read'); ?></span>
                                                    </label>
                                                    <!--draft-->
                                                    <label class="bulk-select-button" for="unread">
                                                        <input type="checkbox" id="unread" name="entries_type[]" class="bulk-select-switcher" value="draft" checked>
                                                        <span class="bulk-select-button-label"><?php esc_html_e('Unread'); ?></span>
                                                    </label>
                                                    <!--trash-->
                                                    <label class="bulk-select-button" for="trash">
                                                        <input type="checkbox" id="trash" name="entries_type[]" class="bulk-select-switcher" value="trash">
                                                        <span class="bulk-select-button-label"><?php esc_html_e('Trash'); ?></span>
                                                    </label>
                                                    <p class="helper"><?php esc_html_e('Choose entries to export.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <?php
                                        wp_nonce_field('action_export_as_csv', '_wpnonce');
                                        submit_button(esc_html__('Export CSV', pwh_dcfh_hc()::TEXT_DOMAIN), 'primary large', 'btn_export_as_csv');
                                        ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div id="postbox-container-1" class="postbox-container">
                            <?php
                            require_once dirname(__DIR__)."/template/documentation-widget.php";
                            require_once dirname(__DIR__)."/template/support-widget.php";
                            ?>
                        </div>
                    </div>
                    <br class="clear">
                </div>
            </div>
            <?php
        }

    }
}