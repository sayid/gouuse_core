<?php
namespace GouuseCore\Libraries;

/**
 * 加密解密类
 * @author zhangyubo
 *
 */
class EncryptLib
{
	const METHOD = 'aes-256-cbc';

	private $iv = '00000000000000000000000000000000';

	public  function encrypt($message, $key = '')
	{
		if (empty($key)) {
			$key = env('AES_KEY');
		}
		$key = hash('sha256', $key, true);
		if (mb_strlen($key, '8bit') !== 32) {
			throw new Exception("Needs a 256-bit key!");
		}
		$iv = $this->hexToStr($this->iv);

		//增加补位
		$block = 16;
		$pad = $block - (strlen($message) % $block);
		$message .= str_repeat(chr($pad), $pad);
		
		$ciphertext = openssl_encrypt(
				$message,
				self::METHOD,
				$key,
				OPENSSL_RAW_DATA,
				$iv
				);
		return base64_encode($ciphertext);
	}

	public  function decrypt($message, $key = '')
	{
		if (empty($key)) {
			$key = env('AES_KEY');
		}
		$key = hash('sha256', $key, true);
		if (mb_strlen($key, '8bit') !== 32) {
			throw new Exception("Needs a 256-bit key!");
		}
		$message = base64_decode($message);

		$iv = $this->hexToStr($this->iv);

		return openssl_decrypt(
				$message,
				self::METHOD,
				$key,
				OPENSSL_RAW_DATA,
				$iv
				);
	}

	function hexToStr($hex)
	{
		$string='';
		for ($i=0; $i < strlen($hex)-1; $i+=2)
		{
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		}
		return $string;
	}

}
