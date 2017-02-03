<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 21-1-17
 * Time: 7:38
 */
if (SSV_General::isValidPOST(SSV_Users::ADMIN_REFERRER_OPTIONS)) {
    if (isset($_POST['reset'])) {
        SSV_Users::resetOptions();
    } else {
        update_option(SSV_Users::OPTION_NEW_MEMBER_ADMIN_EMAIL, filter_var($_POST['email_admin_on_registration'], FILTER_VALIDATE_BOOLEAN));
        update_option(SSV_Users::OPTION_NEW_MEMBER_REGISTRANT_EMAIL, filter_var($_POST['email_registrant_on_registration'], FILTER_VALIDATE_BOOLEAN));
    }
}
?>
<form method="post" action="#">
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Email Admin</th>
            <td>
                <label>
                    <input type="hidden" name="email_admin_on_registration" value="false"/>
                    <input type="checkbox" name="email_admin_on_registration" value="true" <?= get_option(SSV_Users::OPTION_NEW_MEMBER_ADMIN_EMAIL) ? 'checked' : '' ?> />
                    When someone registers the secretary will receive an email.
                </label>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Email Registrant</th>
            <td>
                <label>
                    <input type="hidden" name="email_registrant_on_registration" value="false"/>
                    <input type="checkbox" name="email_registrant_on_registration" value="true" <?= get_option(SSV_Users::OPTION_NEW_MEMBER_REGISTRANT_EMAIL) ? 'checked' : '' ?>/>
                    When someone registers he/she will receive a confirmation email.
                </label>
            </td>
        </tr>
    </table>
    <?= SSV_General::getFormSecurityFields(SSV_Users::ADMIN_REFERRER_OPTIONS); ?>
</form>
