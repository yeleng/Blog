<?php
header("Content-Type:text/html;charset=UTF-8");
// 乱码看三个地方，
// 1、数据库编码
// 2、页面编码
// 3、连接编码
// 三个一致了就毛事木有。
class Comment
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
    /**创建新的评论内容
     * 
     */
    public function commentCreate($to_articleId,$content,$authorId,$to_authorId)//传入4个参数,被评论文章ID,评论内容,评论作者ID,被评论作者ID
    {
        if(empty($to_articleId)){
            throw new Exception('被评论文章ID不能为空',ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY);
        }
        if(empty($content)){
            throw new Exception('评论内容不能为空',ErrorCode::ARTICLE_CONTENT_CANNOT_EMPTY);
        }
        if(empty($authorId)){
            throw new Exception('评论作者不能为空',ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY);
        }
        if(empty($to_authorId)){
            throw new Exception('被评论对象ID不能为空',ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY);
        }
        $sql = 'SELECT * FROM `zhuche` WHERE `username`=:author';
        $stmt=$this -> _db ->prepare($sql); 
        $stmt -> bindParam(':author',$authorId);
        $stmt -> execute();
        $man = $stmt -> fetch(PDO::FETCH_ASSOC);
        $sql = 'INSERT INTO `comment`(`owner_user_id`,`target_user_id`,`content`,`created_at`,`parent_id`,`parent_type`) 
        VALUE (:authorId,:to_authorId,:content,:time,:to_articleId,:type)';
        $stmt = $this->_db->prepare($sql);     //要有全部的变量
        //这里parent_type为0表示一个文章新的评论,为1则为对一个评论的评论
        $time = date('Y-m-d',time());
        $type=0;
        $stmt = $this -> _db -> prepare($sql);
        $stmt -> bindParam(':authorId',$man['id']);
        $stmt -> bindParam(':to_authorId',$to_authorId);
        $stmt -> bindParam(':content',$content);
        $stmt -> bindParam(':time',$time);
        $stmt -> bindParam(':to_articleId',$to_articleId);
        $stmt -> bindParam(':type',$type);
        if(!$stmt->execute()){   //如果添加失败
            throw new Exception('发表评论失败',ErrorCode::ARTICLE_CREATE_FAIL);
        }
            return ['commentId' => $this -> _db -> lastInsertId(),
                    'owner' => $man['id'],
                    'target_user_id' => $to_authorId,
                    'parent_id' => $to_articleId,
                    'content' => $content
            ];                                                                                                                                                                                                                                               
    }

    public function commentCreate2($to_commentId,$content,$authorId,$to_authorId)//传入5个参数,被评论文章ID,评论内容,评论作者ID,被评论作者ID,被评论的评论ID
    {
        if(empty($to_commentId)){
            throw new Exception('被评论的评论ID不能为空',ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY);
        }
        if(empty($content)){
            throw new Exception('评论内容不能为空',ErrorCode::ARTICLE_CONTENT_CANNOT_EMPTY);
        }
        if(empty($authorId)){
            throw new Exception('评论作者不能为空',ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY);
        }
        if(empty($to_authorId)){
            throw new Exception('被评论对象ID不能为空',ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY);
        }
        $sql = 'SELECT * FROM `zhuche` WHERE `username`=:author';
        $stmt=$this -> _db ->prepare($sql); 
        $stmt -> bindParam(':author',$authorId);
        $stmt -> execute();
        $man = $stmt -> fetch(PDO::FETCH_ASSOC);
        $sql = 'INSERT INTO `comment`(`owner_user_id`,`target_user_id`,`content`,`created_at`,`parent_id`,`parent_type`) 
        VALUE (:authorId,:to_authorId,:content,:time,:parent_id,:type)';
        $stmt = $this->_db->prepare($sql);     //要有全部的变量
        //这里parent_type为0表示一个文章新的评论,为1则为对一个评论的评论
        $time = date('Y-m-d',time());
        $type=1;
        $stmt = $this -> _db -> prepare($sql);
        $stmt -> bindParam(':authorId',$man['id']);
        $stmt -> bindParam(':to_authorId',$to_authorId);
        $stmt -> bindParam(':content',$content);
        $stmt -> bindParam(':time',$time);
        $stmt -> bindParam(':parent_id',$to_commentId);
        $stmt -> bindParam(':type',$type);
        if(!$stmt->execute()){   //如果添加失败
            throw new Exception('发表评论失败',ErrorCode::ARTICLE_CREATE_FAIL);
        }
            return ['commentId' => $this -> _db -> lastInsertId(),
                    'owner' => $man['id'],
                    'target_user_id' => $to_authorId,
                    'parent_id' => $to_commentId,
                    'content' => $content
            ];                                                                                                                                                                                                                                              
    }
}
?>