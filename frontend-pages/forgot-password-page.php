<?php
if (!defined('ABSPATH')) {
    exit;
}
function ssv_forgot_password_page_content($content)
{
    global $post;
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
            <div class="mui-panel notification">
                <?php echo esc_html($current_user->user_firstname) . ' ' . esc_html($current_user->user_lastname) . ' you\'re already logged in. Do you want to ' . esc_html($link) . '?'; ?>
            </div>
            <?php
            return ob_get_clean();
        } else {
            if (isset($_GET['logout']) && strpos($_GET['logout'], 'success') !== false) {
                ?>
                <div class="mui-panel notification">Logout successful</div>
                <?php
            }
        }
    }
    if (current_theme_supports('mui')) {
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
    } else {
        ?>
        <!--suppress HtmlUnknownTarget -->
        <form name="loginform" id="loginform" action="/wp-login.php" method="post">
            <div class="mui-textfield mui-textfield--float-label">
                <label for="user_login">Username / Email</label>
                <input type="text" name="log" id="user_login">
            </div>
            <div class="mui-textfield mui-textfield--float-label">
                <label for="user_pass">Password</label>
                <input type="password" name="pwd" id="user_pass">
            </div>
            <div>
                <label for="rememberme">Remember Me</label>
                <input name="rememberme" type="checkbox" id="rememberme" value="forever" checked="checked" style="width: auto; margin-right: 10px;">
            </div>
            <button class="mui-btn mui-btn--primary button-primary" type="submit" name="wp-submit" id="wp-submit">Login</button>
            <input type="hidden" name="redirect_to" value="<?= get_site_url() ?>/profile">
        </form>
        <br/>
        Don't have an account? <!--suppress HtmlUnknownTarget -->
        <a href="register">Click Here</a> to register.
        <?php
    }
    $content = ob_get_clean();
    return $content;
}

add_filter('the_content', 'ssv_login_page_content');
?>