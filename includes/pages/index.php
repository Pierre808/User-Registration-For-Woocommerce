<?php

require_once plugin_dir_path(__FILE__) . "../Placeholders.php";

ob_start();
?>

<h2> Settings </h2>
<hr>

<h3>Placeholders:</h3>
<?php
$placeholders = new Placeholders();
foreach($placeholders->getPlaceholders() as $placeholder) {
    echo "<p>[{$placeholder['val']}]: {$placeholder['description']}</p>";
}
?>

<br>
<h3>Settings:</h3>
<p>Verification Mail content:</p>
<?php 
    $content = get_option('user_registration_for_woocommerce_verification_mail_content_value');
    $editor_id = 'user_registration_for_woocommerce_verification_mail_content';
    $settings = array( 
        'textarea_name' => 'user_registration_for_woocommerce_verification_mail_content',
        'textarea_rows'=>8,
        'media_buttons' => true
    );
    wp_editor(stripslashes($content), $editor_id, $settings);
?>

<p>Verification-link prefix:</p>
<input type="text" id="user_registration_for_woocommerce_verification_link_prefix" value="<?= get_option('user_registration_for_woocommerce_verification_mail_link_prefix') ?>"/>

<?php
$content = ob_get_clean();

echo $content;

/**
 * Custom scripts
 */
wp_enqueue_script('user-registration-for-woocommerce-index-page-script', plugin_dir_url(__FILE__) . '../../assets/js/index-page-script.js');
wp_localize_script('user-registration-for-woocommerce-index-page-script', 'ajaxurl', array( admin_url('admin-ajax.php') ));