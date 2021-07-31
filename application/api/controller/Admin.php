<?php

namespace app\api\controller;

use app\api\model\Art;
use think\Controller;
use think\Request;

class Admin extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $data = Art::order('create_time DESC, id DESC')->select();

        return  success_r($data);
    }

    public function admin()
    {
        // 当日日期
        $today_start = strtotime(date('Y-m-d 00:00:00'));
        $today_end = strtotime(date('Y-m-d 23:59:59'));
        // 查询今日访客
        $visitor = db('visitor')->whereBetween('date', [$today_start,$today_end])->count();
        // 查询文章点赞数量
        $thumb = Art::sum('thumb_up');
        // 查询总阅读量
        $read = Art::sum('read');
        // 查询总访问数
        $visitors = db('visitor')->count();
        // 查询文章总数量
        $articles = Art::count('id');
        // 查询访客来源城市
        $visitor_f = db('visitor')->field('city as name, count(*) as value')->group('city')->order('value', 'DESC')->limit(10)->select();
//        print_r($visitor_f); die();
        // 查询近15日访问量 bug 如果有那天没有访问记录，数据会不准确
        $date = db('visitor')->field('count(FROM_UNIXTIME(date,"%Y-%m-%d")) as count')->group('FROM_UNIXTIME(date,"%Y-%m-%d")')->order('date DESC')->limit(15)->select();
        $days = [];
        foreach ($date as $v)
        {
            $days[] = $v['count'];
        }

        if (count($days) < 15) {
            $num = 15 - count($days);
            for ($i = 0; $i < $num; $i++) {
                array_push($days, 0);
            }
        }
        $days = array_reverse($days);
        $data = [
            'visitor' => $visitor,
            'thumb' => $thumb,
            'visitors' => $visitors,
            'read' => $read,
            'article' => $articles,
            'days' => $days,
            'visitor_f' => $visitor_f
        ];

        return success_r($data);
    }


    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        // 插入数据
        $data = $request->param('data')['article'];

        $result = Art::create($data);

        return success_r($result);
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

    /**
     * 显示编辑资源表单页.
     *
     * @param int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
        $data = Art::find($id);
        
        return success_r($data);
    }

    /**
     * 保存更新的资源
     *
     * @param \think\Request $request
     * @param int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
        $data = $request->param('data')['article'];

        $result = Art::update($data);

        return success_r($result);
    }



    /**
     * 删除指定资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
        $result = Art::destroy($id);
        $msg = $result ? '删除成功' : '删除失败';
        return success_r($msg);
    }


    public function auth()
    {
        $token = request()->param('token');
        $token_auth = cache('token');
        // 验证两个token
        if ($token == $token_auth) {
            // 延长时间
            cache('token', $token, 1800);
            $data = '';
        } else {
            $data = '无效token';
        }
        return success_r($data);
    }


    // 获得用户信息
    public function user()
    {

    }


    // 查询分类数据到发布文章
    public function loginout()
    {
        cache('user', null);
        return success_r('', '退出成功');
    }
    
    // 查询分类数据到发布文章
    public function getAuthors()
    {
        //
        $data = db('user')->column('name');
        return success_r($data);
    }
    
    /*
     * 文件上传
     */
    public function upload()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move( './uploads');
        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
            // echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            $url = 'http://' . $_SERVER['SERVER_NAME'] . '/uploads/' . $info->getSaveName();
            return success_r($url);
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
            // echo $info->getFilename(); 
        }else{
            // 上传失败获取错误信息
            return success_r($file->getError());
        }
    }
    
    /*
     * 删除上传的图片
     */
    public function deleteImg()
    {
        $param = request()->param('data');
        $param = json_decode($param, true);
        // 拼接要删除的路径 ./uploads/20210706/79124d839655fa728a9e7c7d2a4dc081.jpg
        $deleteUrl = str_replace('http://' . $_SERVER['SERVER_NAME'], '.', $param['url']);
        
        if(file_exists($deleteUrl)){
            unlink($deleteUrl);
            return success_r('删除成功');
        }else{
            return error_r('删除失败');
        }
    }
}
