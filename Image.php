<?php
header("Content-Type:text/html;charset=UTF-8");
// 乱码看三个地方，
// 1、数据库编码
// 2、页面编码
// 3、连接编码
// 三个一致了就毛事木有。
class Image
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
    public function saveimages($Id,$url,$path = '../images/') //上传图片
    {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    $file = curl_exec($ch);
    curl_close($ch);
    $filename = pathinfo($url, PATHINFO_EXTENSION);
    $filename=$Id.'.'.$filename;
    $resource = fopen($path . $filename, 'a');
    $res='http://localhost/Blog/images/'.$filename;
    if(fwrite($resource, $file)){
        return ['status'=>1,
                'url'=>$res
                ];
    }else{
        return ['status'=>0];
    }
    fclose($resource);
    }
}
?>