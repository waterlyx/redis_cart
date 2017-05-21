<?php
/**
 * User: 刘业兴
 * @return \Redis
 * 描述：使用redis存储服务
 */
function redis()
{
    $redis = new \Redis();
    $redis->connect(C('REDIS_HOST'), C('REDIS_PORT'));//redis地址和端口
    $redis->auth(C('REDIS_AUTH_PASSWORD'));//redis密码
    return $redis;
}