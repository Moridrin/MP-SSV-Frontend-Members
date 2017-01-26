<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 26-1-17
 * Time: 8:55
 */

/**
 * @param $content
 *
 * @return string
 */
function mp_ssv_user_get_registration_fields($content)
{
    if (isset($_GET['member'])) {
        $user = User::getByID($_GET['member']);
    } else {
        $user = User::getCurrent();
    }
    $fields = Field::fromMeta();
    $fields = $fields ?: array();
    foreach ($fields as $field) {
        if ($field instanceof TabField) {
            foreach ($field->fields as $childField) {
                if ($childField instanceof InputField) {
                    $childField->value = $user->getMeta($childField->name);
                }
            }
        } elseif ($field instanceof InputField && $user != null) {
            $field->value = $user->getMeta($field->name);
        }
    }
    ob_start();
    ?>
    <form action="<?= get_permalink() ?>" method="POST">
        <?= Field::getFormFromFields($fields); ?>
        <div class="input-field">
            <input type="password" id="password" name="password" class="validate invalid" required="">
            <label for="password" class="">Password*</label>
        </div>
        <div class="input-field">
            <input type="password" id="confirm_password" name="confirm_password" class="validate" required="">
            <label for="confirm_password">Confirm Password*</label>
        </div>
        <button type="submit" name="submit" class="btn waves-effect waves-light btn waves-effect waves-light--primary">Save</button
    <?php
    return str_replace(SSV_Users::REGISTER_FIELDS_TAG, ob_get_clean(), $content);
}
