<?php

namespace PWH_DCFH\App\Admin\Controllers;

use WP_List_Table;

if (!class_exists('PWH_DCFH_Email_Templates_List')) {
    class PWH_DCFH_Email_Templates_List extends WP_List_Table
    {

        /**
         * Initial Class Constructor
         *
         * @param array $args
         */
        public function __construct($args = [])
        {
            parent::__construct(['singular' => __('Template List', pwh_dcfh_hc()::TEXT_DOMAIN), 'plural' => __('Templates List', pwh_dcfh_hc()::TEXT_DOMAIN), 'ajax' => false]);
            $_page = pwh_dcfh_helpers()::current_page();
            if ('email_templates_list' === $_page) {
                $this->delete_template();
            }
        }

        /**
         * Prepare Table Items
         */
        public function prepare_items()
        {
            ## Columns
            $this->_column_headers = $this->get_column_info();
            ## Get Record
            $record = $this->get_record();
            $this->items = isset($record['items']) ? $record['items'] : null;
        }

        /**
         * Get Records From Database
         *
         * @return array
         */
        public function get_record()
        {
            $data = [];
            $templates = pwh_dcfh_email_tpl_handler()::get_templates();
            if (!empty($templates)) {
                $total_items = 0;
                foreach ($templates as $template_id => $template) {
                    $total_items = $total_items + 1;
                    $data['items'][] = [
                        'tpl_id' => $template_id,
                        'contact_form_id' => isset($template['contact_form_id']) ? $template['contact_form_id'] : '',
                        'tpl_type' => isset($template['tpl_type']) ? $template['tpl_type'] : '',
                        'tpl_name' => isset($template['tpl_name']) ? $template['tpl_name'] : '',
                        'email_from' => isset($template['email_from']) ? $template['email_from'] : '',
                        'email_subject' => isset($template['email_subject']) ? $template['email_subject'] : '',
                        'email_body' => isset($template['email_body']) ? $template['email_body'] : '',
                        'created_by' => isset($template['created_by']) ? $template['created_by'] : '',
                        'modified_by' => isset($template['modified_by']) ? $template['modified_by'] : '',
                        'created_at' => isset($template['created_at']) ? $template['created_at'] : '',
                        'modified_at' => isset($template['modified_at']) ? $template['modified_at'] : '',
                    ];
                }
                $data['total_items'] = $total_items;
            }

            return $data;
        }

        /** Get Columns
         *
         * @return array
         */
        public function get_columns()
        {
            return [
                'tpl_name' => __('Name', pwh_dcfh_hc()::TEXT_DOMAIN),
                'email_from' => __('From', pwh_dcfh_hc()::TEXT_DOMAIN),
                'email_subject' => __('Subject', pwh_dcfh_hc()::TEXT_DOMAIN),
                'contact_form_id' => __('Contact Form', pwh_dcfh_hc()::TEXT_DOMAIN),
                'tpl_type' => __('Type', pwh_dcfh_hc()::TEXT_DOMAIN),
                'created_by' => __('Created By', pwh_dcfh_hc()::TEXT_DOMAIN),
                'modified_by' => __('Modified By', pwh_dcfh_hc()::TEXT_DOMAIN),
                'created_at' => __('Created at', pwh_dcfh_hc()::TEXT_DOMAIN),
                'modified_at' => __('Modified at', pwh_dcfh_hc()::TEXT_DOMAIN),
            ];
        }

        /**
         * Set Default Columns
         *
         * @param array|object $item
         * @param string $column_name
         *
         * @return mixed|string
         */
        public function column_default($item, $column_name)
        {
            switch ($column_name) {
                case 'tpl_name':
                case 'email_from':
                case 'email_subject':
                    return !empty($item[$column_name]) ? $item[$column_name] : '-';
                case 'contact_form_id':
                    return !empty($item[$column_name]) ? pwh_dcfh_db_handler()::get_contact_form_title($item[$column_name]) : '-';
                case 'tpl_type':
                    return pwh_dcfh_email_tpl_handler()::get_template_type_label($item[$column_name]);
                case 'created_by':
                    return pwh_dcfh_helpers()::get_author_name($item[$column_name]);
                case 'modified_by':
                    return !empty($item[$column_name]) ? pwh_dcfh_helpers()::get_author_name($item[$column_name]) : '-';
                case 'created_at':
                case 'modified_at':
                    return pwh_dcfh_helpers()::date_time($item[$column_name]);
                default:
                    return null;
            }
        }

        /**
         * Set Template Column Name
         *
         * @param $item
         *
         * @return string
         */
        public function column_tpl_name($item)
        {
            $tpl_id = $item['tpl_id'];
            $tpl_name = $item['tpl_name'];
            // EDIT
            $edit_url = add_query_arg([
                'post_type' => pwh_dcfh_hc()::POST_TYPE,
                'page' => 'create_email_template',
                'action' => 'edit',
                'tpl_id' => $tpl_id,
                '_wpnonce' => wp_create_nonce('edit_email_template'),
            ], wp_specialchars_decode(admin_url('edit.php')));
            // DELETE
            $delete_url = add_query_arg(['action' => 'delete', 'tpl_id' => $tpl_id, '_wpnonce' => wp_create_nonce('delete_email_template')],
                wp_specialchars_decode(menu_page_url('email_templates_list', false)));
            $tpl_delete_message = sprintf(__('Are you sure you want to delete %s template?', pwh_dcfh_hc()::TEXT_DOMAIN), esc_html($tpl_name));
            $actions['tpl_edit'] = "<a href='$edit_url'>".__('Edit', pwh_dcfh_hc()::TEXT_DOMAIN)."</a>";
            $actions['tpl_delete'] = "<a href='$delete_url' onclick=\"return confirm('$tpl_delete_message')\">".__('Delete', pwh_dcfh_hc()::TEXT_DOMAIN)."</a>";

            return sprintf('%1$s %2$s', $tpl_name, $this->row_actions($actions));
        }

        /**
         * No Items
         */
        public function no_items()
        {
            esc_html_e('No templates found.', pwh_dcfh_hc()::TEXT_DOMAIN);
        }

        /**
         * Action To Delete Template
         */
        private static function delete_template()
        {
            if (isset($_GET['_wpnonce']) && (isset($_GET['action']) && $_GET['action'] == 'delete') && isset($_GET['tpl_id'])) {
                $_wpnonce = sanitize_text_field($_GET['_wpnonce']);
                $data['action'] = sanitize_text_field($_GET['action']);
                if (!wp_verify_nonce($_wpnonce, 'delete_email_template')) {
                    wp_safe_redirect(add_query_arg(['post_type' => pwh_dcfh_hc()::POST_TYPE, 'page' => 'email_templates_list'], admin_url('edit.php')));
                    exit();
                }
                $tpl_id = sanitize_text_field($_GET['tpl_id']);
                if (empty($tpl_id)) {
                    wp_safe_redirect(add_query_arg(['post_type' => pwh_dcfh_hc()::POST_TYPE, 'page' => 'email_templates_list'], admin_url('edit.php')));
                    exit();
                }
                $response = delete_option($tpl_id);
                if ($response) {
                    wp_safe_redirect(add_query_arg(['post_type' => pwh_dcfh_hc()::POST_TYPE, 'page' => 'email_templates_list', 'success' => true, 'action' => 'delete'], admin_url('edit.php')));
                } else {
                    wp_safe_redirect(add_query_arg(['post_type' => pwh_dcfh_hc()::POST_TYPE, 'page' => 'email_templates_list', 'success' => false, 'action' => ''], admin_url('edit.php')));
                }
                exit();
            }
        }

    }
}