<?php
function ssv_users_add_sub_menu()
{
    add_submenu_page('ssv_settings', 'Users Options', 'Users', 'manage_options', __FILE__, 'ssv_users_options_page_content');
}

function ssv_users_options_page_content()
{
    if (SSV_General::isValidPOST(SSV_Users::ADMIN_REFERER_OPTIONS)) {
        if (isset($_POST['reset'])) {
            SSV_Users::CLEAN_INSTALL();
//            SSV_Users::resetOptions();
        } else {
            update_option(SSV_Events::OPTION_DEFAULT_REGISTRATION_STATUS, $_POST['default_registration_status']);
            update_option(SSV_Events::OPTION_REGISTRATION_MESSAGE, $_POST['registration_message']);
            update_option(SSV_Events::OPTION_CANCELLATION_MESSAGE, $_POST['cancellation_message']);
            update_option(SSV_Events::OPTION_EMAIL_AUTHOR, filter_var($_POST['email_on_registration'], FILTER_VALIDATE_BOOLEAN));
            update_option(SSV_Events::OPTION_EMAIL_ON_REGISTRATION_STATUS_CHANGED, filter_var($_POST['email_on_registration_status_changed'], FILTER_VALIDATE_BOOLEAN));
        }
    }
    $active_tab = "general";
    if (isset($_GET['tab'])) {
        $active_tab = $_GET['tab'];
    }
    ?>
    <div class="wrap">
        <h1>Users Options</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=<?= $_GET['page'] ?>&tab=general" class="nav-tab <?= $active_tab == 'general' ? 'nav-tab-active' : '' ?>">General</a>
            <a href="?page=<?= $_GET['page'] ?>&tab=profile_page" class="nav-tab <?= $active_tab == 'profile_page' ? 'nav-tab-active' : '' ?>">Profile Page</a>
            <?php if (get_option(SSV_Users::OPTION_CUSTOM_REGISTER_PAGE, false)): ?>
                <a href="?page=<?= $_GET['page'] ?>&tab=register_page" class="nav-tab <?= $active_tab == 'register_page' ? 'nav-tab-active' : '' ?>">Register Page</a>
            <?php endif; ?>
            <a href="?page=<?= $_GET['page'] ?>&tab=users_page_columns" class="nav-tab <?= $active_tab == 'users_page_columns' ? 'nav-tab-active' : '' ?>">Users Page Columns</a>
            <a href="?page=<?= $_GET['page'] ?>&tab=email" class="nav-tab <?= $active_tab == 'email' ? 'nav-tab-active' : '' ?>">Email</a>
            <a href="http://2016.bosso.nl/ssv-users/" target="_blank" class="nav-tab">
                Help <img src="<?= SSV_Users::URL ?>general/images/link-new-tab.png" width="14px" style="vertical-align:middle">
            </a>
        </h2>
        <?php
        switch ($active_tab) {
            case "general":
                break;
            case "profile_page":
            case "register_page":
                break;
            case "users_page_columns":
                break;
            case "email":
                break;
        }
        ?>
    </div>
    <?php
}

add_action('admin_menu', 'ssv_users_add_sub_menu');

function ssv_users_general_options_page_content()
{
    ?><h2><a href="?page=<?= str_replace(SSV_Users::PATH, 'ssv-users/', __FILE__) ?>">Users Options</a></h2><?php
}

add_action(SSV_General::HOOK_GENERAL_OPTIONS_PAGE_CONTENT, 'ssv_users_general_options_page_content');
