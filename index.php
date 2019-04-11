<?php

//获得参数 signature nonce token timestamp echostr
$nonce     = $_GET['nonce'];
$token     = 'weixin';                                  //#令牌(Token)
$timestamp = $_GET['timestamp'];
$echostr   = $_GET['echostr'];
$signature = $_GET['signature'];
//形成数组，然后按字典序排序
$array = array();
$array = array($nonce, $timestamp, $token);
sort($array);
//拼接成字符串,sha1加密 ，然后与signature进行校验
$str = sha1( implode( $array ) );
if( $str  == $signature && $echostr ){
    //第一次接入weixin api接口的时候
    echo  $echostr;
}else{
    $services = new services();
    $services->reponseMsg();
}

class services{

    private $_subscribe = '欢迎关注我们的微信公众账号';//关注回复内容

    private $_appid = '';

    private $_appsecret = '';

    private $_accessTokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s";//获取accessToken地址

    private $_serverIpUrl = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=%s";//微信服务器IP地址

    public $_menuUrl = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=%s";//菜单栏

    public $_templateUrl = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=%s";//模板通知地址

    public $_templateId= "PXCJWwBQ406Of7cTpBvGFb8ILbxlRAfdYovKlWWFw8I";//模板ID

    public $_userUrl = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN";

    public $_menuArr;

    public function __construct()
    {
        $this->_menuArr = array(
            'button'=>array(
                //一级菜单
                array(
                    'name'=>urlencode('菜单一'),
                    'type'=>'click',
                    'key'=>'item1',
                ),
                array(
                    'name'=>urlencode('菜单二'),
                    //二级菜单
                    'sub_button'=>array(
                        array(
                            'name'=>urlencode('菜单二-1'),
                            'type'=>'click',
                            'key'=>'item2',
                        ),
                        array(
                            'name'=>urlencode('百度首页'),
                            'type'=>'view',
                            'url'=>'https://www.baidu.com/',
                        )
                    ),
                ),
                array(
                    'name'=>urlencode('菜单三'),
                    'type'=>'click',
                    'key'=>'item3',
                ),
            )
        );
    }

    /**
     * @desc 微信推送,接收消息入口
     */
    public function reponseMsg(){
        //1.获取到微信推送过来post数据（xml格式）
        $postArr = file_get_contents('php://input');

        //2.处理消息类型，并设置回复类型和内容
        $postObj = simplexml_load_string( $postArr );
        //$postObj->ToUserName = '';//开发者微信号
        //$postObj->FromUserName = '';//发送方帐号（一个OpenID）
        //$postObj->CreateTime = '';//消息创建时间 （整型）
        //$postObj->MsgType = '';//消息类型，文本为text
        //$postObj->Event = '';

        //判断该数据包是否是订阅的事件推送
        if( strtolower( $postObj->MsgType) == 'event'){
            //如果是关注 subscribe 事件
            if( strtolower($postObj->Event == 'subscribe') ){
                //回复用户消息(纯文本格式)
                $content  = $this->_subscribe.$postObj->FromUserName.'-'.$postObj->ToUserName;
                $this->responseText($postObj,$content);

                //回复多图文
//                $this->responseNews($postObj,array(
//                    array(
//                        'title'=>'hao123',
//                        'description'=>"hao123 is very cool",
//                        'picUrl'=>'https://www.baidu.com/img/bdlogo.png',
//                        'url'=>'http://www.hao123.com',
//                    )
//                ));
            }

            //自定义事件中的click
            if(strtolower($postObj->Event) == 'click'){
                switch (strtolower($postObj->EventKey)){
                    case 'item1':
                        $content = '这是item1菜单的事件推送';
                        $this->responseText($postObj,$content);
                        break;
                    case 'item2':
                        $content = '这是item2-1菜单的事件推送';
                        $this->responseText($postObj,$content);
                        break;
                    case 'item3':
                        $content = '这是item3菜单的事件推送';
                        $this->responseText($postObj,$content);
                        break;
                    default:
                        $content = '未设置的事件推送';
                        $this->responseText($postObj,$content);
                        break;
                }
            }

        }


        //用户发送的文本消息
        if( strtolower($postObj->MsgType) == 'text'){
            switch( trim($postObj->Content) ){
                case 1:
                    $content = '您输入的数字是1';
                    break;
                case 2:
                    $content = '您输入的数字是2';
                    break;
                case 3:
                    $content = '您输入的数字是3';
                    break;
                default:
                    $content = '您输入的内容是'.$postObj->Content;
                    break;
            }
            $this->responseText($postObj,$content);

        }

        //用户发送的图片消息
        if(strtolower($postObj->MsgType) == 'image'){
            //
        }

        //用户发送的语音消息
        if(strtolower($postObj->MsgType) == 'voice'){
            //
        }

        //用户发送的视频消息
        if(strtolower($postObj->MsgType) == 'video'){
            //
        }

        //用户发送的小视频消息
        if(strtolower($postObj->MsgType) == 'shortvideo'){
            //
        }

        //用户发送的地理位置消息
        if(strtolower($postObj->MsgType) == 'location'){
            //
        }

        //用户发送的链接消息
        if(strtolower($postObj->MsgType) == 'link'){
            //
        }

    }

