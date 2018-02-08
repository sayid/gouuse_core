<?php

namespace GouuseCore\Helpers;

class FileInfoHelper
{
	public static function mimeContentType($filename) {
		
		$mime_types = array(
				
				'txt' => 'text/plain',
				'htm' => 'text/html',
				'html' => 'text/html',
				'php' => 'text/html',
				'css' => 'text/css',
				'js' => 'application/javascript',
				'json' => 'application/json',
				'xml' => 'application/xml',
				'swf' => 'application/x-shockwave-flash',
				'flv' => 'video/x-flv',
				
				// images
				'png' => 'image/png',
				'jpe' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg' => 'image/jpeg',
				'gif' => 'image/gif',
				'bmp' => 'image/bmp',
				'ico' => 'image/vnd.microsoft.icon',
				'tiff' => 'image/tiff',
				'tif' => 'image/tiff',
				'svg' => 'image/svg+xml',
				'svgz' => 'image/svg+xml',
				
				// archives
				'zip' => 'application/zip',
				'rar' => 'application/x-rar-compressed',
				'exe' => 'application/x-msdownload',
				'msi' => 'application/x-msdownload',
				'cab' => 'application/vnd.ms-cab-compressed',
				
				// audio/video
				'mp3' => 'audio/mpeg',
				'qt' => 'video/quicktime',
				'mov' => 'video/quicktime',
				
				// adobe
				'pdf' => 'application/pdf',
				'psd' => 'image/vnd.adobe.photoshop',
				'ai' => 'application/postscript',
				'eps' => 'application/postscript',
				'ps' => 'application/postscript',
				
				// ms office
				'doc' => 'application/msword',
				'rtf' => 'application/rtf',
				'xls' => 'application/vnd.ms-excel',
				'ppt' => 'application/vnd.ms-powerpoint',
				
				// open office
				'odt' => 'application/vnd.oasis.opendocument.text',
				'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);
		$exts = explode('.', $filename);
		$ext = strtolower(end($exts));
		if (array_key_exists($ext, $mime_types)) {
			return $mime_types[$ext];
		} elseif (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		} else {
			return 'application/octet-stream';
		}
	}
	
	/**
	 * 二进制文件获取后缀
	 */
	public static function getBinaryFileSuffix($type_code)
	{
	    switch ($type_code) {
	        case 7790:
	            $file_type = 'exe';
	            break;
	        case 7784:
	            $file_type = 'midi';
	            break;
	        case 8075:
	            $file_type = 'zip';
	            break;
	        case 8297:
	            $file_type = 'rar';
	            break;
	        case 255216:
	            $file_type = 'jpg';
	            break;
	        case 7173:
	            $file_type = 'gif';
	            break;
	        case 6677:
	            $file_type = 'bmp';
	            break;
	        case 13780:
	            $file_type = 'png';
	            break;
	        default:
	            $file_type = 'unknown';
	            break;
	    }
	    return $file_type;
	}
	
	/**
	 * 文件压缩
	 * @param unknown $path压缩文件路径
	 * @param unknown $zip 实例化zip
	 * @param unknown $zip_name 文件压缩路径+名称
	 * @return unknown
	 */
	public static function addFileToZip($path, $zip, $zip_name)
	{
	    $handler=opendir($path); //打开当前文件夹由$path指定。
	    $zip->open($zip_name,\ZipArchive::CREATE);   //打开压缩包.zip
	    while(($filename=readdir($handler))!==false){
	        if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
	            if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
	                addFileToZip($path."/".$filename, $zip);
	            }else{ //将文件加入zip对象
	                //$zip->addFile($path."/".$filename);
	                $zip->addFromString($filename, $zip_name);
	            }
	        }
	    }
	    $zip->close();
	    @closedir($path);
	    return $zip_name;
	}
}
