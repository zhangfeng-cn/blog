<?php

    namespace app\http\middleware;
    
    use think\Db;

    class Visitor
    {
        public function handle($request, \Closure $next)
        {
            // 设置缓存，当日零点前的访客只能访问一次，第二日清零
            $ip = request()->ip();
            $today_start = strtotime(date('Y-m-d 00:00:00'));
            $today_end = strtotime(date('Y-m-d 23:59:59'));
            // 查询当日是否浏览过
            $exists = Db::name('visitor')->where(['ip' => $ip])->whereBetween('date', "$today_start,$today_end")->find();
            if ($exists) {
                return $next($request);
            }
            // 记录访客ip、城市
            // $info = controller('api/Home')->getIPInfo($ip)['result'];
            $info = getIPInfo($ip);
     
            if (is_array($info)) {
                $address = $info['province'] . ' ' . $info['city'];
            }else{
                $address = '其他';
            }
            // 如果是广东，但查询不到城市则跳过
            if (!$info['city'] && $info['province'] == '广东省')
            {
                return $next($request);
            }

            if (!empty($info)) {
                // 广东省记录城市，非广东只记录省
                if ($info['province'] == '广东省') {
                    $data = [
                        'ip' => $ip,
                        'city' => $info['city'],
                        'date' => time(),
                    ];
                }else{
                    $data = [
                        'ip' => $ip,
                        'city' => $info['province'],
                        'date' => time(),
                    ];
                }
            }else{
                $data = [
                    'ip'   => $ip,
                    'date' => time(),
                    'city' => '非公共ip地址',
                ];
            }

            db('visitor')->insert($data);
            return $next($request);
        }
    }
