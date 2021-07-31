<?php

    namespace app\api\controller;

    use app\api\model\Art;
    use app\api\model\User;
    use think\Controller;
    use think\Db;
    use think\Request;

    class Home extends Controller
    {

        // 显示最近文章数据
        public function lists(Request $request)
        {
            // 获取当前页
            $page = $request->param('page');
            $cate = $request->param('cate');

            if ($cate == '最近更新')
            {
                $data['items'] = Art::where('id <> 1')->order('create_time DESC, id DESC')->limit(16)->select();

            }else{
                // 自定义每页显示条数
                $shows = 10;

                // 查询 HTML/CSS 分类的文章
                $query = Art::where(['category' => $cate]);
                $total = $query->count();  // 总记录数量
                $items = $query->where('id <> 1')->order('create_time DESC, id DESC')->page($page, $shows)->select();

                // 计算页码
                $pages = ceil($total / $shows);

                $data = [
                    'items' => $items,
                    'pages' => $pages,
                ];
            }

            // 当前登录用户
            $data['user'] = cache('user');

            return success_r($data);
        }


        // 显示文章详细
        public function article($article_id)
        {
            $ip = request()->ip();
            // 以id查询文章
            $article = Art::find($article_id);
            // 查看是否点赞
            $thumb_status = $this->isThumb($article_id, $ip);

            if ($article) {
                $data = [
                    'article' => $article,
                    'thumb_status' => $thumb_status,
                    'user' => cache('user') // 返回登录状态
                ];
                return success_r($data);
            } else {
                return error_r('查询失败');
            }
        }


        // 判断是否有点赞？
        public function isThumb($article_id, $ip)
        {
            // 判断数据
            if ($article_id && $ip) {
                // 查找用户
                $user = User::where('ip', $ip)->find();
                // 判断用户是否存在
                if (!$user) {
                    // 1为新用户
                    return 1;
                }

                // 通过文章 article_id 查询 user_article 表的得到这篇文章点赞过的用户组
                $users = Db::table('user_article')->where('article_id', $article_id)->select();
                // 判断 $articles 是否有数据，也就是说，这篇文章有没被点赞过
                if (!$users) {
                    // 2表示是用户但未对这篇点赞
                    return 2;
                }

                foreach ($users as $v) {
                    // 如果相等说明已经赞过了
                    if ($user->id == $v['user_id']) {
                        // 相等了，3为已点赞
                        return 3;
                        break;
                    }
                }

                // 2为用户但未对这篇点赞
                return 2;
            }
        }


        // 更新点赞数
        public function updateThumb(Request $request, $article_id)
        {
            // 接收参数
//            $article_id = $request->param('id');
            $user_name = $request->param('user_name');
            $thumb = $request->param('thumb');
            $thumb++; // 旧赞数+1

            // 查询 ip 来源信息，如 城市 运营商等
            $ip = $request->ip();
            $info = getIPInfo($ip);
            if (is_array($info)) {
                $address = $info['province'] . ' ' . $info['city'];
            }else{
                $address = '其他';
            }

            // 访客点赞状态，$thumbed = 0错误状态，1为新用户，2为用户但未对这篇文章点赞，3为已点赞,在前端已拦截
            $thumbed = $request->param('thumbed');

            // 1为新用户
            if ($thumbed == 1) {
                if ($user_name) {
                    // 新增用户到user表
                    $user_id = Db('user')->insertGetId([
                        'name' => $user_name,
                        'ip' => $ip,
                        'login_address' => $address,
                    ]);
                } else {
                    $name = '访客' . date('YmdHis');
                    // 新增用户到user表
                    $user_id = Db('user')->insertGetId([
                        'name' => $name,
                        'ip' => $ip,
                        'login_address' => $address,
                    ]);
                }
            } else// 2为用户但未对这篇文章点赞
            {
                if ($user_name) {
                    // 查找对应的用户
                    $user_id = User::where('name', $user_name)->value('id');
                } else {
                    // 查找对应的用户
                    $user_id = User::where('ip', $ip)->value('id');
                }
            }

            // 更新文章表格的点赞数
            $thumb_ok = Art::where('id', $article_id)->setInc('thumb_up');
            // 更新中间表
            $user_article = Db::table('user_article')->insert([
                'user_id' => $user_id,
                'article_id' => $article_id,
            ]);

            if ($thumb_ok && $user_article) {
                return success_r($thumb);
            }
        }


        public function getIPInfo($ip)
        {
            $key = 'M3TBZ-TYLL2-5C4UT-CHGIM-NCYDJ-MUFMF';
            $url = 'https://apis.map.qq.com/ws/location/v1/ip?key=' . $key . '&ip=' . $ip;
            $res = httpRequest($url);
            var_dump($res); die(); 
            
            if ($ip == '127.0.0.1' || $ip == 'localhost')
            {
                $info = [
                    'result' => [
                        'province' => '广东省',
                        'city' => '中山市demo'
                    ]
                ];

                return $info;
            }

            // 构建请求信息
            error_reporting(E_ALL || ~E_NOTICE);
            $host = "https://ips.market.alicloudapi.com";
            $path = "/iplocaltion";
            $method = "GET";
            $appcode = "57025aa03b4b404b882e8a4794fa2ccf"; //开通服务后 买家中心-查看AppCode
            $headers = array();
            array_push($headers, "Authorization:APPCODE " . $appcode);
            $querys = 'ip=' . $ip;

            // 构建请求链接
            $url = $host . $path . "?" . $querys;

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_FAILONERROR, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_HEADER, true);

            if (1 == strpos("$" . $host, "https://")) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            }
            $out_put = curl_exec($curl);

            curl_close($curl);

            // 得到json，解码为数组
            return json_decode($out_put, true);
        }


        /**
         * 显示指定的资源
         *
         * @param int $id
         * @return \think\Response
         */
        public function read($id)
        {
            //
            $data = Art::find($id);

            return success_r($data);
        }

    }
