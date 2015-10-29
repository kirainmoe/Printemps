<?php
/**
 * Printemps Framework 文件处理类
 * 处理文件上次Action / 文件读写增删操作
 * (C)2015 Printemps Framework All rights reserved.
 */
class PriFile{
	public function upload(){
		isset($_FILES) ? global $_FILES : throw new Exception("未检测到上传的文件哟。", 2330);
	}
}