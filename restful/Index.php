<?php
header("Content-Type:text/html;charset=UTF-8");
require "../User.php";
require "../Article.php";
$pdo = require '../Db.php';

class Restful
{
    private $_user;
 
    private $_article;

    private $_requestMethod;

    private $_resourceName; //请求的资源名称
    
    private $authorid;

    private $author;

    private $_id;
    //允许资源列表
    private $_allowResources = ['articles','users'];
    //允许请求的方法
    private $_allowRequestMethods =['GET','POST','PUT','DELETE','OPTIONS'];
    
    /*常用状态错误
    */
    private $_statusCodes=[
        200 => 'OK',
        204 => 'No Content',
        400 => 'Bad request', //语法错误，服务器不理解
        401 => 'Unauthorized', //为进行权限认证
        403 => 'Forbidden', //权限不足
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Server Internal Error' //服务器错误
    ];
    
    public function __construct(User $_user,Article $_article)
    {
        $this -> _user = $_user;
        $this -> _article = $_article;
    }
    //对于线上的 API 必须保证所有接口正常且关闭所有的错误信息 => error_reporting(0)
    //接口开发，不建议使用框架开发
    //1.接口效率,维护成本2.稳定性,API出错问题很严重
    //API接口开放就是一些大平台提供一些API供你交互使用。
    public function run()
    { //只有一个公共的入口
        //初始化
        try{
        $this -> _setupRequestMethod(); //下的判断method是否合理
        $this -> _setupResource();      //判断articles/id, 这个id资源
        if($this -> _resourceName =='users'){ //因为只有2个合法的方法
            return $this -> _json($this -> _handleUser());
        }else if($this -> _resourceName == 'articles'){
            return $this -> _json($this -> _handleArticle());
        }
        } catch (Exception $e){  //throw 这个类型为e 其有2个方法
            $this -> _json(['error'=>$e->getMessage(),'code'=>$e->getCode()]);
        }
    }

