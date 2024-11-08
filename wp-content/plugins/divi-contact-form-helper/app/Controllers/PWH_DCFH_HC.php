<?php

namespace PWH_DCFH\App\Controllers;
defined('ABSPATH') or die('HEY, WHAT ARE YOU DOING HERE? YOU SILLY HUMAN!');
if (!class_exists('PWH_DCFH_HC')) {
    class PWH_DCFH_HC
    {

        private static $_instance;

        const POST_TYPE = 'pwh_dcfh';

        const FILE = PWH_DCFH_PLUGIN_FILE;

        const BASENAME = PWH_DCFH_PLUGIN_BASENAME;

        const TEXT_DOMAIN = 'pwh-dcfh';

        const FILTER_PREFIX = 'pwh_dcfh_f_';

        const DIVI_CONTACT_FORM_SLUG = 'et_pb_contact_form';

        const DIVI_CONTACT_FORM_FIELDS_SLUG = 'et_pb_contact_field';

        const CF_TITLE_OPTION_NAME = '_pwh_dcfh_contact_form_title_';

        const CF_PAGEID_OPTION_NAME = '_pwh_dcfh_contact_form_pageid_';

        const CF_VIEWS_OPTION_NAME = '_pwh_dcfh_contact_form_views_';

        const CF_UNIQUE_VIEWS_OPTION_NAME = '_pwh_dcfh_contact_form_unique_views_';

        const CF_PAGE_ID_META_KEY = '_pwh_dcfh_page_id';

        const CF_FORM_ID_META_KEY = '_pwh_dcfh_contact_form_id';

        const CF_FIELDS_META_KEY = '_pwh_dcfh_form_fields';

        const CF_CONTACT_EMAIL_META_KEY = '_pwh_dcfh_contact_email';

        const CF_IP_ADDRESS_META_KEY = '_pwh_dcfh_ip_address';

        const CF_USER_AGEN_META_KEY = '_pwh_dcfh_user_agent';

        const CF_REFERER_URL_META_KEY = '_pwh_dcfh_referer_url';

        const CF_READ_BY_META_KEY = '_pwh_dcfh_read_by';

        const EMAIL_SENT_LOGS_META_KEY = '_pwh_dcfh_email_sent_log';

        const CF_EMAIL_TPL_PREFIX = '_pwh_dcfh_email_tpl_';

        const ENTRY_CLONE_LOG_META_KEY = '_pwh_dcfh_clone_log';

        const AJAX_NONCE = 'pwh-dcfh-nonce';

        const DATEPICKER_CLASS = 'et_pb_datetimepicker_input';

        const UPLOAD_FILE_CLASS = 'et_pb_file_input';

        const MAX_UPLOAD_FILE_SIZE = '104857600';

        const UPLOAD_DIR = 'pwh-dcfh-uploads';

        const TEMP_UPLOAD_DIR = 'tmp';

        const ADMIN_MENU_PAGE_SLUG = 'edit.php?post_type='.self::POST_TYPE;

        /**
         * Get Class Instance
         *
         * @return PWH_DCFH_HC
         */
        public static function instance()
        {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

    }
}
