<?php

    namespace app\http\middleware;

    use think\Request;

    class Read
    {
        public function handle(Request $request, \Closure $next)
        {
            $id = $request->param('article_id');
            // 阅读数 +1
            db('article')->where('id', $id)->setInc('read');

            return $next($request);
        }
    }