    private function _handleUser()
    { //0是注册，1是登录
        if($this -> _requestMethod !='POST'){ //用户注册或登录
            throw new Exception('请求方法不被允许',405);
        }
        $body =array();
<<<<<<< HEAD
        $body = $this -> _getBodyParams(); //这里拿的是从body拿过来的数据,json格式，直接拿是拿不到的,
        if($this -> _id == 1){
        $name = $this -> _userLogin($body['username'],$body['password']);
        $jwt=$this -> encode($name['username']); //给一个签发者的名字,获得20分钟专属token
        return [ 
            'name' => $name['username'],
            'token' => $jwt,
        ]; //之前就已经定好返回的为json格式
=======
        $body = $this -> _getBodyParams(); //josn格式，直接拿是拿不到的
        if($this ->_setupResource() == 1){
        $name = $this -> _userLogin($body['username'],$body['password']);
        $jwt = $this -> encode($name);
        echo $jwt;
        return $jwt;
>>>>>>> 029d41411ae74c5a9e851de0fb347cd49b25752c
        }else
        return $this -> _user -> register($body['username'],$body['password'],$body['email'],$body['name'],$body['intro'],$body['sex']); //这里是注册
    }

    private function _getBodyParams()
    { //获取请求体
        $raw = file_get_contents('php://input'); //php的文件流,这里获取的$raw是从url的
        if(empty($raw)){
            throw new Exception('请求参数错误',400);
        }
        return json_decode($raw,true); //这里传入的为json格式，但是使用的时候需要转换为php数组,且第二个变量一定要为true
        //TRUE时返回数组，FALSE时返回对象 
    }
    
    private function _handleArticle()
    {
        switch($this ->_requestMethod)
        {
            case 'GET':
            if(empty($this -> _id)){
                return $this -> _handleArticleList();//当ID为0就是返回一个列表的文章
            }else{
                return $this -> _handleArticleView(); //返回一个单独的文章
            }
<<<<<<< HEAD
            case 'POST':
            if($this -> check())
            return $this -> _handleArticleCreate();
            else
            print_r('验证出错');
            case 'DELETE':
            if($this -> check())
            return $this -> _handleArticleDelete();
            else
            print_r('验证出错');
            case 'PUT':
            if($this -> check())
            return $this -> _handleArticleEdit();
            else
            print_r('验证出错');
            
=======
            case 'POST':{
            if(!$restful -> decode(apache_request_headers();,'6'))return 0;
            
            return $this -> _handleArticleCreate();
            }case 'DELETE':{
            if(!$restful -> decode(apache_request_headers();,'6'))return 0;
            return $this -> _handleArticleDelete();
            }case 'PUT':{
            if(!$restful -> decode(apache_request_headers();,'6'))return 0;
            return $this -> _handleArticleEdit();
            }
>>>>>>> 029d41411ae74c5a9e851de0fb347cd49b25752c
        }
    }
    //添加文章
     private function _handleArticleCreate()
    {
        $body =array();
        $body = $this -> _getBodyParams();
        //$user = $this -> _userLogin($_SERVER['PHP_AUTH_USER'],$this -> _user -> _md5($_SERVER['PHP_AUTH_PW']));
        try{
            $article = $this -> _article -> create($this -> author ,$body['title'],$body['content']);
            return $article;
        } catch (Exception $e){
            throw new Exception('请求方法不被允许',405);
        }
    }
    //删除文章
    private function _handleArticleDelete()
    {
        try{
       // $user = $this -> _userLogin($_SERVER['PHP_AUTH_USER'],$this -> _user -> _md5($_SERVER['PHP_AUTH_PW']));
        $article =$this -> _article -> view($this -> _id);
        $result = $this ->_article -> delete($article['id'],$this -> author);
        return $result;
        }catch (Exception $e){
            $this -> _json(['error'=>$e->getMessage(),'code'=>$e->getCode()]);
        }
    }
    //更新文章
     private function _handleArticleEdit()
    {
        try{
        $body = array();
        $body = $this -> _getBodyParams();
        $result = $this ->_article -> edit($this -> _id,$body['content'],$body['title'],$this -> author);
        return $result;
        }catch (Exception $e){
            $this -> _json(['error'=>$e->getMessage(),'code'=>$e->getCode()]);
        }
    }
    //查找文章(id)
     private function _handleArticleView()
    {
        try{
        $article =$this -> _article -> view($this -> _id);
        return $article;
        }catch (Exception $e){
            $this -> _json(['error'=>$e->getMessage(),'code'=>$e->getCode()]);
        }
    }
    //查找文章(列表')
    public function _handleArticleList()
    {
        try{
        $result = $this ->_article ->getList($_GET['authorid']);
        return $result;
        }catch (Exception $e){
           $this -> _json(['error'=>$e->getMessage(),'code'=>$e->getCode()]);
        }
    }
    private function _userLogin($PHP_AUTH_USER,$PHP_AUTH_PW)
    {
        try{
            return $this -> _user -> login($PHP_AUTH_USER,$PHP_AUTH_PW);
        } catch (Exception $e){
           $this -> _json(['error'=>$e->getMessage(),'code'=>$e->getCode()]);
        }
    }
    private function _json($array,$code=0)
    {
         if($code>0 && $code!=200 && $code!=204){
         header("HTTP/1.1 {$code} {$this->_statusCodes[$code]}");
        }
        header('Content-Type:application/json;charset=utf-8');  //json 的一个头为utf8格式
        echo json_encode($array,JSON_UNESCAPED_UNICODE);
        exit();
    }
    /*初始化请求方法
    */
    public function _setupRequestMethod()
    {
        $this -> _requestMethod = $_SERVER['REQUEST_METHOD'];
        if(!in_array($this -> _requestMethod, $this -> _allowRequestMethods))
        {
          throw new Exception('请求方法不被允许',405);
        }
    }
    /*初始化请求资源
    */
    public function _setupResource()
    {
        $path = $_SERVER['PATH_INFO'];
        $params = explode('/',$path); //把path通过/分割为数组
        $this -> _resourceName=$params[1];
        if(!in_array($this -> _resourceName,$this -> _allowResources)){
            throw new Exception('请求资源错误',400);
        }
        if(!empty($params[2])){
            $this -> _id=$params[2];
        }
    }
    public function encode($issuer,$key='6',$alg = 'sha1') //这里alg是随便定的，为signature的计算方式
    {
        
        $payload=[
            'iss' => $issuer, //签发者
            'iat' => $_SERVER['REQUEST_TIME'], //什么时候签发的
            'exp' => $_SERVER['REQUEST_TIME'] + 7200 //过期时间
        ];
        //payload包含签发者，签发时间，签发过期时间
        $key = md5($key);
        $jwt = base64_encode(json_encode(['typ' => 'JWT', 'alg' => $alg])) . '.' . base64_encode(json_encode($payload));
        return $jwt . '.' . self::signature($jwt, $key, $alg); //这里jwt包括前两个
    }
    //alg要使用hash算法的名字,input为数据,key为密钥
<<<<<<< HEAD
   public  function signature($input,$key,$alg='sha1')
=======
   public  function signature($input,$key,$alg)
>>>>>>> 029d41411ae74c5a9e851de0fb347cd49b25752c
    {
   // return $input1.$key.$alg; 
    return hash_hmac($alg, $input, $key);
    }
<<<<<<< HEAD
    public function decode($jwt,$key='6')
    {
        $arr = explode(" ",$jwt);
        $jwt = $arr[1];
        $tokens = explode('.',$jwt);//按照.分隔开
        
        $key = md5($key);
        if (count($tokens) != 3)
            return false;
        list($header64, $payload64, $sign) = $tokens; //分开放入
        $header = json_decode(base64_decode($header64), JSON_OBJECT_AS_ARRAY);
        if (empty($header['alg']))
            return false;
        if (self::signature($header64 . '.' . $payload64, $key) !== $sign)
            return false;
       $payload = json_decode(base64_decode($payload64), JSON_OBJECT_AS_ARRAY);
        $time = $_SERVER['REQUEST_TIME'];
        if (isset($payload['iat']) && $payload['iat'] > $time)
            return false;
        if (isset($payload['exp']) && $payload['exp'] < $time)
            return false;
        $this ->author = $payload['iss'];
        return $payload;
    }
    public function check(){
    $header=apache_request_headers();
    $header=getallheaders();
    $jwt=$header['Authorization'];
    return $this -> decode($jwt);
    }
=======
    public function decode($jwt,$key)
    {
        $tokens = explode('.', $jwt);//按照.分隔开
        
        $key    = md5($key);
        if (count($tokens) != 3)
            return false;

        list($header64, $payload64, $sign) = $tokens; //分开放入

        $header = json_decode(base64_decode($header64), JSON_OBJECT_AS_ARRAY);
        if (empty($header['alg']))
            return false;

        if (self::signature($header64 . '.' . $payload64, $key, $header['alg']) !== $sign)
            return false;

        $payload = json_decode(base64_decode($payload64), JSON_OBJECT_AS_ARRAY);

        $time = $_SERVER['REQUEST_TIME'];
        if (isset($payload['iat']) && $payload['iat'] > $time)
            return false;

        if (isset($payload['exp']) && $payload['exp'] < $time)
            return false;
        return $payload;
    }
>>>>>>> 029d41411ae74c5a9e851de0fb347cd49b25752c
}
$article = new Article($pdo);
$user = new User($pdo);
$restful = new Restful($user,$article);
$restful -> run();
<<<<<<< HEAD
//获取请求头的数据Authorization
=======
 //获取请求头的数据Authorization
>>>>>>> 029d41411ae74c5a9e851de0fb347cd49b25752c
?>