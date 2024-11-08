<div class="postbox">
    <h3 class="activity-block">
        <span class="dashicons dashicons-book"></span> <?php echo esc_html__('Documentation', pwh_dcfh_hc()::TEXT_DOMAIN); ?>
    </h3>
    <div class="inside">
        <p class="text-justify"><?php echo esc_html__('Be sure to reference our documentation area for instructions on how to use all the settings and features.', pwh_dcfh_hc()::TEXT_DOMAIN); ?></p>
        <?php
        printf(
            sprintf(
                '<a href="%1$s" class="button button-help button-primary" target="_blank">%2$s</a>',
                'https://www.peeayecreative.com/docs/divi-contact-form-helper/',
                esc_html__('View Documentation', pwh_dcfh_hc()::TEXT_DOMAIN)
            )
        );
        ?>
    </div>
</div>