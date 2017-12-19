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
        $this -> _setupRequestMethod(); //run下的判断method是否合理
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
    {
        if($this -> _requestMethod !='POST'){ //用户注册
            throw new Exception('请求方法不被允许',405);
        }
        $body =array();
        $body = $this -> _getBodyParams(); //josn格式，直接拿是拿不到的
        return $this -> _user -> register($body['username'],$body['password'],$body['email'],$body['name'],$body['intro'],$body['sex']); //这里是注册
    }
    private function _getBodyParams()
    { //获取请求体
        $raw = file_get_contents('php://input'); //php的文件流
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
            case 'POST':
            return $this -> _handleArticleCreate();
            case 'DELETE':
            return $this -> _handleArticleDelete();
            case 'PUT':
            return $this -> _handleArticleEdit();
            
        }
    }
    //添加文章
     private function _handleArticleCreate()
    {
        $body = $this -> _getBodyParams();
        $user = $this -> _userLogin($_SERVER['PHP_AUTH_USER'],$this -> _user -> _md5($_SERVER['PHP_AUTH_PW']));
        try{
            $article = $this -> _article -> create($_SERVER['PHP_AUTH_USER'],$body['title'],$body['content']);
            return $article;
        } catch (Exception $e){
            throw new Exception('请求方法不被允许',405);
        }
    }
    //删除文章
    private function _handleArticleDelete()
    {
        try{
        $user = $this -> _userLogin($_SERVER['PHP_AUTH_USER'],$this -> _user -> _md5($_SERVER['PHP_AUTH_PW']));
        $article =$this -> _article -> view($this -> _id);
        $result = $this ->_article -> delete($article['id'],$user['username']);
        return $result;
        }catch (Exception $e){
            $this -> _json(['error'=>$e->getMessage(),'code'=>$e->getCode()]);
        }
    }
    //更新文章
     private function _handleArticleEdit()
    {
        try{
        $user = $this -> _userLogin($_SERVER['PHP_AUTH_USER'],$this -> _user -> _md5($_SERVER['PHP_AUTH_PW']));
        $body = $this -> _getBodyParams();
        $result = $this ->_article -> edit($this -> _id,$body['content'],$body['title'],$_SERVER['PHP_AUTH_USER']);
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
     private function _handleArticleList()
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
}
$article = new Article($pdo);
$user = new User($pdo);
$restful = new Restful($user,$article);
$restful -> run();
?>