<?php
header("Content-Type:text/html;charset=UTF-8");
// 乱码看三个地方，
// 1、数据库编码
// 2、页面编码
// 3、连接编码
// 三个一致了就毛事木有。
class Article
{
    /**这是一个数据库的句柄
     * 
     */
    private $_db;
    /**
     * 构造方法为数据库连接
     */
    public function __construct($_db){
        $this -> _db = $_db;
    }
    /**创建文章
     * 
     */
    public function create($author,$title,$content)
    {
        if(empty($title)){
            throw new Exception('文章标题不能为空',ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY);
        }
        if(empty($content)){
            throw new Exception('文章内容不能为空',ErrorCode::ARTICLE_CONTENT_CANNOT_EMPTY);
        }
        if(empty($author)){
            throw new Exception('作者名不能为空',ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY);
        }
        $sql = 'SELECT * FROM `zhuche` WHERE `username`=:author';
        $stmt=$this -> _db ->prepare($sql); 
        $stmt -> bindParam(':author',$author);
        $stmt -> execute();
        $man = $stmt -> fetch(PDO::FETCH_ASSOC);

        $sql = 'INSERT INTO `main_info`(`title`,`content`,`author`,`time`,`authorId`) VALUE (:title,:content,:author,:time,:id)';
        $time = date('Y-m-d',time());
        $stmt = $this -> _db -> prepare($sql);
        $stmt -> bindParam(':title',$title);
        $stmt -> bindParam(':content',$content);
        $stmt -> bindParam(':author',$author);
        $stmt -> bindParam(':time',$time);
        $stmt -> bindParam(':id',$man['id']);
        if(!$stmt->execute()){   //如果添加失败
            throw new Exception('发表文章失败',ErrorCode::ARTICLE_CREATE_FAIL);
        }
            return ['articleId' => $this -> _db -> lastInsertId(),
                    'title' => $title,
                    'content' => $content,
                    'author' => $author,
                    'authorId' => $man['id']
            ];                                                                                                                                                                                                                                                       
    }
    /**编辑文章
     * 
     */
    public function edit($articleId,$content,$title,$author)   //这个文章的id需要与修改人的id匹配,一般用id而不是字符串的作者
    {
        $article = $this -> view($articleId);
        if($article['author']!=$author){
            throw new Exception('对不起，您无权操作这篇文章',ErrorCode::PERMISSION_DENIED);
        }
        $title = empty($title)?$article['title']:$title;
        $content = empty($content)?$article['content']:$content;
        if($article['content']==$content&&$article['title']==$title)
        {
            return $article;
        }
        $sql = 'UPDATE `main_info` SET `title`=:title,`content`=:content WHERE `id` = :articleId';
        //更新的时候需要一个逗号
        $stmt=$this -> _db ->prepare($sql);
        $stmt -> bindParam(':content',$content);
        $stmt -> bindParam(':title',$title);
        $stmt -> bindParam(':articleId',$articleId);
        if(!$stmt -> execute()){
            throw new Exception('文章编辑失败',ErrorCode::ARTICLE_EDIT_FAIL);
        }
        return ['articleId' => $articleId,
                'title' => $title,
                'content' => $content,
                'time' => $article['time']
        ];
    }
     /**
      * 查看文章内容
      */
    public function view($articleId){
        if(empty($articleId)){
            throw new Exception('文章ID不能为空',ErrorCode::ARTICLE_ID_CANNOT_EMPTY);
        }
        $sql = 'SELECT * FROM `main_info` WHERE `id`=:id';
        $stmt=$this -> _db ->prepare($sql); 
        $stmt -> bindParam(':id',$articleId);
        $stmt -> execute();
        $article = $stmt -> fetch(PDO::FETCH_ASSOC);
        if(empty($article)){
            throw new Exception('文章不存在',ErrorCode::ARTICLE_NOT_FOUND);
        }
        return $article;
    }
    /**
     * 删除文章
     */
    public function delete($articleId,$author)
    {
        $article = $this -> view($articleId);
        if($article['author']!=$author){
            throw new Expection('您权限不足', PERMISSION_DENIED);
        }
        $sql = 'SELECT * FROM `main_info` WHERE `id`=:articleId';
        $stmt =$this -> _db ->prepare($sql);
        $stmt -> bindParam(':articleId',$articleId);
        $stmt -> execute();   //查成一个数组
        $article = $stmt -> fetch(PDO::FETCH_ASSOC);
        if(empty($article)){
            throw new Exception('找不到此文章',ErrorCode::ARTICLE_NOT_FOUND);
        }
        $sql = 'DELETE FROM `main_info` WHERE `id`=:articleId AND `author`=:author';
        $stmt =$this -> _db ->prepare($sql);
        $stmt -> bindParam(':articleId',$articleId);
        $stmt -> bindParam(':author',$author);
        if(!$stmt->execute()){
            throw new Exception('删除文章失败',ErrorCode::ARTICLE_DELETE_FALI);
        }
        return true;
    }
    public function getList($authorid, $page= 1,$size =10)
    {
        if($size>100){
            throw new Exception('分页大小最多为100',15);
        }
        $limit = ($page-1)*$size;
        $limit = $limit < 0 ? 0:$limit;
        $sql = 'SELECT * FROM `main_info` WHERE `authorId`=:authorid LIMIT '.$limit.','.$size;
        //这个只能根据键查找
        //从limit开始(不算limit)的后offset个
        $stmt= $this -> _db -> prepare($sql);
        $stmt->bindParam(':authorid',$authorid);
       // $stmt->bindParam(':limit',$limit);
    //    $stmt->bindParam(':offset',$size);
        $stmt->execute();
        $data = $stmt ->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
}
?>