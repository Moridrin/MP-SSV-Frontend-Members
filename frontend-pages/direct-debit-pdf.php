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
    $_SESSION['first_name'],
    $_SESSION['initials'],
    $_SESSION['last_name'],
    $_SESSION['gender'],
    $_SESSION['iban'],
    $_SESSION['date_of_birth'],
    $_SESSION['street'],
    $_SESSION['email'],
    $_SESSION['postal_code'],
    $_SESSION['city'],
    $_SESSION['phone_number'],
    $_SESSION['emergency_phone']
);
$pdf->Output('I');