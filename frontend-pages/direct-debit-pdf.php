<?php
session_start();
require_once('../include/fpdf/SSV_FPDF.php');
if (
    !isset($_SESSION['ABSPATH'])
    || !isset($_SESSION['first_name'])
    || !isset($_SESSION['initials'])
    || !isset($_SESSION['last_name'])
    || !isset($_SESSION['gender'])
    || !isset($_SESSION['iban'])
    || !isset($_SESSION['date_of_birth'])
    || !isset($_SESSION['street'])
    || !isset($_SESSION['email'])
    || !isset($_SESSION['postal_code'])
    || !isset($_SESSION['city'])
    || !isset($_SESSION['phone_number'])
    || !isset($_SESSION['emergency_phone'])
) {
    ?>
    Incomplete variable set.
    This pdf requires the member to have the following fields:
    <ul>
        <li>first_name</li>
        <li>initials</li>
        <li>last_name</li>
        <li>gender</li>
        <li>iban</li>
        <li>date_of_birth</li>
        <li>street</li>
        <li>email</li>
        <li>postal_code</li>
        <li>city</li>
        <li>phone_number</li>
        <li>emergency_phone</li>
    </ul>
    If the member does have these fields, try reloading the profile page.
    <?php
}
$pdf = new SSV_FPDF();
$pdf->build(
    $_SESSION['ABSPATH'],
    iconv('UTF-8', 'windows-1252', $_SESSION['first_name']),
    iconv('UTF-8', 'windows-1252', $_SESSION['initials']),
    iconv('UTF-8', 'windows-1252', $_SESSION['last_name']),
    iconv('UTF-8', 'windows-1252', $_SESSION['gender']),
    iconv('UTF-8', 'windows-1252', $_SESSION['iban']),
    iconv('UTF-8', 'windows-1252', $_SESSION['date_of_birth']),
    iconv('UTF-8', 'windows-1252', $_SESSION['street']),
    iconv('UTF-8', 'windows-1252', $_SESSION['email']),
    iconv('UTF-8', 'windows-1252', $_SESSION['postal_code']),
    iconv('UTF-8', 'windows-1252', $_SESSION['city']),
    iconv('UTF-8', 'windows-1252', $_SESSION['phone_number']),
    iconv('UTF-8', 'windows-1252', $_SESSION['emergency_phone'])
);
$pdf->Output('I');