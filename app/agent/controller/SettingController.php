<?php
// +----------------------------------------------------------------------
// | 优站通 [ 一站到底，让排名不是梦 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.youzhantong.vip All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: WangMode <wangmode@163.com>
// +----------------------------------------------------------------------
namespace app\agent\controller;

use app\agent\model\AgentModel;
use cmf\controller\UserBaseController;
use \think\Db;
use think\Exception;
use \think\Validate;
class SettingController extends UserBaseController
{

    /**
     * 个人资料
     */
    public function index()
    {
        $this->assign('agent_info', cmf_get_current_user());
        return $this->fetch();
    }

    //更新个人资料
    function update_agent_info(){
        $info = $this->request->post();
        $data['company'] = $info['company'];
        $data['linkman'] = $info['linkman'];
        $data['linkphone'] = $info['linkphone'];
        $data['wechat'] = $info['wechat'];
        $data['email'] = $info['email'];
        $data['qq'] = $info['qq'];
        $id = cmf_get_current_user_id();
        $model = new AgentModel();
        $re = $model->updateAgentData($id,$data);
        if($re){
            $userInfo = $model->getAgentData($id);
            cmf_update_current_user($userInfo);
            return json(['status'=>1,'msg'=>'更新成功！']);
        }else{
            return json(['status'=>0,'msg'=>'没有更新项！']);
        }

    }

    /**
     * 熊掌号配置
     */
    public function xzh()
    {
        if ($this->request->isPost()) {
            $info = $this->request->param('xzh/a');
            if($info['type']==2){
                 $validate = new Validate(
                    [
                        'company'             => 'require',
                        'org_code'               => 'require',
                        'org_license'            => 'require',
                    ],
                    [
                        'company.require'                => '企业名称不要为空！',
                        'org_code.require'   => '营业执照注册号不要为空！',
                        'org_license.require' => '请上传营业执照！',
                    ]
                );
            }else{
                $validate = new Validate(
                    [
                        'operation_name'             => 'require',
                        'operation_id'               => 'require',
                        'operation_idcard'            => 'require',
                    ],
                    [
                        'operation_name.require'                => '运营者姓名不要为空！',
                        'operation_id.require'   => '运营者身份证不要为空！',
                        'operation_idcard.require' => '请上传手持身份证照！!',
                    ]
                );
            }
            if (!$validate->check($info)) {
                return json(['status'=>0,'msg'=>$validate->getError()]);
            }

            cmf_set_option('xzh_info', $info);
            $info['avatar'] = base64EncodeImage(trim($info['avatar'],'/'));
            $info['org_license'] = base64EncodeImage(trim($info['org_license'],'/'));
            $info['auth_img'] = base64EncodeImage(trim($info['auth_img'],'/'));
            $info['operation_idcard'] = base64EncodeImage(trim($info['operation_idcard'],'/'));
            $info['status'] = 1;
            $info['third_id'] = str_rand();
            $result = XzhModel::xzh_submit($info);
            //对接代理平台
            if($result['status']==1){//提交成功
                return json(['status'=>1,'msg'=>$result['msg']]);
            }else{
                return json(['status'=>0,'msg'=>$result['msg']]);
            }
        }

        $host = $_SERVER['HTTP_HOST'];
        $url = get_site_url($host);
        $agent_xz_info = XzhModel::where('url',$url)->find();
        //审核状态
        $status = 0;
        if($agent_xz_info){
            $status = $agent_xz_info['status'];
            $this->assign('info', $agent_xz_info);
        }
        $this->assign('status', $status);
        $this->assign('xzh_info', cmf_get_option('xzh_info'));
        $this->assign('domain', cmf_get_option('domain'));
        return $this->fetch();
    }

    function xzh_cx(){
        $xzh = new XzhModel();
        $re = $xzh->inquireReview('Nfgad0LYyUxMeqQNi0');
        print_r($re);exit;
    }


    //版本更新
    function update(){
        //检查版本更新
        if($this->request->isPost()){
            $update = new Update();
            $re = $update->oneKeyUpdate();
            if($re==1){
                return json(['status'=>1,'msg'=>'更新成功！']);
            }else{
                return json(['status'=>0,'msg'=>$re]);
            }
        }else{
            $is_update = 0;
            $update = new Update();
            $update_info= $update->checkVersion();
            if($update_info['status']){
                $is_update = 1;
            }
            $this->assign('version_info', $update_info['version_info']);
            $this->assign('is_update', $is_update);
            return $this->fetch();
        }


    }


    /**
     * 网站信息设置提交
     */
    public function sitePost()
    {
        if ($this->request->isPost()) {
            $result = $this->validate($this->request->param(), 'SettingSite');
            if ($result !== true) {
                return json(['status'=>0,'msg'=>$result]);
            }

            $options = $this->request->param('site_info/a');
            cmf_set_option('site_info', $options);

            //更新网推侠配置
            $site_name = explode('|',$options['sitename']);

            $options['pc_slide'];

            $config['company'] = $site_name[count($site_name)-1];

            $config['linkman'] = $options['linkman'];
            $config['linkphone'] = $options['phone'];
            $config['qq'] = $options['qq'];
            $config['email'] = $options['email'];
            $config['address'] = $options['address'];
            InfoModel::setTable('yzt_config')->where('admin_id',config('wtx_admin_id'))->update($config);

            return json(['status'=>1,'msg'=>'保存成功']);
        }
    }

    /**
     * 密码修改
     */
    public function password()
    {
        return $this->fetch();
    }

    /**
     * 密码修改提交
     */
    public function passwordPost()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            if (empty($data['old_password'])) {
                return json(['status'=>0,'msg'=>'原始密码不能为空']);
            }
            if (empty($data['password'])) {
                return json(['status'=>0,'msg'=>'新密码不能为空']);
            }

            $userId = cmf_get_current_user_id();

            $agent = new AgentModel();
            try{
                $agentData = $agent->getAgentData($userId);
                $result = $agent->editAgentPassword($data,$agentData,$userId);
                return $result;
            }catch (Exception $exception){
                return $this->returnListJson(self::CODE_FAIL,0,null,$exception->getMessage());
            }
        }
    }

    /**
     * 网推侠配置
     */
    public function wtx_info()
    {
        if ($this->request->isPost()) {
            $wtx_info = $this->request->param('wtx_info/a');
            //发布状态
            if(isset($wtx_info['status']) && $wtx_info['status']=='on'){
                $wtx_info['status'] = 1;
            }else{
                $wtx_info['status'] = 0;
            }

            //开关
            if(isset($wtx_info['is_display']) && $wtx_info['is_display']=='on'){
                $wtx_info['is_display'] = 1;
            }else{
                $wtx_info['is_display'] = 0;
            }

            cmf_set_option('wtx_info', $wtx_info);

            //修改网推侠配置
            $config['is_display'] = $wtx_info['is_display'];
            $config['url'] = $wtx_info['url'];
            $config['wap_url'] = $wtx_info['wap_url'];
            InfoModel::setTable('yzt_config')->where('admin_id',config('wtx_admin_id'))->update($config);

            return json(['status'=>1,'msg'=>'保存成功']);
        }
        $this->assign('wtx_info', cmf_get_option('wtx_info'));
        return $this->fetch();
    }

    /**
     * 清除缓存
     */
    public function clearCache()
    {
        cmf_clear_cache();
        return $this->fetch();
    }


}