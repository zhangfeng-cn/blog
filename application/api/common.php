<?php

// 返回成功信息
function success_r($data = '', $msg = 'success', $code = 200)
{
    $ret = [
        'code' => $code,
        'msg' => $msg,
        'ret' => $data
    ];
    return json($ret);
}

// 返回失败信息
function error_r($msg = 'error', $data = '', $code = 401)
{
    $ret = [
        'code' => $code,
        'msg' => $msg,
        'ret' => $data
    ];
    return json($ret);
}

echo '635';
