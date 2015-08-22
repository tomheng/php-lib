<?php
/**
* 锁服务(用File模拟锁)
* Author: tomheng
* @date 2015-08-22
*
*/

class FileLock {
	
	private $all_lock_names = array();
	private $lock_dir = "/tmp";
	
	public function __construct($lock_dir = 0){
		if($lock_dir){
			$this->$lock_dir = $lock_dir;
		}
		if(!file_exists($this->lock_dir)){
			mkdir($this->lock_dir, 0777, true);
		}
	}
	
	private function getLockFile($name){
		$name = trim($name);
		if(!$name){
			throw new Exception("lock name should not empty");
		}
		return $lock_file = "{$this->lock_dir}/$name.php.lock";
	}
	
    /**
     * 捕获锁
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function begin($name, $block = false) {
		$lock_file = $this->getLockFile($name);
		$fp = fopen($lock_file, "w+");
		$this->all_lock_names[] = $name;
		$opt = LOCK_EX;
		if($block){
			$opt |= LOCK_NB;
		}
		if (!flock($fp, $opt)) {  // acquire an exclusive lock
		    throw new Exception("Couldn't get the lock $lock_file !\n");
		}
		return true;
    }
	
    /**
     * 释放锁
     */
    public function release($name){
        unlink($this->getLockFile($name));
    }
	
    /**
     * 释放所有的锁
     */
    public function __destruct(){
        foreach ($this->all_lock_names as $name) {
            # code...
            $this->release($name);
        }
    }
}