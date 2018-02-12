<?php

    // 用token(header.payload.signature)代替cookie+session的验证方式
    public static function encode(array $payload, string $key, string $alg = 'HS256') //这里alg是随便定的，为signature的计算方式
    {
        /*
        header 格式为:

{
"typ":"JWT",
"alg":"HS256"
}
        */

        /*
        $payload=[
            'iss' => $issuer, //签发者
            'iat' => $_SERVER['REQUEST_TIME'], //什么时候签发的
            'exp' => $_SERVER['REQUEST_TIME'] + 7200 //过期时间
            'uid'=>1111
        ];*/
        //payload包含签发者，签发时间，签发过期时间
        $key = md5($key);
        $jwt = base64_encode(json_encode(['typ' => 'JWT', 'alg' => $alg])) . '.' . base64_encode(json_encode($payload));
        return $jwt . '.' . self::signature($jwt, $key, $alg); //这里jwt包括前两个
    }

   public static function signature(string $input, string $key, string $alg)
    {
        return hash_hmac($alg, $input, $key);
    }

public static function decode(string $jwt, string $key)
    {
        $tokens = explode('.', $jwt);//按照.分隔开
        
        $key    = md5($key);

        if (count($tokens) != 3)
            return false;

        list($header64, $payload64, $sign) = $tokens; //分开放入

        $header = json_decode(( base64_decode$header64), JSON_OBJECT_AS_ARRAY);
        if (empty($header['alg']))
            return false;

        if (self::signature($header64 . '.' . $payload64, $key, $header['alg']) !== $sign)
            return false;

        $payload = json_decode(self::urlsafeB64Decode($payload64), JSON_OBJECT_AS_ARRAY);

        $time = $_SERVER['REQUEST_TIME'];
        if (isset($payload['iat']) && $payload['iat'] > $time)
            return false;

        if (isset($payload['exp']) && $payload['exp'] < $time)
            return false;

        return $payload;
    }