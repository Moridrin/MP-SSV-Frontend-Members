<?php
namespace mp_ssv_users;
use mp_ssv_general\User;
use WP_User;

if (!defined('ABSPATH')) {
    exit;
}

function mp_ssv_user_get_fields($content)
{
    $value = User::getCurrent() ? 'value="' . esc_attr(User::getCurrent()->user_login) . '"' : '';
    $users = get_users();
    ob_start();
    if (current_theme_supports('materialize')): ?>
        <form name="lostpasswordform" id="lostpasswordform" action="<?php echo esc_url(site_url('wp-login.php?action=lostpassword', 'login_post')); ?>" method="post">
            <?php if (User::isBoard()): ?>
                <div class="input-field col s12">
                    <select name="user_login" title="User Login">
                        <?php /** @var WP_User $user */ ?>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user->user_login ?>"><?= $user->display_name ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="user_login">Username or Email</label>
                </div>
            <?php else: ?>
                <div class="input-field col s12">
                    <input type="text" name="user_login" id="user_login" <?= $value ?>>
                    <label for="user_login">Username or Email</label>
                </div>
                <?php
            endif;
            do_action('lostpassword_form'); ?>
            <input type="hidden" name="redirect_to" value="<?php echo get_site_url(); ?>"/>
            <button class="btn waves-effect waves-light" type="submit" name="wp-submit" id="wp-submit">Send New Password</button>
        </form>
        <?php
    endif;
    return str_replace(SSV_Users::TAG_LOST_PASSWORD, ob_get_clean(), $content);
}
