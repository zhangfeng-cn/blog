<?php

namespace app\http\middleware;

use think\Request;

class Auth
{
    public function handle(Request $request, \Closure $next)
    {
        $params = $request->param('data');
        if ($request->method() == 'GET' || $request->method() == 'DELETE')
        {
            $params = json_decode($params, true);
        }

        if (isset($params['token']) && cache('token') == $params['token']){
            $user = cache('user');
            // 延长时间
            cache('user', $user, 1800);
            return $next($request);
        }else{
            return error_r('非法访问！');
        }
    }
}
