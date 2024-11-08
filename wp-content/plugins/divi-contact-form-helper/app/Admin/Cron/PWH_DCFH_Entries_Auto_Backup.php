<?php

namespace PWH_DCFH\App\Admin\Cron;

use Exception;
use PWH_DCFH\App\Helpers\PWH_DCFH_Email_Helper;
use PWH_DCFH\App\Helpers\PWH_DCFH_Helpers;
use WP_Query;
use ZipArchive;

defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Entries_Auto_Backup')) {
    class PWH_DCFH_Entries_Auto_Backup
    {

        private $email_to;

        private $hook;

        private $recurrence;

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {

            if (pwh_dcfh_helpers()::is_option_enabled('pwh_dcfh_backup_enabled')) {
                $this->email_to = pwh_dcfh_helpers()::get_option('pwh_dcfh_backup_email');
                $backup_schedule = pwh_dcfh_helpers()::get_option('pwh_dcfh_backup_schedule');
                $this->hook = 'pwh_dcfh_entries_auto_backup_'.$backup_schedule;
                $this->recurrence = $backup_schedule;
                add_action('init', [$this, 'add_cron']);
                add_action($this->hook, [$this, 'backup']);
            }
        }

        /**
         * Regsiter Cron Event In WP
         */
        public function add_cron()
        {
            if (!wp_next_scheduled($this->hook)) {
                wp_schedule_event(time(), $this->recurrence, $this->hook);
            }
        }

        /**
         *  Cron Event Process To Perform
         */
        public function backup()
        {
            $zip_file_dir = self::create_backup();
            if (false !== $zip_file_dir) {
                if (!empty($zip_file_dir['path']) && !empty($zip_file_dir['url'])) {
                    $zip_file = $zip_file_dir['path'];
                    $email = new PWH_DCFH_Email_Helper($this->email_to);
                    $email_from = 'noreply@loclhost.com';
                    $remote_address = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
                    if (!in_array($remote_address, ['127.0.0.1', '::1'])) {
                        $email_from = 'noreply@'.wp_parse_url(get_bloginfo('url'), 1);
                    }
                    $email_subject = wp_strip_all_tags(apply_filters(pwh_dcfh_hc()::FILTER_PREFIX.'auto_backup_email_subject', __('Divi Contact Form Backup', pwh_dcfh_hc()::TEXT_DOMAIN)));
                    $heading = wp_strip_all_tags(apply_filters(pwh_dcfh_hc()::FILTER_PREFIX.'auto_backup_email_heading', __('Divi Contact Form Backup', pwh_dcfh_hc()::TEXT_DOMAIN)));
                    $email->set_email_from($email_from);
                    $email->set_email_subject($email_subject);
                    $email->set_email_template(pwh_dcfh_helpers()::plugin_dir('html-templates/tpl-auto-backup.html'));
                    $email->set_email_body([
                        'heading' => $heading,
                        'site_title' => htmlspecialchars_decode(get_bloginfo('name')),
                        'site_url' => get_option('siteurl'),
                        'total_files' => $zip_file_dir['total_files'],
                        'backup_date' => pwh_dcfh_helpers()::date_time(date_i18n('Y-m-d H:i:s')),
                    ]);
                    $email->set_email_attachments([$zip_file]);
                    $email->set_wp_mail_filters();
                    try {
                        if ($email->send()) {
                            $email->delete_attachment();
                        }
                    } catch (Exception $e) {
                        self::send_error_email(__('Unable To Send Backup Email', pwh_dcfh_hc()::TEXT_DOMAIN), esc_html($e->getMessage()));
                    }
                }
            }
        }

        /**
         * Create Backup Process
         *
         * @return array|false
         */
        private function create_backup()
        {
            if (!class_exists('ZipArchive')) {
                self::send_error_email(__('Backup Error Zip Archive', pwh_dcfh_hc()::TEXT_DOMAIN),
                    __('PHP needs to have the zip extension installed. If you are using cpanel you may have zip extension installed but not activate. You need to active it. Please contact your hosting provider to activate it.',
                        pwh_dcfh_hc()::TEXT_DOMAIN));

                return false;
            }
            $contact_forms = pwh_dcfh_db_handler()::get_contact_forms();
            if (!empty($contact_forms)) {
                $wp_upload_dir = wp_upload_dir();
                $upload_path = $wp_upload_dir['basedir'];
                $upload_url = $wp_upload_dir['baseurl'];
                $zip_file_name = 'divi-contact-form-entries-'.time().'.zip';
                $zip_file_dir = wp_normalize_path($upload_path.DIRECTORY_SEPARATOR.$zip_file_name);
                $zip_file_url = wp_normalize_path($upload_url.DIRECTORY_SEPARATOR.$zip_file_name);
                $zip = new ZipArchive();
                $total_files_zipped = 0;
                foreach ($contact_forms as $contact_form) {
                    $contact_form_id = $contact_form['ID'];
                    $contact_form_title = $contact_form['title'];
                    $csv_file_name = $contact_form_title.'.csv';
                    $csv_file_dir = wp_normalize_path($upload_path.DIRECTORY_SEPARATOR.$csv_file_name);
                    $f_handle = fopen($csv_file_dir, 'w'); // phpcs:ignore
                    if (false === $f_handle) {
                        self::send_error_email(__('Unable To Create CSV File', pwh_dcfh_hc()::TEXT_DOMAIN),
                            __('Backup need to create CSV file.Server is not allowd to create file.', pwh_dcfh_hc()::TEXT_DOMAIN));
                    }
                    $total_posts_obj = new WP_Query([
                        'post_type' => pwh_dcfh_hc()::POST_TYPE,
                        'post_status' => 'any',
                        'meta_key' => pwh_dcfh_hc()::CF_FORM_ID_META_KEY,       // phpcs:ignore
                        'meta_value' => $contact_form_id                   // phpcs:ignore
                    ]);
                    $total_posts = $total_posts_obj->found_posts;
                    $posts_per_page = 200;
                    for ($i = 0; $i < $total_posts; $i += $posts_per_page) {
                        $post_obj = new WP_Query([
                            'post_type' => pwh_dcfh_hc()::POST_TYPE,
                            'post_status' => 'any',
                            'posts_per_page' => $posts_per_page,
                            'offset' => $i,
                            'meta_key' => pwh_dcfh_hc()::CF_FORM_ID_META_KEY,       // phpcs:ignore
                            'meta_value' => $contact_form_id                   // phpcs:ignore
                        ]);
                        $form_headers = [];
                        $form_rows = [];
                        for ($k = 0; $k < $post_obj->found_posts; $k++) {
                            $posts = $post_obj->posts;
                            if (isset($posts[$k]->ID)) {
                                $post_id = $posts[$k]->ID;
                                $post_excerpt = $posts[$k]->post_excerpt;
                                if (is_serialized($post_excerpt)) {
                                    $page_id = pwh_dcfh_post_meta_handler()::get_page_id_meta_value($post_id);
                                    $form_entries = pwh_dcfh_helpers()::maybe_unserialize($post_excerpt);
                                    if (!empty($form_entries) && is_array($form_entries)) {
                                        $fields_label = pwh_dcfh_db_handler()::get_contact_form_fields_label($contact_form_id);
                                        foreach ($form_entries as $form_entry) {
                                            $field_id = $form_entry['id'];
                                            $field_type = $form_entry['type'];
                                            $form_headers[$contact_form_id][$field_id] = $field_id;
                                            if ('file' === $field_type) {
                                                $subdir = isset($form_entry['subdir']) ? esc_html($form_entry['subdir']) : '';
                                                $files = explode(',', $form_entry['value']);
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
                                                    $form_rows[$contact_form_id][$k][$field_id] = $media_links;
                                                }
                                            } else {
                                                $form_rows[$contact_form_id][$k][$field_id] = '' !== $form_entry['value'] ? $form_entry['value'] : '-';
                                            }
                                        }
                                        $form_rows[$contact_form_id][$k]['entry_number'] = $post_id;
                                        $form_rows[$contact_form_id][$k]['read_by'] = '0000-00-00 00:00:00' !== $posts[$k]->post_modified ? pwh_dcfh_helpers()::get_author_name($posts[$k]->post_author) : '-';
                                        $form_rows[$contact_form_id][$k]['submitter'] = pwh_dcfh_helpers()::get_submitter_name($posts[$k]->post_author);
                                        $form_rows[$contact_form_id][$k]['page_title'] = get_the_title($page_id);
                                        $form_rows[$contact_form_id][$k]['page_url'] = get_the_permalink($page_id);
                                        $form_rows[$contact_form_id][$k]['date'] = $posts[$k]->post_date;
                                        $ip_address = pwh_dcfh_post_meta_handler()::get_ip_address_meta_value($post_id);
                                        $form_rows[$contact_form_id][$k]['ip_address'] = !empty($ip_address) ? $ip_address : '-';
                                        $user_agent_meta = pwh_dcfh_post_meta_handler()::get_user_agent_meta_value($post_id);
                                        if (!empty($user_agent_meta)) {
                                            $form_rows[$contact_form_id][$k]['browser'] = isset($user_agent_meta['browser']) ? ucfirst($user_agent_meta['browser']) : null;
                                            $form_rows[$contact_form_id][$k]['platform'] = isset($user_agent_meta['platform']) ? ucfirst($user_agent_meta['platform']) : null;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (!empty($form_rows)) {
                        $form_headers[$contact_form_id] = wp_parse_args(['entry_number', 'read_by', 'submitter', 'page_title', 'page_url', 'date', 'ip_address', 'browser', 'platform'], $form_headers[$contact_form_id]);
                        $csv_headers = array_map([PWH_DCFH_Helpers::class, 'clean_string'], $form_headers[$contact_form_id]);
                        fputcsv($f_handle, $csv_headers);
                        foreach ($form_rows[$contact_form_id] as $value) {
                            $csv_rows = [];
                            foreach ($form_headers[$contact_form_id] as $header) {
                                if (isset($value[$header])) {
                                    $csv_rows[] = $value[$header];
                                } else {
                                    $csv_rows[] = '-';
                                }
                            }
                            fputcsv($f_handle, $csv_rows);
                        }
                        $csv_footer = [
                            ["path" => "\n\n"],
                            ["path" => __("Total Rows: ".$total_posts, pwh_dcfh_hc()::TEXT_DOMAIN)],
                            ["path" => __("Created on: ".date_i18n("F j, Y, g:i a"), pwh_dcfh_hc()::TEXT_DOMAIN)],
                            ["path" => __("Auto Generated", pwh_dcfh_hc()::TEXT_DOMAIN)],
                        ];
                        foreach ($csv_footer as $footer) {
                            fputcsv($f_handle, $footer);
                        }
                        $f_close = fclose($f_handle); // phpcs:ignore
                        // Zipped CSV
                        if ($f_close) {
                            if ($zip->open($zip_file_dir, ZipArchive::CREATE)) {
                                $zip->addFile($csv_file_dir, $csv_file_name);
                                @$zip->close(); // phpcs:ignore
                                if (file_exists($csv_file_dir)) {
                                    wp_delete_file($csv_file_dir);
                                    $total_files_zipped++;
                                }
                            }
                        }
                    }
                }
                if ($total_files_zipped == count($contact_forms)) {
                    return ['path' => $zip_file_dir, 'url' => $zip_file_url, 'total_files' => $total_files_zipped];
                }
            }

            return false;
        }

        /**
         * Send Error Email
         *
         * @param $subject
         * @param $message
         *
         * @return void
         */
        private static function send_error_email($subject, $message)
        {
            wp_mail(get_option('admin_email'), $subject, $message);
        }

    }
}