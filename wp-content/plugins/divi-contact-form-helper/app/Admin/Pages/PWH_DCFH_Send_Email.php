<?php

namespace PWH_DCFH\App\Admin\Pages;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_Send_Email')) {
    class PWH_DCFH_Send_Email
    {

        private $_post_type;

        private $_post_id;

        private $email_templates;

        /**
         * Initializer Of The Class
         *
         * Add/Remove Necessary Actions/Filters
         */
        public function init()
        {
            $_page = pwh_dcfh_helpers()::current_page();
            $this->_post_id = isset($_GET['post_id']) ? sanitize_key($_GET['post_id']) : null; // phpcs:ignore
            $this->_post_type = pwh_dcfh_hc()::POST_TYPE;
            add_action('admin_menu', [$this, 'menu']);
            if ('send_email' === $_page && !empty($this->_post_id)) {
                $this->email_templates = pwh_dcfh_email_tpl_handler()::get_templates();
                if (null === get_post($this->_post_id)) {
                    wp_safe_redirect(add_query_arg(['post_type' => $this->_post_type], admin_url('edit.php')));
                    exit();
                }
                add_action('view_send_email', [$this, 'display_form'], 10, 1);
                add_action('admin_init', [$this, 'admin_hooks']);
                add_filter(pwh_dcfh_hc()::FILTER_PREFIX.'admin_localizations', [$this, 'load_email_templates']);
            }
        }

        /**
         * Register Admin Hooks
         */
        public function admin_hooks()
        {
            ob_start();
            add_action('wp_mail_failed', [$this, 'wp_mailed_failed_error'], 10, 1);
        }

        /**
         * Filter Admin Localizations
         *
         * @return array
         */
        public function load_email_templates($localizations)
        {
            $localizations['emailTemplates'] = wp_json_encode($this->email_templates);

            return $localizations;
        }

        /**
         * Register Wp Admin Menu
         */
        public function menu()
        {
            add_submenu_page(pwh_dcfh_hc()::ADMIN_MENU_PAGE_SLUG, __('Send Email', pwh_dcfh_hc()::TEXT_DOMAIN), null, 'manage_options', 'send_email', [$this, 'process_form']);
        }

        /**
         * Process Form Actions
         */
        public function process_form()
        {
            $post_request = [
                'email_from' => get_option('admin_email'),
                'email_to' => '',
                'email_subject' => '',
                'email_body' => '',
            ];
            if (isset($_POST['btn_send_email']) && isset($_POST['_wpnonce'])) {
                if (!wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'action_send_email')) {
                    wp_die(esc_html__('Unable to submit this form, please refresh and try again.', pwh_dcfh_hc()::TEXT_DOMAIN));
                }
                $post_request = $_POST;
                $email_from = isset($_POST['email_from']) ? sanitize_text_field($_POST['email_from']) : null;
                $email_to = isset($_POST['email_to']) ? sanitize_text_field($_POST['email_to']) : null;
                $email_subject = isset($_POST['email_subject']) ? sanitize_text_field(htmlspecialchars_decode(stripslashes(sanitize_text_field($_POST['email_subject'])), ENT_QUOTES)) : null; // phpcs:ignore
                $email_body = isset($_POST['email_body']) ? wpautop(htmlspecialchars_decode(stripslashes($_POST['email_body']), ENT_QUOTES)) : null; // phpcs:ignore
                $validate_request = true;
                if (empty($email_from)) {
                    pwh_dcfh_helpers()::add_error_message(__('Email from is required.', pwh_dcfh_hc()::TEXT_DOMAIN));
                    $validate_request = false;
                }
                if (empty($email_subject)) {
                    pwh_dcfh_helpers()::add_error_message(__('Email subject is required.', pwh_dcfh_hc()::TEXT_DOMAIN));
                    $validate_request = false;
                }
                if (empty($email_body)) {
                    pwh_dcfh_helpers()::add_error_message(__('Email body is required.', pwh_dcfh_hc()::TEXT_DOMAIN));
                    $validate_request = false;
                }
                if ($validate_request) {
                    $post_obj = get_post($this->_post_id);
                    if (is_serialized($post_obj->post_excerpt)) {
                        $fields = pwh_dcfh_helpers()::maybe_unserialize($post_obj->post_excerpt);
                        $email_keywords = pwh_dcfh_db_handler()::get_send_email_static_keywords_values($post_obj);
                        if (!empty($fields)) {
                            foreach ($fields as $index => $field) {
                                $email_keywords["%%".$field["id"]."%%"] = $field['value'];
                            }
                        }
                        // Email Body
                        if (!empty($email_keywords)) {
                            $email_body = str_replace(array_keys($email_keywords), array_values($email_keywords), $email_body);
                        }
                        // Filters the email header
                        add_filter('wp_mail_content_type', function () {
                            return 'text/html';
                        });
                        // Filters the email address to send from.
                        add_filter('wp_mail_from', function ($email) use ($email_from) {
                            return $email_from;
                        });
                        // Filters the name to associate with the “from” email address.
                        add_filter('wp_mail_from_name', function ($name) {
                            return get_bloginfo('name');
                        });
                        // Send Email
                        if (wp_mail($email_to, $email_subject, $email_body)) {
                            // Update Replies
                            $last_log_count = pwh_dcfh_logger()->get_log_count('sent_email_log', $this->_post_id);
                            pwh_dcfh_logger()->add_log('email-sent-log-'.$last_log_count, maybe_serialize([
                                'email_from' => $email_from,
                                'email_to' => $email_to,
                                'email_body' => $email_body,
                            ]), 'sent_email_log', $this->_post_id);
                            // Redirect
                            wp_safe_redirect(add_query_arg([
                                'post' => $this->_post_id,
                                'action' => 'edit',
                                'success' => true,
                                '_wpnonce' => wp_create_nonce('entry_action'),
                                'email_sent_to' => $email_to,
                            ], admin_url('post.php')));
                        } else {
                            wp_safe_redirect(add_query_arg([
                                'post' => $this->_post_id,
                                'action' => 'edit',
                                'success' => false,
                                '_wpnonce' => wp_create_nonce('entry_action'),
                                'email_sent_to' => $email_to,
                            ], admin_url('post.php')));
                        }
                        exit();
                    }
                }
            }
            do_action('view_send_email', $post_request);
        }

        /**
         * Display Form HTML Content
         *
         * @param $post_request
         */
        public function display_form($post_request)
        { ?>
            <div class="wrap pwh-dcfh-page" id="page-send-email">
                <h1 class="wp-heading-inline"><?php esc_html_e(get_admin_page_title(), pwh_dcfh_hc()::TEXT_DOMAIN); ?></h1>
                <a href="<?php echo esc_url(add_query_arg(['post_type' => $this->_post_type], admin_url('edit.php'))); ?>" class="page-title-action"><?php esc_html_e('Entries',
                        pwh_dcfh_hc()::TEXT_DOMAIN); ?></a>
                <a href="<?php echo esc_url(add_query_arg(['post_type' => $this->_post_type, 'page' => 'create_email_template'],
                    admin_url('edit.php'))); ?>" class="page-title-action"><?php esc_html_e('Add New Template', pwh_dcfh_hc()::TEXT_DOMAIN); ?></a>
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
                                            <?php if (!empty($this->email_templates)) { ?>
                                                <tr>
                                                    <th><?php esc_html_e('Choose Template:', pwh_dcfh_hc()::TEXT_DOMAIN); ?></th>
                                                    <td>
                                                        <select title="" name="email_template" id="email_template" class="form-control jsEmailTemplate">
                                                            <option value=""><?php esc_html_e('Select Template', pwh_dcfh_hc()::TEXT_DOMAIN); ?></option>
                                                            <?php foreach ($this->email_templates as $key => $value) { ?>
                                                                <option value="<?php esc_html_e($key); ?>"><?php esc_html_e($value['tpl_name']); ?></option>
                                                            <?php } ?>
                                                        </select>
                                                        <p class="helper"><?php esc_html_e('Choose predefined template from list.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                            <tr>
                                                <th><?php esc_html_e('Email From:', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
                                                    <span class="required">*</span></th>
                                                <td>
                                                    <input title="" type="email" name="email_from" id="email_from" class="form-control" value="<?php esc_html_e($post_request['email_from']); ?>" placeholder="<?php
                                                    esc_html_e('Replay From', pwh_dcfh_hc()::TEXT_DOMAIN); ?>">
                                                    <p class="helper"><?php esc_html_e('Write email address for from.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php esc_html_e('Email Subject:', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
                                                    <span class="required">*</span></th>
                                                <td>
                                                    <input title="" type="text" name="email_subject" id="email_subject" class="form-control" value="<?php esc_html_e($post_request['email_subject']);
                                                    ?>" placeholder="<?php
                                                    esc_html_e('Subject', pwh_dcfh_hc()::TEXT_DOMAIN); ?>">
                                                    <p class="helper"><?php esc_html_e('Write subject for email.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php esc_html_e('Reply To:', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
                                                    <span class="required">*</span></th>
                                                <td colspan="4">
                                                    <select title="" name="email_to" id="email_to" class="form-control">
                                                        <?php foreach (pwh_dcfh_user_handler()::get_user_emails($this->_post_id) as $key => $email) { ?>
                                                            <option value="<?php esc_html_e($key); ?>" <?php selected($key, $post_request['email_to']); ?>><?php esc_html_e($email); ?></option>
                                                        <?php } ?>
                                                    </select>
                                                    <p class="helper"><?php esc_html_e('Choose user email address to reply back.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php esc_html_e('Email Data Tags:', pwh_dcfh_hc()::TEXT_DOMAIN); ?></th>
                                                <td>
                                                    <div id="merge-data-tsgs-list">
                                                        <?php pwh_dcfh_db_handler()::get_contact_form_send_email_data($this->_post_id); ?>
                                                    </div>
                                                    <?php
                                                    $create_template_url = esc_url(add_query_arg(['post_id' => $this->_post_id, 'page' => 'create_email_template'], admin_url(pwh_dcfh_hc()::ADMIN_MENU_PAGE_SLUG)));
                                                    echo sprintf('<p class="helper">%1$s <a href="%2$s">%3$s</a></p>', esc_html(' Or use these keywords to create email template.'),
                                                        esc_url($create_template_url), esc_html('Click Here'))
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><?php esc_html_e('Email Message:', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
                                                    <span class="required">*</span></th>
                                                <td>
                                                    <?php wp_editor($post_request['email_body'], 'email_body', [
                                                        'wpautop' => true,
                                                        'media_buttons' => false,
                                                        'textarea_name' => 'email_body',
                                                        'textarea_rows' => 6
                                                    ]);
                                                    ?>
                                                    <p class="helper"><?php esc_html_e('Write email body. Please use correct keywords.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <?php
                                        wp_nonce_field('action_send_email', '_wpnonce');
                                        submit_button(esc_html__('Send Email', pwh_dcfh_hc()::TEXT_DOMAIN), 'primary large', 'btn_send_email');
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

        /**
         * Get WP Email Faield Errors
         *
         * @param $wp_error
         *
         * @return void
         */
        public function wp_mailed_failed_error($wp_error)
        {
            $wp_mail_failed = isset($wp_error->errors['wp_mail_failed']) ? $wp_error->errors['wp_mail_failed'] : null;
            if (!empty($wp_mail_failed)) {
                $error_messages = implode(', ', $wp_mail_failed);
                delete_transient('pwh_dcfh_email_failed');
                set_transient('pwh_dcfh_email_failed', $error_messages, MINUTE_IN_SECONDS);
            }
        }

    }
}