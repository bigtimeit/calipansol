<?php

namespace PWH_DCFH\App\Admin\Pages;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Create_Email_Template')) {
    class PWH_DCFH_Create_Email_Template
    {

        private $_post_type;

        private $contact_forms_data;

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            $this->_post_type = pwh_dcfh_hc()::POST_TYPE;
            $_page = pwh_dcfh_helpers()::current_page();
            add_action('admin_menu', [$this, 'menu']);
            if ('create_email_template' === $_page) {
                $this->contact_forms_data = pwh_dcfh_db_handler()::get_contact_form_template_data();
                add_action('admin_init', function () {
                    ob_start();
                });
                add_action('view_create_email_template', [$this, 'display_form'], 10, 1);
                add_filter(pwh_dcfh_hc()::FILTER_PREFIX.'admin_localizations', [$this, 'load_tpl_data']);
            }
        }

        /**
         * Register Wp Admin Menu
         */
        public function menu()
        {
            add_submenu_page(pwh_dcfh_hc()::ADMIN_MENU_PAGE_SLUG, __('Create Template', pwh_dcfh_hc()::TEXT_DOMAIN), null, 'manage_options', 'create_email_template', [$this, 'process_form']);
        }

        /**
         * Filter Admin Localizations
         *
         * @return array
         */
        public function load_tpl_data()
        {
            $localizations['tplData'] = wp_json_encode($this->contact_forms_data);

            return $localizations;
        }

        /**
         * Process Form Actions
         */
        public function process_form()
        {
            $_post_id = isset($_GET['post_id']) ? sanitize_text_field($_GET['post_id']) : null;
            $contact_form_id = pwh_dcfh_post_meta_handler()::get_contact_form_id_meta_value($_post_id);
            $post_request = [
                'action' => 'create',
                'tpl_id' => '',
                'contact_form_id' => $contact_form_id,
                'tpl_type' => '',
                'tpl_name' => '',
                'email_from' => '',
                'email_subject' => '',
                'email_body' => '',
            ];
            // Create
            if (isset($_POST['btn_create_template']) && isset($_POST['_wpnonce'])) {
                $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : 'create';
                $old_tpl_id = isset($_POST['old_tpl_id']) ? sanitize_text_field($_POST['old_tpl_id']) : null;
                $contact_form_id = isset($_POST['contact_form_id']) ? sanitize_text_field($_POST['contact_form_id']) : null;
                $tpl_type = isset($_POST['tpl_type']) ? sanitize_text_field($_POST['tpl_type']) : null;
                $tpl_name = isset($_POST['tpl_name']) ? sanitize_text_field($_POST['tpl_name']) : null;
                $email_from = isset($_POST['email_from']) ? sanitize_text_field($_POST['email_from']) : null;
                $email_subject = isset($_POST['email_subject']) ? sanitize_text_field(htmlspecialchars_decode(stripslashes(sanitize_text_field($_POST['email_subject'])), ENT_QUOTES)) : null; // phpcs:ignore
                $email_body = isset($_POST['email_body']) ? wpautop(htmlspecialchars_decode(stripslashes($_POST['email_body']), ENT_QUOTES)) : null; // phpcs:ignore
                $post_request = $_POST; // phpcs:ignore
                $validate_request = true;
                if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), $action.'_email_template')) {
                    wp_die(esc_html__('Unable to submit this form, please refresh and try again.', pwh_dcfh_hc()::TEXT_DOMAIN));
                }
                if (empty($tpl_name)) {
                    pwh_dcfh_helpers()::add_error_message(__('Template name is required.', pwh_dcfh_hc()::TEXT_DOMAIN));
                    $validate_request = false;
                }
                if (!in_array($tpl_type, ['admin', 'confirmation'], true)) {
                    if (empty($email_from)) {
                        pwh_dcfh_helpers()::add_error_message(__('Email from is required.', pwh_dcfh_hc()::TEXT_DOMAIN));
                        $validate_request = false;
                    }
                    if (empty($email_subject)) {
                        pwh_dcfh_helpers()::add_error_message(__('Email subject is required.', pwh_dcfh_hc()::TEXT_DOMAIN));
                        $validate_request = false;
                    }
                }
                if (empty($email_body)) {
                    pwh_dcfh_helpers()::add_error_message(__('Email body is required.', pwh_dcfh_hc()::TEXT_DOMAIN));
                    $validate_request = false;
                }
                $tpl_name = pwh_dcfh_helpers()::clean_string($tpl_name);
                if ($action == 'create') {
                    $tpl_id = pwh_dcfh_email_tpl_handler()::set_template_id($tpl_name);
                    if (pwh_dcfh_email_tpl_handler()::is_template_exist($tpl_id) && !empty($tpl_name)) {
                        pwh_dcfh_helpers()::add_error_message(__('The template with same name already exist. Please choose another name.', pwh_dcfh_hc()::TEXT_DOMAIN));
                        $validate_request = false;
                    }
                } else {
                    $tpl_id = $old_tpl_id;
                }
                if ($validate_request) {
                    if ($action === 'create') {
                        $template = [
                            'contact_form_id' => $contact_form_id,
                            'tpl_type' => $tpl_type,
                            'tpl_name' => $tpl_name,
                            'email_from' => $email_from,
                            'email_subject' => $email_subject,
                            'email_body' => $email_body,
                        ];
                        $template['created_by'] = get_current_user_id();
                        $template['modified_by'] = '';
                        $template['created_at'] = current_time('mysql');
                        $template['modified_at'] = current_time('mysql');
                    } elseif ($action === 'edit') {
                        $template = get_option($tpl_id);
                        $template['contact_form_id'] = $contact_form_id;
                        $template['tpl_type'] = $tpl_type;
                        $template['tpl_name'] = $tpl_name;
                        $template['email_from'] = $email_from;
                        $template['email_subject'] = $email_subject;
                        $template['email_body'] = $email_body;
                        $template['modified_by'] = get_current_user_id();
                        $template['modified_at'] = current_time('mysql');
                    }
                    $response = update_option($tpl_id, $template, 'no');
                    if ($response) {
                        wp_safe_redirect(add_query_arg([
                            'post_type' => $this->_post_type,
                            'page' => 'email_templates_list',
                            'success' => true,
                            'action' => $action,
                        ], admin_url('edit.php')));
                    } else {
                        wp_safe_redirect(add_query_arg([
                            'post_type' => $this->_post_type,
                            'page' => 'email_templates_list',
                            'success' => false,
                            'action' => '',
                        ], admin_url('edit.php')));
                    }
                    exit();
                }
            }
            // Edit
            if (isset($_GET['_wpnonce']) && (isset($_GET['action']) && 'edit' === $_GET['action']) && isset($_GET['tpl_id'])) {
                if (!wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'edit_email_template')) {
                    wp_safe_redirect(add_query_arg([
                        'post_type' => $this->_post_type,
                        'page' => 'email_templates_list',
                    ], admin_url('edit.php')));
                    exit();
                }
                $tpl_id = sanitize_text_field($_GET['tpl_id']);
                $options = get_option($tpl_id);
                if (empty($options)) {
                    wp_safe_redirect(add_query_arg([
                        'post_type' => $this->_post_type,
                        'page' => 'email_templates_list',
                    ], admin_url('edit.php')));
                    exit();
                }
                $post_request['action'] = sanitize_text_field($_GET['action']);
                $post_request['tpl_id'] = $tpl_id;
                $post_request['contact_form_id'] = $options['contact_form_id'];
                $post_request['tpl_type'] = isset($options['tpl_type']) ? $options['tpl_type'] : '';
                $post_request['tpl_name'] = $options['tpl_name'];
                $post_request['email_from'] = $options['email_from'];
                $post_request['email_subject'] = $options['email_subject'];
                $post_request['email_body'] = $options['email_body'];
            }
            do_action('view_create_email_template', $post_request);
        }

        /**
         * Display Form HTML Content
         *
         * @param $post_request
         */
        public function display_form($post_request)
        {
            $contact_forms_data = $this->contact_forms_data;
            ?>
            <div class="wrap pwh-dcfh-page" id="page-create-email-template">
                <h1 class="wp-heading-inline"><?php esc_html_e(get_admin_page_title(), pwh_dcfh_hc()::TEXT_DOMAIN); ?></h1>
                <a href="<?php echo esc_url(add_query_arg(['post_type' => $this->_post_type, 'page' => 'email_templates_list'],
                    admin_url('edit.php'))); ?>" class="page-title-action"><?php esc_html_e('Templates', pwh_dcfh_hc()::TEXT_DOMAIN); ?></a>
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
                                            <tr class="tpl_type">
                                                <th><?php esc_html_e('Template Type:', pwh_dcfh_hc()::TEXT_DOMAIN); ?></th>
                                                <td>
                                                    <select title="" name="tpl_type" id="tpl_type" class="form-control">
                                                        <?php foreach (pwh_dcfh_email_tpl_handler()::get_templates_types() as $key => $type) { ?>
                                                            <option value="<?php echo esc_html($key); ?>" <?php selected($key,
                                                                $post_request['tpl_type']); ?>><?php echo esc_html($type); ?></option>
                                                        <?php } ?>
                                                    </select>
                                                    <p class="helper"><?php esc_html_e('Choose contact form from list.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                </td>
                                            </tr>
                                            <tr class="tpl_name">
                                                <th><?php esc_html_e('Template Name:', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
                                                    <span class="required">*</span></th>
                                                <td>
                                                    <input title="" type="text" name="tpl_name" id="tpl_name" class="form-control" value="<?php echo esc_html($post_request['tpl_name']); ?>" placeholder="<?php
                                                    esc_html_e('Template Name', pwh_dcfh_hc()::TEXT_DOMAIN); ?>">
                                                    <p class="helper"><?php esc_html_e('Write unique template name.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                </td>
                                            </tr>
                                            <tr class="contact_form_id">
                                                <th><?php esc_html_e('Contact Form:', pwh_dcfh_hc()::TEXT_DOMAIN); ?></th>
                                                <td>
                                                    <select title="" name="contact_form_id" id="contact_form_id" class="form-control">
                                                        <option value=""><?php esc_html_e('Please Select Contact Form', pwh_dcfh_hc()::TEXT_DOMAIN); ?></option>
                                                        <?php if (!empty($contact_forms_data)) {
                                                            foreach ($contact_forms_data as $key => $value) { ?>
                                                                <option value="<?php echo esc_html($key); ?>" <?php selected($key,
                                                                    $post_request['contact_form_id']); ?>><?php echo esc_html($value['contact_form_title']); ?></option>
                                                            <?php }
                                                        } ?>
                                                    </select>
                                                    <p class="helper"><?php esc_html_e('Choose contact form from list.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                    <div id="merge-data-tsgs-list"></div>
                                                </td>
                                            </tr>
                                            <tr class="email_from">
                                                <th><?php esc_html_e('Email From:', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
                                                    <span class="required">*</span></th>
                                                <td>
                                                    <input title="" type="email" name="email_from" id="email_from" class="form-control" value="<?php echo esc_html($post_request['email_from']); ?>" placeholder="<?php esc_html_e('Replay From',
                                                        pwh_dcfh_hc()::TEXT_DOMAIN); ?>">
                                                    <p class="helper"><?php esc_html_e('Write email address for email from.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                </td>
                                            </tr>
                                            <tr class="email_subject">
                                                <th><?php esc_html_e('Email Subject:', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
                                                    <span class="required">*</span></th>
                                                <td>
                                                    <input title="" type="text" name="email_subject" id="email_subject" class="form-control" value="<?php echo esc_html($post_request['email_subject']); ?>" placeholder="<?php esc_html_e('Subject',
                                                        pwh_dcfh_hc()::TEXT_DOMAIN); ?>">
                                                    <p class="helper"><?php esc_html_e('Write email subject.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                </td>
                                            </tr>
                                            <tr class="email_body">
                                                <th><?php esc_html_e('Email Message:', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
                                                    <span class="required">*</span></th>
                                                <td>
                                                    <?php
                                                    wp_editor($post_request['email_body'], 'email_body', [
                                                        'wpautop' => true,
                                                        'media_buttons' => true,
                                                        'textarea_name' => 'email_body',
                                                        'textarea_rows' => 10,
                                                    ]);
                                                    ?>
                                                    <p class="helper"><?php esc_html_e('Write email body using given keywords.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <input type="hidden" name="action" value="<?php echo esc_attr($post_request['action']); ?>">
                                        <input type="hidden" name="old_tpl_id" value="<?php echo isset($post_request['tpl_id']) ? esc_attr($post_request['tpl_id']) : ''; ?>">
                                        <?php
                                        $button_text = 'create' == $post_request['action'] ? __('Create Template', pwh_dcfh_hc()::TEXT_DOMAIN) : __('Update Template', pwh_dcfh_hc()::TEXT_DOMAIN);
                                        wp_nonce_field(esc_html($post_request['action']).'_email_template', '_wpnonce');
                                        submit_button($button_text, 'primary large action-btn', 'btn_create_template');
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