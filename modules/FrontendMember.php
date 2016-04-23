<?php
/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 14:48
 */

namespace modules;


class FrontendMember extends \WP_User
{
	/**
	 * FrontendMember constructor.
	 *
	 * @param \WP_User $user the WP_User component used as base for the FrontendMember
	 */
	function __construct($user)
	{
		parent::__construct($user);
	}

	/**
	 * This function returns the metadata associated with the given key (or an alias of that key).
	 * The aliases are:
	 *  - email, email_address, member_email => user_email
	 *  - name => display_name
	 *  - login, username, user_name => user_login
	 * If the key contains "_role" this function will return if the FrontendMember is part of that role.
	 *
	 * @param string $meta_key defines which metadata should be returned.
	 * @param bool   $single   defines if it should return a single value or an array of values. Default it will return
	 *                         a single value.
	 *
	 * @return string the value associated with the key.
	 */
	function get_meta($meta_key, $single = true)
	{
		if ($meta_key == "email" || $meta_key == "email_address" || $meta_key == "user_email" || $meta_key == "member_email") {
			return $this->user_email;
		} else if ($meta_key == "name" || $meta_key == "display_name") {
			return $this->display_name;
		} else if ($meta_key == "login" || $meta_key == "username" || $meta_key == "user_name" || $meta_key == "user_login") {
			return $this->user_login;
		} else if (strpos($meta_key, "_role") !== false) {
			return in_array(str_replace("_role", "", $meta_key), $this->roles);
		} else {
			return $this->get_meta($meta_key, $single);
		}
	}

	/**
	 * This function sets the metadata defined by the key (or an alias of that key).
	 * The aliases are:
	 *  - email, email_address, member_email => user_email
	 *  - name => display_name
	 *  - login, username, user_name => user_login
	 * If the key contains "_role" or "_role_select" this function will also add, remove or change the role.
	 *
	 * @param string $meta_key the key that defines which metadata to set.
	 * @param string $value    the value to set.
	 *
	 * @return bool is only false if the key is user_login (or an alias).
	 */
	function update_meta($meta_key, $value)
	{
		if ($meta_key == "email" || $meta_key == "email_address" || $meta_key == "user_email" || $meta_key == "member_email") {
			$this->update_meta('user_email', $value);

			return true;
		} else if ($meta_key == "name" || $meta_key == "display_name") {
			$this->update_meta('display_name', $value);

			return true;
		} else if ($meta_key == "login" || $meta_key == "username" || $meta_key == "user_name" || $meta_key == "user_login") {
			return false; //cannot change user_login
		} else if (strpos($meta_key, "_role_select") !== false) {
			$old_role = $this->get_meta(str_replace("_role_select", "", $meta_key), true);
			$this->remove_role($old_role);
			$this->add_role($value);
		} else if (strpos($meta_key, "_role") !== false) {
			$role = str_replace("_role", "", $meta_key);
			if ($value == "yes") {
				$this->add_role($role);
			} else {
				$this->remove_role($role);
			}

			return true;
		} else {
			$this->update_meta($meta_key, $value);

			return true;
		}
	}
}