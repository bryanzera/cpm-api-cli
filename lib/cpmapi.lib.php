<?php

	// supported nouns and their CMS path counterparts
	$supported_nouns = array(
		'story' => 'stories',
		'audio' => 'audio',
		'show' => 'shows'
	);

	// supported verbs and their HTTP method counterparts
	$supported_verbs = array(
		'create' => 'POST',
		'update' => 'PUT'
	);

	function send_to_cms($noun, $verb, $object) {

		global $supported_nouns;
		global $supported_verbs;

		$token = get_cms_token();
		$request_body = json_encode($object, JSON_UNESCAPED_SLASHES);
		$cms_path = $supported_nouns[$noun];
		$method = $supported_verbs[$verb];
		$headers = array(
			"Authorization: Bearer $token",
			"Content-Type: application/json",
			"Content-Length: " . strlen($request_body)
		);

		$request = curl_init();
		curl_setopt_array($request, array(
			CURLOPT_URL => CPMAPI_ENDPOINT . $cms_path . '/',
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => $request_body,
			CURLOPT_HEADER => true
		));

		$return_headers = curl_exec($request);
		$info = curl_getinfo($request);

		if ($info['http_code'] == 201) {

			// SUCCESS
			foreach (explode("\r\n", $return_headers) as $h) {
				$matches = array();
				if (preg_match('/^Location: (.*)$/', $h, $matches)) {
					$g = explode('/', $matches[1]);
					print $g[count($g)-1];
					exit(0);
				}
			}

		} else {

			// FAIL
			list($gbg, $error_json)= explode("\r\n\r\n", $return_headers);
			$error_object = json_decode($error_json);
			print "ERROR: send_to_cms(): " . $error_object->message;
			exit(1);

		}


	}

	function get_cms_token() {

		$url = CPMAPI_ENDPOINT . 'tokens/';

		$request = curl_init();

		$fields = 'grant_type=password&scope=default&username=' .
			CPMAPI_CMS_USER . '&password=' . CPMAPI_CMS_PASSWORD;

		$headers = array(
			'Content-Type: application/x-www-form-urlencoded'
		);

		curl_setopt_array($request, array(
			CURLOPT_URL => $url,
			CURLOPT_POST => true,
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_USERPWD => 'client:secret',
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POSTFIELDS => $fields,
			CURLOPT_RETURNTRANSFER => true
		));

		$result = json_decode(curl_exec($request));

		if (isset($result->access_token)) {
			return $result->access_token;
		} else if (isset($result->error)) {
			exit($result->error . " : " . $result->error_description);
		} else {
			exit("No token or error message returned");
		}

	}

	function update_cms_object(&$obj, $path, $value) {

		$path = explode(':', $path);
		$php_object_code = '$obj';
		$last_object_type = $new_object_type = null;

		for ($q = 0; $q < count($path); $q++) {

			$p = $path[$q];
			$path_set = false;

			// if the next path section exists and is numeric, this
			// is an array.  otherwise, it's an object
			if (isset($path[$q+1]) && is_numeric($path[$q+1])) {
				$new_object_type = 'array()';
			} else {
				$new_object_type = 'new stdClass()';
			}

			// if the current path section is numeric, use array
			// notation.  otherwise, use object notation to add to
			// the php object code.
			if (is_numeric($path[$q])) {
				$php_object_code .= '[' . $p . ']';
			} else {
				$php_object_code .= '->' . $p;
			}

			// determine if  the current $php_object_code has
			// been set already and create it if not.
			eval("\$path_set = isset($php_object_code);");

			if (!$path_set) {
				eval("$php_object_code = $new_object_type;");
			}

		}

		// now that the complete object code is created,
		// assign the incoming value to it.
		$value = preg_replace('/"/', '\"', $value);
		eval("$php_object_code = \"\$value\";");

	}

	function get_cms_path($noun) {
		global $supported_nouns;
		return (isset($supported_nouns[$noun]) ? $supported_nouns[$noun] : false);
	}

	function get_http_method($verb) {
		global $supported_verbs;
		return (isset($supported_verbs[$verb]) ? $supported_verbs[$verb] : false);
	}

	function send_to_slack($message) {

		print "SEND TO SLACK";

		$request = curl_init();

		$form_data = array(
			'token' => CPMAPI_NOTIFICATION_TOKEN,
			'slackOnly' => 1,
			'recipients' => '5592731453',
			'message' => $message
		);

		$headers = array(
			"Content-Type: application/x-www-form-urlencoded"
		);

		curl_setopt_array($request, array(
			CURLOPT_URL => 'http://admintools.wbez.org/sms/',
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => http_build_query($form_data)
		));

		curl_exec($request);

	}

?>
