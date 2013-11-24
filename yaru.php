<?php
class yaruapi {	
	var $client_id;
	var $client_secret;
	var $uri;
	var $code;
	var $response;
	var $token;
	var $result;

	function postKeys($url, $peremen, $headers) {
		$post_arr = array();
		foreach ($peremen as $key => $value) {
			$post_arr[] = $key."=".$value;
		}
		$data = implode('&', $post_arr);
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
		curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
		$this->response = curl_exec($handle);
		$this-> code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	}
		
	function yaruapi($client_id, $client_secret, $uri) {
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->uri = $uri;
		$this->postKeys ("https://oauth.yandex.ru/token",
			array(
				'grant_type' => 'authorization_code',
				'code' => $_GET["code"],
				'client_id'=> $this->client_id,
				'client_secret' => $this->client_secret
			),
			array('Content-type: application/x-www-form-urlencoded')
		);		
		if ($this->code == 200) {
			$this->response = json_decode($this->response, true);
			$this->token = $this->response["access_token"];
			echo $this->token;
		}
		else {
			echo "Error: ".$this->code;
		}
	}

	function comment($text, $num) {
		$xml = <<<XML
		<entry xmlns="http://www.w3.org/2005/Atom">
		<title>sps</title>
		<content type="text"><![CDATA[$text]]></content>
		<category scheme="urn:ya.ru:posttypes" term="text"/>
		</entry>
XML;
		$komment = curl_init();
		curl_setopt($komment, CURLOPT_URL, 'http://api-yaru.yandex.ru/person/'.$this->uri.'/post/'.$num.'/comment/?oauth_token='.$this->token);
		curl_setopt($komment, CURLOPT_HTTPHEADER, array('Content-type: application/atom+xml; type=entry'));
		curl_setopt($komment, CURLOPT_POST, 1);
		curl_setopt($komment, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($komment, CURLOPT_RETURNTRANSFER, true);
		$jsonk = curl_exec($komment);
	}
	
	function status($tex) {
		$xmls = <<<XML
		<entry xmlns="http://www.w3.org/2005/Atom" xmlns:y="http://api.yandex.ru/yaru/">
		<category scheme="urn:ya.ru:posttypes" term="status"/>
		<content>$tex</content>
		<y:comments_disabled/>
		</entry>
XML;
		$status = curl_init();
		curl_setopt($status, CURLOPT_URL, 'http://api-yaru.yandex.ru/person/'.$this->uri.'/post/?oauth_token='.$this->token);
		curl_setopt($status, CURLOPT_HTTPHEADER, array('Content-type: application/atom+xml; type=entry; charset=utf-8;'));
		curl_setopt($status, CURLOPT_POST, 1);
		curl_setopt($status, CURLOPT_POSTFIELDS, $xmls);
		curl_setopt($status, CURLOPT_RETURNTRANSFER, true);
		$jsons = curl_exec($status);
	}

	function post($title, $main){
		$xmlp = <<<XML
		<entry xmlns="http://www.w3.org/2005/Atom">
		<title>$title</title>
		<content type="text"><![CDATA[$main]]></content>
		<category scheme="urn:ya.ru:posttypes" term="text"/>
		</entry>
XML;
		$post = curl_init();
		curl_setopt($post, CURLOPT_URL, 'http://api-yaru.yandex.ru/person/'.$this->uri.'/post/?oauth_token='.$this->token);
		curl_setopt($post, CURLOPT_HTTPHEADER, array('Content-type: application/atom+xml; type=entry; charset=utf-8;'));
		curl_setopt($post, CURLOPT_POST, 1);
		curl_setopt($post, CURLOPT_POSTFIELDS, $xmlp);
		curl_setopt($post, CURLOPT_RETURNTRANSFER, true);
		$jsonp = curl_exec($post);
	}
};
?>