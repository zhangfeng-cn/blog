<?php

    namespace app\api\controller;

    use app\api\model\User;
    use think\captcha\Captcha;
    use think\Controller;
    use think\Request;

    class Login extends Controller
    {
        // 生成验证码图片
        public function getCaptcha()
        {
            $config = [
                'codeSet' => '0123456789',  // 验证码字符集合
                'fontSize' => 30,  // 验证码字体大小
                'fontttf' => '5.ttf',  //字体
                'length' => 4, // 验证码位数
                'bg' => [230, 236, 253], // 背景颜色
                'useCurve' => false,
                'useNoise' => false
            ];

            $captcha = new Captcha($config);

            return $captcha->entry();
        }

        //验证账号是否已存在
        public function nameExists(Request $request)
        {
            $name = $request->get('name');

            // 查询账号是否已存在
            $result = User::where('name', $name)->value('id');

            if ($result) {
                return success_r('', '账号已存在');
            } else {
                return error_r('账号可用');
            }
        }


        // 验证登录
        public function login(Request $request)
        {
            // 接收数据
            $ip = $request->ip();
            $name = $request->param('name');
            $password = $request->param('password');
            $code = $request->param('code');
            $salt = '风过无痕';

            // 核对验证码
            $nameCaptcha = request()->ip() . 'captcha';
            if (!cache($nameCaptcha) || cache($nameCaptcha) !== strtoupper($code))
            {
                return error_r( '验证码错误');
            }

            $user = User::getByName($name);

            if ($user && $password == $user->password) {
                // 隐藏密码项
                $user->password = null;

                // 生成 token, 加盐加密
                $token = md5($name . $password . $salt);
                cache('token', $token, 1800);
                cache('user', $user, 1800);

                // 格式化日期
                $week = ["日", "一", "二", "三", "四", "五", "六"];
                $user->login_time .= ' 星期' . $week[date('w', strtotime($user->login_time))];

                // 构建返回结果
                $data = [
                    'user' => $user,
                    'token' => $token,
                ];
                // $info = controller('api/Home')->getIPInfo($ip)['result'];
                // $address = $info['province'] . ' ' . $info['city'];
                $info = getIPInfo($ip);
                if (is_array($info)) {
                    $address = $info['province'] . ' ' . $info['city'];
                }else{
                    $address = '其他';
                }
                // 更新此次登录时间,地点
                User::update(['id' => $user['id'], 'login_time' => date('Y-m-d h:i:s', time()), 'address' => $address]);

                return success_r($data, '登录成功');
            } else {
                return error_r('登录失败');
            }
        }


        // 注册账号
        public function register(Request $request)
        {
            // 接收数据
            $name = $request->param('name');
            $ip = $request->ip();
            $password = $request->param('password');
            $code = $request->param('code');
           
            // 核对验证码
            $nameCaptcha = request()->ip() . 'captcha';
            if (!cache($nameCaptcha) || cache($nameCaptcha) !== strtoupper($code))
            {
                return error_r( '验证码错误');
            }

            // $info = controller('api/Home')->getIPInfo($ip)['result'];
            // $address = $info['province'] . ' ' . $info['city'];
            $info = getIPInfo($ip);
            if (is_array($info)) {
                $address = $info['province'] . ' ' . $info['city'];
            }else{
                $address = '其他';
            }

            // 新增用户到user表
            $result = User::create([
                'name' => $name,
                'ip' => $ip,
                'password' => $password,
                'login_address' => $address,
            ]);

            if ($result) {
                return success_r('', '注册成功');
            } else {
                return error_r('注册成功');
            }
        }
    }
