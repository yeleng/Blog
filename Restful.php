<?php
header("Content-Type:text/html;charset=UTF-8");
require "User.php";
require "Article.php";
class Restful
{
    private $_user;
 
    private $_article;
    public function __construct(User $_user,Article $_article)
    {
        $this -> _user = $_user;
        $this -> _article = $_article;
    }
    //对于线上的 API 必须保证所有接口正常且关闭所有的错误信息 => error_reporting(0)
    //接口开发，不建议使用框架开发
    //1.接口效率,维护成本2.稳定性,API出错问题很严重
    //API接口开放就是一些大平台提供一些API供你交互使用。

}
$article = new Article();
$user = new User();
$restful = new Restful($user,$article);
