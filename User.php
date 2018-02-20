<?php
require "ErrorCode.php";
header("Content-Type:text/html;charset=UTF-8");
class User 
{
    /**
     * 数据库连接句柄
     */
    private $_db;
    
    public function __construct($_db) //构造方法传入，而不是require进来
    {
        $this ->_db = $_db;                                     
    }
    /**
     * 用户登录
     */
    public function login($username,$password)
    {
        if(empty($username)){
            throw new Exception("用户名不能为空",ErrorCode::USERNAME_CANNNOT_EMPTY);
        }
        if(empty($password)){
            throw new Exception('密码不能为空',ErrorCode::PASSWORD_CANNOT_EMPTY);
        }
        $sql = 'SELECT * FROM `zhuche` WHERE `username` =:username AND `password` =:password';
        $stmt = $this -> _db ->prepare($sql);
        $password = $this->_md5($password);
        $stmt -> bindParam(':username',$username);
        $stmt -> bindParam(':password',$password);
        $stmt -> execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if(empty($user)){
            throw new Exception('用户名或密码错误',ErrorCode::USERNAME_OR_PASSWORD_INVALID);
        }
        unset($user['password']); //删除这个数组中的value=password的key
        return $user;
    }
    /**
     * 注册
     */
    public function register($username,$password,$email,$name,$intro,$sex)
    {
        if(empty($username)){
            throw new Exception("用户名不能为空",ErrorCode::USERNAME_CANNNOT_EMPTY);
        }
        if($this -> _isUsernameExists($username)){
            throw new Exception("用户名已存在",ErrorCode::USERNAME_EXISTS);
        }
        if(empty($password)){
            throw new Exception('密码不能为空',ErrorCode::PASSWORD_CANNOT_EMPTY);
        }
        $sql = 'INSERT INTO `zhuche`(`username`,`password`,`name`,`email`,`intro`,`sex`) VALUES (:username,:password,:name,:email,:intro,:sex)';
        $stmt = $this->_db->prepare($sql);     //要有全部的变量
        $password = $this->_md5($password);
        $stmt -> bindParam(':username',$username);//该函数绑定了 SQL 的参数，且告诉数据库参数的值
        $stmt -> bindParam(':password',$password); 
        $stmt -> bindParam(':name',$name);
        $stmt -> bindParam(':email',$email);
        $stmt -> bindParam(':intro',$intro);
         $stmt -> bindParam(':sex',$sex);
        if(!$stmt->execute()){
            throw new Exception('注册失败',ErrorCode::REGISTER_FAIL);
        }
        return [
            'userId' => $this -> _db->lastInsertId(), //这句话可以少调用一次数据库而直接获得上次操作的数据
            'username' => $username,
            'name' => $name
        ];
    }
    /**
     * md5加密
     */
    public function _md5($string , $key ='XDU'){ //把md5加密改一改
        return md5($string.$key);
    }
    /**
     * 检测用户名是否存在
     */
    private function _isUsernameExists($username)
    {
        $sql = 'SELECT * FROM `zhuche` WHERE `username` = :username';     //这里：是占位符
        $stmt = $this->_db->prepare($sql);         //调用自己变量直接不用$
        $stmt->bindParam(':username',$username);
        $stmt->execute();
        $result = $stmt -> fetch(PDO::FETCH_ASSOC);
        return !empty($result);
    }
}