<?php

namespace mp_ssv_users;

use mp_ssv_general\ATMS_API;

class ATMSRegistrationFields
{
    public function render($atts, $content)
    {
        $success = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = ATMS_API::post('register-member', $_POST, true);
            $success = ($result['status'] == 200);
            $form = $result['body'];
        } else {
            $form = ATMS_API::get('registration-form');
        }
        ob_start();
        if ($success):
        ?>
            <?= $result['body']; ?>
        <?php else: ?>
        <div class="card">
            <div class="card-content">
                <span class="card-title"><?= isset($atts['title']) ? $atts['title'] : 'title' ?></span>
                <p><?= isset($atts['form-desc']) ? $atts['form-desc'] : 'form-desc' ?></p>
            </div>
        </div>
        <form method="POST">
            <?= $form ?>
        </form>
        <?php
        endif;
        return ob_get_clean();
    }
}

