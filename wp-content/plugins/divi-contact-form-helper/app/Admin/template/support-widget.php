<div class="postbox">
    <h3 class="activity-block">
        <span class="dashicons dashicons-sos"></span> <?php echo esc_html__('Support', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
    </h3>
    <div class="inside">
        <p class="text-justify"><?php echo esc_html__('If you have a question not covered in the documentation, or if you face any technical issue, you can reach out to us on our support page.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
        <?php
        printf(
            sprintf(
                '<a href="%1$s" class="button button-help button-primary" target="_blank">%2$s</a>',
                'https://www.peeayecreative.com/support/',
                esc_html__('Get Support', pwh_dcfh_hc()::TEXT_DOMAIN)
            )
        );
        ?>
    </div>
</div>