    /**
     * @desc 回复单文本
     * @param $postObj
     * @param $content
     * @return string
     */
    private function responseText($postObj,$content){
        $template = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[%s]]></MsgType>
		<Content><![CDATA[%s]]></Content>
		</xml>";
        //注意模板中的中括号 不能少 也不能多
        $fromUser = $postObj->ToUserName;
        $toUser   = $postObj->FromUserName;
        $time     = time();
        $msgType  = 'text';
        echo sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
    }


    /**
     * @desc 回复多图文类型的微信消息
     * @param $postObj
     * @param $arr
     * @return string
     */
    private function responseNews($postObj ,$arr){
        $toUser = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
        $template = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<ArticleCount>".count($arr)."</ArticleCount>
					<Articles>";
        foreach($arr as $k=>$v){
            $template .="<item>
						<Title><![CDATA[".$v['title']."]]></Title> 
						<Description><![CDATA[".$v['description']."]]></Description>
						<PicUrl><![CDATA[".$v['picUrl']."]]></PicUrl>
						<Url><![CDATA[".$v['url']."]]></Url>
						</item>";
        }

        $template .="</Articles>
					</xml> ";
        echo sprintf($template, $toUser, $fromUser, time(), 'news');
    }

    /**
     * @desc 获取access_token,保存到SESSION或者redis等...
     * @return mixed
     */
    private function getWxAccessToken(){
        //讲access_token存在session中
        if($_SESSION['access_token'] && $_SESSION['expire_time'] > time()){
            //如果access_token在session 并未过期
            return $_SESSION['access_token'];
        }else{
            //如果access_token 不存在或者已经过期,重新获取access_token
            $res = $this->http_curl(sprintf($this->_accessTokenUrl,$this->_appid,$this->_appsecret));
            //正常返回: {"access_token":"ACCESS_TOKEN","expires_in":7200}

            $access_token = $res['access_token'];

            //将重新获取到的access_token存到session
            $_SESSION['access_token'] = $access_token;
            $_SESSION['expire_time'] = time()+$res['expires_in'];

            return $access_token;
        }
    }

    /**
     * @desc 获取微信服务器IP地址
     * @return mixed|array
     */
    private function getWxServerIp(){
        //正常返回: {    "ip_list": [        "127.0.0.1",         "127.0.0.2",         "101.226.103.0/25"    ]}
        return $this->http_curl(sprintf($this->_serverIpUrl,$this->getWxAccessToken()));
    }


    /**
     * 创建菜单栏
     */
    public function definedItem(){
        //创建微信菜单
        $postJson = urldecode(json_encode($this->_menuArr));
        return $this->http_curl(sprintf($this->_menuUrl,$this->getWxAccessToken()),$postJson,'post');
    }

    /**
     * 发送模板
     */
    public function sendTemplateMsg(){
        $array = array(
            'touser'=>'',
            'template_id'=>$this->_templateId,
            'topcolor'=>'#FF0000',
            'url'=>'https://www.baidu.com/',
            'data'=>array(
                'name'=>array(
                    'value'=>'先生一',
                    'color'=>'#173177',
                ),
                'money'=>array(
                    'value'=>'100',
                    'color'=>'#173177',
                ),
                'date'=>array(
                    'value'=>date('Y-m-d H:i:s'),
                    'color'=>'#173177',
                )
            ),
        );

        $postJson = json_encode($array,true);

        return $this->http_curl(sprintf($this->_templateUrl,$this->getWxAccessToken()),$postJson,'post');
    }

    /**
     * 获取用户基本信息
     */
    public function getUserInfo(){
        $openId = '';
        return $this->http_curl(sprintf($this->_userUrl,$this->getWxAccessToken(),$openId));
    }

    /*
    * ---------------------------# 公共方法 #---------------------------
    */


    /**
     * @param $url
     * @param string $data
     * @param string $type
     * @param string $res
     * @return mixed|string
     */
    public function http_curl($url,$data='',$type='get',$res='json'){
        //1.初始化curl
        $ch = curl_init();

        //2.设置curl的参数
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(strtolower($type) == 'post'){
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        }

        //3.采集
        $output = curl_exec($ch);

        //4.关闭
        curl_close($ch);

        if(strtolower($res) == 'json'){
            if(curl_errno($ch)){
                //请求失败,返回错误信息
                return curl_error($ch);
            }else{
                //请求成功
                return json_decode($output,true);
            }
        }
        return $output;
    }

    private function write_log($data){

        $years = date('Y-m');
        $url  = './log/'.$years.'/'.$years.'_request_log.txt';
        $dir_name = dirname($url);

        if(!file_exists($dir_name)) {

            $res = mkdir(iconv("UTF-8","GBK",$dir_name),0777,true);
        }

        $fp = fopen($url,"a");//打开文件资源通道 不存在则自动创建

        fwrite($fp,var_export($data,true)."\r\n");//写入文件

        fclose($fp);//关闭资源通道

    }

}

//设置菜单栏
if($_GET['open'] ==1){
    $services = new services();
    print_r($services->definedItem());
}

//发送模板
if($_GET['open'] ==2){
    $services = new services();
    print_r($services->sendTemplateMsg());
}

//获取用户信息
if($_GET['open'] ==3){
    $services = new services();
    print_r($services->getUserInfo());
}

