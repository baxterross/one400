<?php

/**
 * Converts $value to an absolute integer
 * @param mixed $value
 * @return integer
 */
function wlm_abs_int($value) {
	return abs((int) $value);
}

/**
 * adds a metadata to the user levels
 * note: right now only supports adding is_latest_registration
 * @param array user_levels
 * @param meta_name is_latest_registration
 *
 * Metadata implementations
 * is_latest_registration - if the current level is the latest level
 * the user has registered in, that level will have $obj->is_lastest_registration = 1
 * 
 *
 */
function wlm_add_metadata(&$user_levels, $meta_name = 'is_latest_registration') {
	if ($meta_name = 'is_latest_registration') {
		$idx = 0;
		$ref_ts = 0;
		foreach ($user_levels as $i => $item) {
			$item->is_latest_registration = 0;
			if ($item->Timestamp > $ref_ts) {
				$idx = $i;
				$ref_tx = $item->Timestamp;
			}
		}
		$user_levels[$idx]->is_latest_registration = 1;
		//break early please
		return;
	}
}

function wlm_print_r() {
	echo '<pre style="font-size:small">';
	call_user_func_array('print_r', func_get_args());
	echo '</pre>';
}

function wlm_diff_microtime($mt_old, $mt_new = '') {
	if (empty($mt_new)) {
		$mt_new = microtime();
	}
	list($old_usec, $old_sec) = explode(' ', $mt_old);
	list($new_usec, $new_sec) = explode(' ', $mt_new);
	$old_mt = ((float) $old_usec + (float) $old_sec);
	$new_mt = ((float) $new_usec + (float) $new_sec);
	return number_format($new_mt - $old_mt, 32);
}

function wlm_debugout($text) {
	static $filename;

	if (!$filename) {
		$filename = realpath(sys_get_temp_dir()) . '/wlmdebug_' . date('YMd');
	}

	$text = trim($text) . "\n";

	$f = fopen($filename, 'a');
	fwrite($f, $text);
	fclose($f);
}

/**
 * Dissects the form part of a custom registration form
 * and returns an array of dissected field entries
 * @param string $custom_registration_form_data
 * @return array
 */
