<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 10-4-17
 * Time: 10:35
 */

/**
 * @param      $url
 * @param User $user
 *
 * @return mixed
 */
function mp_ssv_users_direct_debit_pdf_url($url, $user)
{
    /** @var WP_Post[] $pages */
    $pages       = SSV_Users::getPagesWithTag(SSV_Users::TAG_PROFILE_FIELDS);
    $wildcardURL = null;
    foreach ($pages as $page) {
        $pageRole = get_post_meta($page->ID, SSV_Users::PAGE_ROLE_META, true);
        if ($pageRole == $user->roles[0]) {
            return get_permalink($page) . '?member=' . $user->ID;
        } elseif ($pageRole == -1) {
            $wildcardURL = get_permalink($page) . '?member=' . $user->ID;
        }
    }
    return $wildcardURL ?: $url;
}

add_filter(SSV_General::HOOK_USER_PDF_URL, 'mp_ssv_users_direct_debit_pdf_url', 10, 2);

require_once('include/fpdf/SSV_DirectDebitPDF.php');

function mp_ssv_user_pdf_content($content)
{
    if (strpos($content, '[direct_debit_pdf]') === false) {
        return $content;
    }
    $user = null;
    if (isset($_GET['member']) && User::currentUserCan('edit_users')) {
        $user = User::getByID($_GET['member']);
    }
    if ($user == null) {
        $user = User::getCurrent();
    }
    if ($user == null) {
        SSV_General::redirect('/login');
        return $content;
    }

    $first_name      = $user->getMeta('first_name');
    $initials        = $user->getMeta('initials');
    $last_name       = $user->getMeta('last_name');
    $gender          = $user->getMeta('gender');
    $iban            = $user->getMeta('iban');
    $date_of_birth   = $user->getMeta('date_of_birth');
    $street          = $user->getMeta('street');
    $email           = $user->getMeta('email');
    $postal_code     = $user->getMeta('postal_code');
    $city            = $user->getMeta('city');
    $phone_number    = $user->getMeta('phone_number');
    $emergency_phone = $user->getMeta('emergency_phone');

    $pdf = new SSV_DirectDebitPDF();
    $pdf->build(
        $_SESSION['ABSPATH'],
        iconv('UTF-8', 'windows-1252', $first_name),
        iconv('UTF-8', 'windows-1252', $initials),
        iconv('UTF-8', 'windows-1252', $last_name),
        iconv('UTF-8', 'windows-1252', $gender),
        iconv('UTF-8', 'windows-1252', $iban),
        iconv('UTF-8', 'windows-1252', $date_of_birth),
        iconv('UTF-8', 'windows-1252', $street),
        iconv('UTF-8', 'windows-1252', $email),
        iconv('UTF-8', 'windows-1252', $postal_code),
        iconv('UTF-8', 'windows-1252', $city),
        iconv('UTF-8', 'windows-1252', $phone_number),
        iconv('UTF-8', 'windows-1252', $emergency_phone)
    );
    $pdf->Output('I');
}

add_filter('the_content', 'mp_ssv_user_pdf_content');