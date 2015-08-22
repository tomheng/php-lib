<?php
/**
 * 锁服务(用Memcache模拟锁)
 * Author: tomheng
 * gist: https://gist.github.com/tomheng/6149779
 */

class MemcacheLock{

    private $mc = null;
    private $key_prefix = "memcache_lock_service_key_";
    private $all_lock_names = array();
    private $expiration = 60; //one min
    private $max_block_time = 15; //最长的阻塞时间
    /**
     * [__construct description]
     */
    public function __construct(){
        if(function_exists('memcache_init')){
            $this->mc = memcache_connect('memcache_host', 11211);
        }
    }

    /**
     * [get_key description]
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    private function get_key($name){
        $key = $this->key_prefix.$name;
        return $key;
    }

    /**
     * 捕获锁
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public  function begin($name, $block = true)
    {
        if(!$this->mc || !$name){
            return false;
        }
        $max_block_time = $this->max_block_time;
        $key = $this->get_key($name);
        do{ 
            $re = memcache_add($this->mc, $key, 1, false, $this->expiration);
            if($re == true){
                $this->all_lock_names[$name] = 1;
                //$this->debug();
                break;
            }else{
                //dolog('Lock failed '.$name);
            }
            //echo '#'.PHP_EOL;
            //sleep(1);
        }while($block && $max_block_time-- && !sleep(1));
        return $re;
    }

    /**
     * 释放锁
     */
    public function release($name){
        if(!$this->mc || !$name){
            return false;
        }
        $key = $this->get_key($name);
        $re = memcache_delete($this->mc, $key);
        if($re == true){
            unset($this->all_lock_names[$name]);
        }
        return $re;
    }

    /**
     * 释放所有的锁
     */
    public function __destruct(){
        if(!$this->mc){
            return false;
        }
        foreach ($this->all_lock_names as $name => $value) {
            # code...
            $this->release($name);
        }
    }

    /**
     * 调试
     * @return [type] [description]
     */
    public function debug(){
        var_dump($this->all_lock_names);
        foreach ($this->all_lock_names as $name => $value) {
            $key = $this->get_key($name);
            if($this->mc){
                $value = memcache_get($this->mc, $key);
            }else{
                $value = "no such lock ";
            }
            echo "Lock name:$key, value:{$value}".PHP_EOL;
        }
    }
}