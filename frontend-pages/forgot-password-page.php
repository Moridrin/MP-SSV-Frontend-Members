<?php
if (!defined('ABSPATH')) {
    exit;
}
function ssv_forgot_password_page_content($content)
{
    ob_start();
    if (strpos($content, '[ssv-frontend-members-forgot-password]') === false) {
        return $content;
    } else {
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $url          = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?logout=success';
            $link         = '<a href="' . esc_url(wp_logout_url($url)) . '">Logout</a>';
            ob_start();
            ?>
            <div class="card-panel notification">
                <?php echo esc_html($current_user->user_firstname) . ' ' . esc_html($current_user->user_lastname) . ' you\'re already logged in. Do you want to ' . esc_html($link) . '?'; ?>
            </div>
            <?php
            return ob_get_clean();
        } else {
            if (isset($_GET['logout']) && strpos($_GET['logout'], 'success') !== false) {
                ?>
                <div class="card-panel primary">Logout successful</div>
                <?php
            }
        }
    }
    if (current_theme_supports('materialize')) {
        ?>
        <form name="lostpasswordform" id="lostpasswordform" action="<?php echo esc_url( network_site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>" method="post">
            <p>
                <label for="user_login" ><?php _e('Username or Email') ?><br />
                    <input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr($user_login); ?>" size="20" /></label>
            </p>
            <?php
            /**
             * Fires inside the lostpassword form tags, before the hidden fields.
             *
             * @since 2.1.0
             */
            do_action( 'lostpassword_form' ); ?>
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
            <p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Get New Password'); ?>" /></p>
        </form>
        <?php
    }
    $content = ob_get_clean();
    return $content;
}

add_filter('the_content', 'ssv_login_page_content');
?>