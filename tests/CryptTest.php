<?php
use GouuseCore\Libraries\EncryptLib;


class CryptTest extends TestCase
{
	
	function EnTest() {
		$data = "hello world";
		$lib = new EncryptLib();
		$key = 'it_is_my_key_to_encrypt_or_decrypt';
		$en = $lib->encrypt($data, $key);
		var_dump($en);
		$de = $lib->decrypt($en, $key);
		var_dump($de);
	}
}