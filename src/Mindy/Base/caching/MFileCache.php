<?php
/**
 *
 * CoreFileCache class file.
 *
 * @author Falaleev Maxim <max@studio107.com>
 * @link http://studio107.ru/
 * @copyright Copyright &copy; 2010-2012 Studio107
 * @license http://www.cms107.com/license/
 * @package modules.core.components
 * @since 1.1.1
 * @version 1.0
 *
 */
class MFileCache extends CFileCache
{
    public $directoryLevel=0;

    private $_gcProbability=100;
    private $_gced=false;

	protected function getValue($key)
	{
		$cacheFile=$this->getCacheFile($key);
		if(($time=@filemtime($cacheFile))<time())
			return @file_get_contents($cacheFile);
		else if($time>0)
			@unlink($cacheFile);
		return false;
	}

	protected function setValue($key,$value,$expire)
	{
		if(!$this->_gced && mt_rand(0,1000000)<$this->_gcProbability) {
			$this->gc();
			$this->_gced=true;
		}

		$cacheFile=$this->getCacheFile($key);
		if($this->directoryLevel>0)
			@mkdir(dirname($cacheFile),0777,true);
		if(@file_put_contents($cacheFile,$value,LOCK_EX)!==false) {
			@chmod($cacheFile,0777);
            return true;
		} else
			return false;
	}
}
