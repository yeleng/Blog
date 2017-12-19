<?php
header("Content-Type:text/html;charset=UTF-8");
class ErrorCode
{
    const USERNAME_CANNNOT_EMPTY = 1;  //用户名不能为空
    const USERNAME_EXISTS = 2; //用户名已存在
    const PASSWORD_CANNOT_EMPTY = 3;  //密码不能为空
    const REGISTER_FAIL = 4;//注册失败
    const USERNAME_OR_PASSWORD_INVALID=5; //用户名或密码错误
    //文章标题不可以为空
    const CONTENT_TITLE_CANNOT_EMPTY =6;
    const ARTICLE_CONTENT_CANNOT_EMPTY=7;//文章内容不能为空
    const ARTICLE_ID_CANNOT_EMPTY=8; //文章id不能为空
    const ARTICLE_NOT_FOUND=9; //文章不存在
    const PERMISSION_DENIED=10; //无权操作	
    const ARTICLE_EDIT_FAIL=11; //文章编辑失败
    const ARTICLE_DELETE_FALI = 12; //文章删除失败
    
}