<?php

?>
<div class="wrap">
    <h1>Akahoshi Settings</h1>
    <hr class="wp-header-end"/>
    <form id="akahoshi" method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
        <?php settings_fields('akahoshi'); ?>
        <?php do_settings_sections('akahoshi'); ?>
        <?php submit_button(); ?>
    </form>

    <form id="akahoshi-do-it-now" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="akahoshi_do_it_now"/>
        <?php wp_nonce_field('akahoshi_do_it_now', '_akahoshi_nonce'); ?>
    </form>

    <form id="akahoshi-reset-all" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="akahoshi_reset_all"/>
        <?php wp_nonce_field('akahoshi_reset_all', '_akahoshi_nonce'); ?>
    </form>
</div>
