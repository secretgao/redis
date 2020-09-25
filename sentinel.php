<?php
/* redis缓存类
*/
class Cache_Redis {

    private static $_instance = null;
    private $_iRedis = null;

    //获取连接对象
    protected function _getRedis(){
        return $this->_iRedis->_getRedis();
    }

    public function __construct() {
        //初始化配置信息
        $this->_iRedis = iRedis::getInstance();
    }

    /**
     * 获取全局唯一的Redis缓存实例
     * @return Cache_Redis
     */
    public static function getInstance() {
        if (! isset(self::$_instance)) {
            self::$_instance = new Cache_Redis();
        }
        return self::$_instance;
    }
    public function set($key,$value){
        return $this->_getRedis()->set($key,$value);
    }
    public function get($key){
        return  $this->_getRedis()->get($key);
    }
}
//Cache_Redis底层类，增加方法的重试机制
class iRedis {

    protected static $_redis = array();
    protected static $_instance = null;
    protected $errors = 0; //错误次数
    protected $max_errors = 10; //同一连接上错误次数超过多少次就重新连接，预防先建立连接然后循环操作的情况

    public  $ip;
    public  $port;
    protected function __construct() {

    }

   

    public static function getInstance(){
        if(!isset(self::$_instance)){
            self::$_instance = new iRedis();
        }
        return self::$_instance;
    }

    //连接redis获取对象
    public function _getRedis(){

        if(isset(self::$_redis)){
            self::$_redis = $this->_connect();
        }else{
            self::$_redis = false;
        }
        return $this;
    }

    //连接redis
    protected function _connect() {

        $redis = new Redis();
        // 连接redis-sentinel 服务器
        $redis->connect('127.0.0.1',26379);
        //通过redis-sentinel服务器获取到当前监控的 redis-master
        $master_info = $redis->rawCommand("sentinel","get-master-addr-by-name",'mymaster');

        $this->ip = $master_info[0];
        $this->port = $master_info[1];

        echo "当前master-ip;".$this->ip."port:".$this->port.PHP_EOL;
        try{
            $redis = new Redis();
            $res = $redis->connect($this->ip, $this->port,4);

            if(!$res){
                if($this->handleErrors()) {
                    echo (sprintf('redis TIME=%s FILE=%s LINE=%s MESSAGE=%s SERVER=%s', date('Y-m-d H:i:s'),
                            __FILE__, __LINE__, 'connect error', $this->ip.':'.$this->port)).PHP_EOL;
                }
                return false;
            }
            $this->start_time = time();
            return $redis;
        }catch(Exception $e){
            if($this->handleErrors()) {
                echo (sprintf('redis TIME=%s FILE=%s LINE=%s MESSAGE=%s SERVER=%s', date('Y-m-d H:i:s'),
                        __FILE__, __LINE__, 'connect error', $this->ip.':'.$this->port)).PHP_EOL;
            }
        }
        return false;

    }

    //真正的redis方法
    public function __call($method_name, $param_arr){

        try{
            $interval = time()-$this->start_time;
            //命令距离第一次连接上服务端间隔20s则重新连接
            if($interval>20){
                self::$_redis = $this->_connect();
            }
            return call_user_func_array(array(self::$_redis, $method_name), $param_arr);
        }catch(RedisException $e){

            if($this->handleErrors()) {
                echo (sprintf('redis TIME=%s FILE=%s LINE=%s MESSAGE=%s METHOD=%s SERVER=%s', date('Y-m-d H:i:s'),
                        __FILE__, __LINE__, $e->getMessage(), $method_name, $this->ip.':'.$this->port)).PHP_EOL;
            }

        }
        $this->errors++;
        return false;

    }

    //针对某些循环操作，记录执行次数，避免频繁报错
    private function handleErrors(){
        if($this->errors > $this->max_errors){
            $this->errors = 0;
            sleep(2);
            return false;
        }
        return true;
    }

}


for($i=0;$i<10000;$i++){
    sleep(2);
    $set_res = Cache_Redis::getInstance()->set($i,$i);
    $get = Cache_Redis::getInstance()->get($i);
    echo "当前循环次数：".$i." set结果：".$set_res.' get结果：'.$get.PHP_EOL;
 }
