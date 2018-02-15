<?php

namespace mp_ssv_users;

use mp_ssv_general\ATMS_API;

class ATMSRegistrationFields
{
    public function render($atts, $content)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return ATMS_API::post('register-member', $_POST);
        }
        $html = '<form method="POST">';
        $html .= ATMS_API::get('registration-form');
        $html .= '</form>';
        return $html;
    }
}

