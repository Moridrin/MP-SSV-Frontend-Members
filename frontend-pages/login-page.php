<?php
if (!defined('ABSPATH')) {
    exit;
}
function mp_ssv_login_page_content($content)
{
    ob_start();
    if (strpos($content, '[ssv-frontend-members-login]') === false) {
        return $content;
    } elseif (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $url          = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?logout=success';
        $link         = '<a href="' . wp_logout_url($url) . '">Logout</a>';
        ob_start();
        ?>
        <div class="card-panel primary">
            <?php echo esc_html($current_user->user_firstname) . ' ' . esc_html($current_user->user_lastname) . ' you\'re already logged in. Do you want to ' . $link . '?'; ?>
        </div>
        <?php
        return ob_get_clean();
    } elseif (isset($_GET['logout']) && strpos($_GET['logout'], 'success') !== false) {
        ?>
        <div class="card-panel primary">Logout successful</div>
        <?php
    }
    if (current_theme_supports('materialize')) {
        ?>
        <!--suppress HtmlUnknownTarget -->
        <form name="loginform" id="loginform" action="/wp-login.php" method="post">
            <div class="input-field col s12">
                <input type="text" name="log" id="user_login">
                <label for="user_login">Username / Email</label>
            </div>
            <div class="input-field col s12">
                <input type="password" name="pwd" id="user_pass">
                <label for="user_pass">Password</label>
            </div>
            <div>
                <p>
                    <input name="rememberme" class="filled-in" type="checkbox" id="rememberme" value="forever" checked="checked" style="width: auto; margin-right: 10px;">
                    <label for="rememberme">Remember Me</label>
                </p>
            </div>
            <button class="btn waves-effect waves-light" type="submit" name="wp-submit" id="wp-submit">Login</button>
            <input type="hidden" name="redirect_to" value="<?= get_site_url() ?>/profile">
        </form>
        Don't have an account? <!--suppress HtmlUnknownTarget -->
        <a href="register">Click Here</a> to register.
        <?php
    }
    $content = ob_get_clean();
    return $content;
}

add_filter('the_content', 'mp_ssv_login_page_content');
?>