function wlm_dissect_custom_registration_form($custom_registration_form_data) {

	function fetch_label($string) {
		if (preg_match('#<td class="label".*?>(.*?)</td>#', $string, $match)) {
			return $match[1];
		} else {
			return false;
		}
	}

	function fetch_desc($string) {
		if (preg_match('#<div class="desc".*?>(.*?)</div></td>#', $string, $match)) {
			return $match[1];
		} else {
			return false;
		}
	}

	function fetch_attributes($tag, $string) {
		preg_match('#<' . $tag . '.+?>#', $string, $match);
		preg_match_all('# (.+?)="([^"]*?)"#', $match[0], $matches);
		$attrs = array_combine($matches[1], $matches[2]);
		unset($attrs['class']);
		unset($attrs['id']);
		return $attrs;
	}

	function fetch_options($type, $string) {
		switch ($type) {
			case 'checkbox':
			case 'radio':
				preg_match_all('#<label[^>]*?><input.+?value="([^"]*?)"[^>]*?>(.*?)</label>#', $string, $matches);
				$options = array();
				for ($i = 0; $i < count($matches[0]); $i++) {
					$option = array(
						'value' => $matches[1][$i],
						'text' => $matches[2][$i],
						'checked' => (int) preg_match('#checked="checked"#', $matches[0][$i])
					);
					$options[] = $option;
				}
				return $options;
				break;
			case 'select':
				preg_match_all('#<option value="([^"]*?)".*?>(.*?)</option>#', $string, $matches);
				$options = array();
				for ($i = 0; $i < count($matches[0]); $i++) {
					$option = array(
						'value' => $matches[1][$i],
						'text' => $matches[2][$i],
						'selected' => (int) preg_match('#selected="selected"#', $matches[0][$i])
					);
					$options[] = $option;
				}
				return $options;
				break;
		}

		return false;
	}

	$form = maybe_unserialize($custom_registration_form_data);

	$form_data = $form['form'];

	preg_match_all('#<tr class="(.*?li_(fld|submit).*?)".*?>(.+?)</tr>#is', $form_data, $fields);

	$field_types = $fields[1];
	$fields = $fields[3];

	foreach ($fields AS $key => $value) {
		$fields[$key] = array('fields' => $value, 'types' => explode(' ', $field_types[$key]));

		if (in_array('required', $fields[$key]['types'])) {
			$fields[$key]['required'] = 1;
		}
		if (in_array('systemFld', $fields[$key]['types'])) {
			$fields[$key]['required'] = 1;
			$fields[$key]['system_field'] = 1;
		}
		if (in_array('wp_field', $fields[$key]['types'])) {
			$fields[$key]['wp_field'] = 1;
		}

		$fields[$key]['description'] = fetch_desc($fields[$key]['fields']);

		if (in_array('field_special_paragraph', $fields[$key]['types'])) {
			$fields[$key]['type'] = 'paragraph';
			$fields[$key]['text'] = $fields[$key]['description'];
			unset($fields[$key]['description']);
		} elseif (in_array('field_special_header', $fields[$key]['types'])) {
			$fields[$key]['type'] = 'header';
			$fields[$key]['text'] = fetch_label($fields[$key]['fields']);
		} elseif (in_array('field_tos', $fields[$key]['types'])) {
			$fields[$key]['attributes'] = fetch_attributes('input', $fields[$key]['fields']);
			unset($fields[$key]['attributes']['value']);
			unset($fields[$key]['attributes']['checked']);
			$options = fetch_options('checkbox', $fields[$key]['fields']);
			$fields[$key]['text'] = preg_replace('#<[/]{0,1}a.*?>#', '', html_entity_decode($options[0]['value']));
			$fields[$key]['type'] = 'tos';
			$fields[$key]['required'] = 1;
			$fields[$key]['lightbox'] = (int) in_array('lightbox_tos', $fields[$key]['types']);
		} elseif (in_array('field_radio', $fields[$key]['types'])) {
			$fields[$key]['attributes'] = fetch_attributes('input', $fields[$key]['fields']);
			unset($fields[$key]['attributes']['checked']);
			unset($fields[$key]['attributes']['value']);
			$fields[$key]['options'] = fetch_options('radio', $fields[$key]['fields']);
			$fields[$key]['type'] = 'radio';
			$fields[$key]['label'] = fetch_label($fields[$key]['fields']);
		} elseif (in_array('field_checkbox', $fields[$key]['types'])) {
			$fields[$key]['attributes'] = fetch_attributes('input', $fields[$key]['fields']);
			unset($fields[$key]['attributes']['checked']);
			unset($fields[$key]['attributes']['value']);
			$fields[$key]['options'] = fetch_options('checkbox', $fields[$key]['fields']);
			$fields[$key]['type'] = 'checkbox';
			$fields[$key]['label'] = fetch_label($fields[$key]['fields']);
		} elseif (in_array('field_select', $fields[$key]['types'])) {
			$fields[$key]['attributes'] = fetch_attributes('select', $fields[$key]['fields']);
			$fields[$key]['options'] = fetch_options('select', $fields[$key]['fields']);
			$fields[$key]['type'] = 'select';
			$fields[$key]['label'] = fetch_label($fields[$key]['fields']);
		} elseif (in_array('field_textarea', $fields[$key]['types']) OR in_array('field_wp_biography', $fields[$key]['types'])) {
			$fields[$key]['attributes'] = fetch_attributes('textarea', $fields[$key]['fields']);
			preg_match('#<textarea.+?>(.*?)</textarea>#', $fields[$key]['fields'], $match);
			$fields[$key]['attributes']['value'] = $match[1];
			$fields[$key]['type'] = 'textarea';
			$fields[$key]['label'] = fetch_label($fields[$key]['fields']);
		} elseif (in_array('field_hidden', $fields[$key]['types'])) {
			$fields[$key]['attributes'] = fetch_attributes('input', $fields[$key]['fields']);
			$fields[$key]['type'] = 'hidden';
		} elseif (in_array('li_submit', $fields[$key]['types'])) {
			preg_match('#<input .+?value="(.+?)".*?>#', $fields[$key]['fields'], $match);
			$submit_label = $match[1];
			unset($fields[$key]);
		} else {
			$fields[$key]['attributes'] = fetch_attributes('input', $fields[$key]['fields']);
			$fields[$key]['type'] = 'input';
			$fields[$key]['label'] = fetch_label($fields[$key]['fields']);
		}

		unset($fields[$key]['fields']);
		unset($fields[$key]['types']);
	}

	ksort($fields);
	$fields = array('fields' => $fields, 'submit' => $submit_label);

	return $fields;
}

/**
 * Checks if the requested array index is set and returns its value
 * @param array $array_or_object
 * @param string|number $index
 * @return mixed
 */
function wlm_arrval($array_or_object, $index) {
	if (is_array($array_or_object) && isset($array_or_object[$index])) {
		return $array_or_object[$index];
	}
	if (is_object($array_or_object) && isset($array_or_object->$index)) {
		return $array_or_object->$index;
	}
	return;
}

/**
 * Function to correctly interpret boolean representations
 * - interprets false, 0, n and no as FALSE
 * - interprets true, 1, y and yes as TRUE
 * 
 * @param mixed $value representation to interpret
 * @param type $no_match_value value to return if representation does not match any of the expected representations
 * @return boolean|$no_match_value
 */
function wlm_boolean_value($value, $no_match_value = false) {
	$value = trim(strtolower($value));
	if(in_array($value,array(false, 0, 'false','0','n','no'),true)){
		return false;
	}
	if(in_array($value,array(true, 1, 'true','1','y','yes'),true)){
		return true;
	}
	return $no_match_value;
}

function wlm_admin_in_admin() {
	return (current_user_can('administrator') && is_admin());
}

if (!function_exists('sys_get_temp_dir')) {

	function sys_get_temp_dir() {
		if ($temp = getenv('TMP'))
			return $temp;
		if ($temp = getenv('TEMP'))
			return $temp;
		if ($temp = getenv('TMPDIR'))
			return $temp;
		$temp = tempnam(__FILE__, '');
		if (file_exists($temp)) {
			unlink($temp);
			return dirname($temp);
		}
		return null;
	}

}

/**
 * Calls the WishList Member API 2 Internally
 * @param type $request (i.e. "/levels");
 * @param type $method (GET, POST, PUT, DELETE)
 * @param type $data (optional) Associate array of data to pass
 * @return type array WishList Member API2 Result
 */
function WishListMemberAPIRequest($request, $method = 'GET', $data = null) {
	require_once('API2.php');
	$api = new WLMAPI2($request, strtoupper($method), $data);
	return $api->result;
}

?>
