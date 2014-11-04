<?php
date_default_timezone_set('PRC');
define('TOKEN', '密码');
require_once 'Curl.php';
require_once 'XUtils.php';
class weixin {
    protected $redis;
 //test inveroment
    private $textTpl = "<xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[%s]]></MsgType>
                                <Content><![CDATA[%s]]></Content>
                                </xml>";
    private $token = '';
    private $curl;

    public function weixin() {
        $act = isset($_GET['act']) && $_GET['act'] ?  $_GET['act'] : ( isset($_POST['act']) && $_POST['act']  ? $_POST['act'] : '');
        if(method_exists($this, $act)) {
            $this->redis = new Redis();
            $this->redis->connect('127.0.0.1');
            $this->curl = new Curl();
            $this->token = $this->getToken();
            $this->{$act}();
        }  else {
            echo "没有{$act}方法";
        }
    }

   
    public function init() {
        $this->token = $this->actionGetToken();
     //   $this->token = 'XbmUuI41OH1Ra4i8bO0M4crL8FkH7I__2RUJH6vueEbUKnOJQbQL1Fu6e7A3OT8RrGbTiZ9iXho4QMIf9OMD5jUFbEebs6fVuKpNVMIdW6B9sn53u4rWhhucGYWLg5RuwTfzNghReiimSEXAMR_5fA';
         parent::init();
    }

    public function getToken() {
        $token = $this->redis->get('weixin_access_token');
        if(!$token) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx614260eb51439de2&secret=f9a5c2a7904d3468ab0e806acfe01eea";
            $retArr = $this->curl->get($url);
            if($retArr) {
                $token = $retArr->access_token;
                $this->redis->set('weixin_access_token', $token, 7200);
            }

       }
       return $token;
    }
    public function validate() {
        //验证是否是微信的消息
       $echostr = isset($_GET['echostr']) ? $_GET['echostr'] : '';
        if($echostr) {
            if($this->checkSign() ) {
                exit($echostr);
            }
        } else {
            //返回信息给微信
            $this->PushMessageToUser();
        }
        
       // $content = "\n[" . Date('Y-m-d h:i:s'). "]\n" . json_encode($_GET);
       // file_put_contents('log/weixin_validate.log', $content , FILE_APPEND );
    }
    private function checkSign() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 去重复参见：http://mp.weixin.qq.com/wiki/index.php?title=%E6%8E%A5%E6%94%B6%E6%99%AE%E9%80%9A%E6%B6%88%E6%81%AF
    **/
    private function isReceivedMessage($messageId) {
        return false;
    }
    /**
     * 消息回复
    **/
    public function responseMessage() {

        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        //extract post data
        $retStr = '';
        if (!empty($postStr)) {
            try {
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $messageId = trim($postObj->msgid);
                 //去掉微信重复的请求id
                if(!$this->isReceivedMessage($messageId)) {
                    $retStr = '请输入查询内容';
                    if(!empty( $keyword )) {
                        $msgType = "text";
                        if(preg_match('/[你|您]好/', $keyword)) {
                            $contentStr = '您好,我是途星网小秘书测试账号。';
                             $retStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        } else if(preg_match('/最热行程/', $keyword)) {
                            $msgType = 'news';
                            $articles = array(
                                array(
                                    'title' => '上海出发丨美西3大历史名城+科罗拉多峡谷9日精华游（包上海往返美国机票）',
                                    'description' => '加州第一历史文化名城旧金山(知名学府斯坦福大学，金门大桥，渔人码头，自费深度游(叮当车，圣玛丽大教堂，双子峰，同性恋街))，',
                                    'picurl' => 'http://www.ituxing.com/index/show/b8420e1ea3d3ef2830797c33b23fa97f?type=7.html',
                                    'url' => 'http://www.ituxing.com/trip/detail/SHA-14TSG-2.html',
                                ),
                                array(
                                    'title' => '【十景十美】8日生态深度游,黄石/大提顿/大峡谷等十大自然美景一网打尽',
                                    'description' => '【十景十美】8日生态深度游,黄石/大提顿/大峡谷等十大自然美景一网打尽',
                                    'picurl' => 'http://www.ituxing.com/index/show/3bbb476656913036e07cdc58a5995dbe?type=7.html',
                                    'url' => 'http://www.ituxing.com/trip/detail/14TYA8.html',
                                ),
                                  array(
                                    'title' => '【自由夏威夷】6日5晚畅游两大海岛,品味世外桃源,享受蓝天白云',
                                    'description' => '你将来到有世外桃源之称的夏威夷！来到曾经被日本偷袭过的【珍珠港】，游览州政府大楼、夏威夷第一任国王铜像以及夏威夷皇宫；富有夏威夷风情的【威基基海滩】；',
                                    'picurl' => 'http://www.ituxing.com/index/show/582ec2820d2394a78fc9c18f11740a1c?type=7.html',
                                    'url' => 'http://www.ituxing.com/zt/sealove.html',
                                ),
                            );
                            $contentStr = $this->_replayMulitText($fromUsername, $toUsername, $time, $msgType,$articles);
                            $retStr =  sprintf($contentStr, $fromUsername, $toUsername, $time, $msgType);
                        } else if(preg_match('/草|cao|草泥马|尼玛/', $keyword)) {
                             $contentStr = '请注意文明用语';
                             $retStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        } else if(preg_match('/百度|baidu/', $keyword)){
                            $contentStr = '您要了解百度相关信息 <a href="http://www.baidu.com">百度</a>';
                             $retStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        }else if(preg_match('/途星|tuxing/', $keyword)){
                            $contentStr = '您要了解我嘛，请猛戳这里 <a href="http://www.ituxing.com">途星网</a>';
                             $retStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        }else {
                            $contentStr = '途星网官方微信服务号。途星网（ituxing.com)，精心打造独特品味的美洲旅行。
                                            回复“最热行程”将推送给您最热行程信息
                                            回复“wap”将引导您去wap站点
                                            回复“报名”将引导您参加最热行程报名。
                                            您还可以调戏客服
                                            更多功能正在紧张建设中，敬请期待！';
                             $retStr = sprintf($this->textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        }
                    }
                }
            } catch(Exception $ex) {
              //  XUtils::writeLog('weixin_log:' . $ex->getMessage(), 'weixin_log', 'file');
            }
        }
        echo  $retStr;
    }
    /**
     * 获取单个用户信息
    **/
    public function getOneUser($uname) {
        $user = new stdClass;
        if($uname &&  $this->token) {
            $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' .  $this->token. '&openid=' . $uname . '&lang=zh_CN';
            $user = $this->curl->post($url, array(), $_is_verify=true);
            if($user && $user->data) {
                $user = json_decode($user->data);
            }
        }
        return $user;
    }
    /**
     * 反馈图文混排记录
    **/
    private function _replayMulitText($fromUsername, $toUsername, $time, $msgType, $articles=array()) {
        $mulitTpl ='';
        try {
                $contentstr = '';
                foreach($articles as $value) {
                    $contentstr.= "<item>
                                            <Title><![CDATA[".$value['title']."]]></Title>
                                            <PicUrl><![CDATA[". $value['picurl']."]]></PicUrl>
                                            <Description><![CDATA[" . $value['description'] . "]]></Description>
                                            <Url><![CDATA[". $value['url']."]]></Url>
                                        </item>";
                }
                 $mulitTpl = "<xml>
                                <ToUserName><![CDATA[{$fromUsername}]]></ToUserName>
                                <FromUserName><![CDATA[{$toUsername}]]></FromUserName>
                                <CreateTime>{$time}</CreateTime>
                                <MsgType><![CDATA[{$msgType}]]></MsgType>
                                <ArticleCount>" . sizeof($articles) . "</ArticleCount><Articles>{$contentstr}</Articles></xml>";

        } catch (Exception $e) {
            XUtils::writeLog('weixin_log:' . $e->getMessage(), 'weixin_log', 'file');
        }
        return $mulitTpl;
    }
    /**
     * 重复请求,参见http://mp.weixin.qq.com/wiki/index.php?title=%E6%8E%A5%E6%94%B6%E4%BA%8B%E4%BB%B6%E6%8E%A8%E9%80%81
    **/
    private function isRepeatRequest($strId, $method) {
        if($method == 'subscribe') {
            return false;
        } else {
            return false;
        }
    }
    /**
     * 关注计数
    **/
    public function responseFollowOrUnFollow() {
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
            $retStr = '';
            if(!empty($postStr)) {
                 $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                 $toUserName = $postObj->ToUserName;
                 $fromUserName = $postObj->FromUserName;
                 $createTime = $postObj->CreateTime;
                 $msgType = $postObj->MsgType;
                 $event = $postObj->Event;
                 $eventKey = $postObj->EventKey;//二维码扫描关注
                 $time = time();
                 if($event == 'subscribe') {
                    if(!$this->isRepeatRequest($fromUserName . $createTime, $event)) {
                        $contentStr = '感谢您的关注';
                        $retStr =  sprintf($this->textTpl, $fromUserName, $toUserName, $time, $msgType, $contentStr);
                    }
                 } else if($event == 'unsubscribe'){
                        if(!$this->isRepeatRequest($fromUserName . $createTime, $event)) {
                            $contentStr = '非常感谢您一直以来的关注';
                            $retStr =  sprintf($this->textTpl, $fromUserName, $toUserName, $time, $msgType, $contentStr);
                        }
                 } else {
                    $retStr = '消息类型错误';
                }
            }
            echo  $retStr;
    }
    /**************************************************************
       *
       *    使用特定function对数组中所有元素做处理
       *    @param  string  &$array     要处理的字符串
       *    @param  string  $function   要执行的函数
       *    @return boolean $apply_to_keys_also     是否也应用到key上
       *    @access public
       *
     *************************************************************/
    public function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }
    private function _getJSON(&$demo_data) {
        $this->arrayRecursive($demo_data, 'urlencode', true);
        $demo_data = urldecode(json_encode($demo_data));
    }
    /**
     * 创建自定义菜单
    **/
    public function CreateMenu() {
        $ret = array('errcode' => 1, 'msg'=> 'curl发送失败');
        //前台自定义菜单
        // $menu = $_POST['menu'];
        // if(!$menu) {
        //     return  json_encode(array('errcode' => 1, '没有数据'));
        // }
        $menu1 = array(
            "name" => "优惠专区",
            "sub_button" => array(
                array(
                    "type" => "view",
                    "name" => "［金牌］玩全美，7.5折特权",
                    "url"=>"http://aililuo.com/weixin/ituxing.php?type=1",
                ),
                 array(
                    "type" => "view",
                    "name" => "［海岛］夏天的迷情海湾",
                    "url"=>"http://aililuo.com/weixin/ituxing.php?type=2",
                ),
                  array(
                    "type" => "view",
                    "name" => "［黄石］喜欢就走吧，七折",
                    "url"=>"http://aililuo.com/weixin/ituxing.php?type=2",
                ),
            )
        );
        $menu2 =  array(
            "type" => "view",
            "name" => "黄石惊喜",
            "url" => "http://aililuo.com/weixin/ituxing.php?type=4",
        );
        // $menu2 =  array(
        //             "type" => "click",
        //             "name" => "最新动态",
        //             "key" => "TUXING_NEWS",
        //         );
        $menu3 =  array(
                    "name" => "旅游服务",
                    "sub_button" => array(
                        array(
                            "type" => "view",
                            "name" => "问问客服",
                            "url"=>"http://aililuo.com/weixin/ituxing.php?type=4"
                        ),
                         array(
                            "type" => "click",
                            "name" => "我要报名",
                            "key" => "MEMORYZA_SEND",
                        ),
                         array(
                            "type" => "view",
                            "name" => "签证指南",
                            "url" => "http://aililuo.com/weixin/ituxing.php?type=5",
                        ),
                    )
                );
        $button =array(
                    $menu1, $menu2, $menu3
            );
        $demo_data = array(  "button" =>$button);
        $this->_getJSON($demo_data);
        $data  = isset($_POST['menu']) && $_POST['menu'] ? $_POST['menu'] : $demo_data;
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->token;
        $ret = $this->curl->post($url, $data);
       if($ret) {
            $ret = array(
                'errcode' => $ret->errcode,
                'msg' => $ret->errmsg
            );
        }
        exit(json_encode($ret));
    }
    /**
     * 查询自定义菜单
    **/
    public function ViewMenu() {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=' . $this->token;
         $ret = $this->curl->post($url, array(), $is_verify=true);
         if($ret->data) {
               return $ret->data;
        }
        return json_encode(new stdClass);
    }
    /**
     * 删除自定义菜单
    **/
    public function DeleteMenu() {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='. $this->token;
        $ret = $this->curl->post($url, array());
        if($ret->data) {
            return $ret->data;
        }
        return json_encode(array("errcode"=> 1,"errmsg"=> "删除自定义菜单失败"));
    }
    /**
     * 接受用户消息以后的发推送给用户
    **/
    public function PushMessageToUser($toUsername='') {
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
            if(!empty($postStr)) {
                 $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                 
                 $msgType = $postObj->MsgType;
            
                 switch($msgType) {
                    case 'event':
                        $toUserName = $postObj->ToUserName;
                         $fromUserName = $postObj->FromUserName;
                         $createTime = $postObj->CreateTime;
                         $event = $postObj->Event;
                         $eventKey = $postObj->EventKey;//二维码扫描关注
                         $time = time();
                         $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $this->token;
                        if($event == 'CLICK') {
                            if($eventKey == 'MEMORYZA_SEND') {
                                $repost = array(
                                    "touser" => $fromUserName,
                                    "msgtype" => "news",
                                    "news" => array(
                                        'articles' => array(
                                            array(
                                                'title' => '黄石专题跟团报名入口',
                                                'description' => '震撼超低价，6日黄石自然风光超值游',
                                                'url' => "http://www.aililuo.com/weixin/index.html",
                                                'picurl' => "http://www.ituxing.com/images/weixin/it1.jpg"
                                            ),
                                        )
                                    )
                                );
                                
                            } else {
                                $repost = array(
                                    "touser" => $fromUserName,
                                    "msgtype" => "text",
                                    "text" => array(
                                        "content"=>"图形网信息反馈"
                                    )
                                );
                        
                            }
                            $this->_getJSON($repost);
                            $ret = $this->curl->post($url, $repost);
                            // } else {
                            //     $repost = array(
                            //         "touser" => $fromUserName,
                            //         "msgtype" => "news",
                            //         "news" => array(
                            //             'articles' => array(
                            //                 array(
                            //                     'title' => '黄石-包伟湖-布莱斯峡谷-优胜美地8天游(F1Y5-旧金山进盐湖城出 一、三、四、日)',
                            //                     'description' => '当用户主动发消息给公众号的时候（包括发送信息、点击自定义菜单、订阅事件、扫描二维码事件、支付成功事件、用户维权），微信将会把消息数据推送给开发者，开发者在一段时间内（目前修改为48小时）可以调用客服消息接口，通过POST一个JSON数据包来发送消息给普通用户，在48小时内不限制发送次数。此接口主要用于客服等有人工消息处理环节的功能，方便开发者为用户提供更加优质的服务。',
                            //                     'url' => "http://www.ituxing.com/trip/detail?id=14TF1Y5",
                            //                     'picurl' => "http://www.ituxing.com/index/show/16712ed73fceae43fa033c0736f0abe0?type=7.html"
                            //                 ),
                            //                  array(
                            //                     'title' => '迈阿密+美东9日游（KW9-周一）',
                            //                     'description' => '1. 接机当天送特色游*+自由购物（独家）
                            //                                             2. 特色风味餐：波士顿龙虾餐和新英格兰蛤蜊巧达浓汤
                            //                                             3. 纵横美国深度走访美东各大城市名胜，还可游览迈阿密，西锁岛等渡假胜地，行程更丰富，省时省钱。
                            //                                             4. 博览美南最大港口城市---迈阿密迷人风光
                            //                                             5. 在沼泽河流中，“草上飞”风力船带您体验与“鳄鱼亲密接触”的惊险之旅
                            //                                             6. 参观南部第一街“DECO Street”, 游览32个不同岛屿，穿越42座桥，带您领略佛罗里达最南端西锁岛美奂绝伦的独特风景',
                            //                     'url' => "http://www.ituxing.com/trip/detail?id=14KW9",
                            //                     'picurl' => "http://www.ituxing.com/index/show/213c06fd6ad3a4db36bce2d568b80211?type=7.html"
                            //                 ),
                            //                  array(
                            //                     'title' => '旧金山-圣塔芭芭拉-优胜美地三日游',
                            //                     'description' => '游览: 加州第一历史文化名城旧金山(知名学府斯坦福大学，金门大桥，渔人码头，自费深度游(叮当车，圣玛丽大教堂，双子峰，同性恋街)，优胜美地国家公园.',
                            //                     'url' => "http://www.ituxing.com/trip/detail?id=14TSFO",
                            //                     'picurl' => "http://www.ituxing.com/index/show/b4664d10bd88206bea082d051067f819?type=7.html"
                            //                 ),
                            //             ),
                            //         )
                            //     );
                            // }
                            // $contentStr = $this->_replayMulitText($fromUsername, $toUsername, $time, $msgType,$repost);
                            // $retStr =  sprintf($contentStr, $fromUsername, $toUsername, $time, $msgType);
                            // echo $retStr;
                        } else if($event == 'subscribe') {
                            $repost = array(
                                    "touser" => $fromUserName,
                                    "msgtype" => "news",
                                    "news" => array(
                                        'articles' => array(
                                            array(
                                                'title' => '黄石-包伟湖-布莱斯峡谷-优胜美地8天游(F1Y5-旧金山进盐湖城出 一、三、四、日)',
                                                'description' => '当用户主动发消息给公众号的时候（包括发送信息、点击自定义菜单、订阅事件、扫描二维码事件、支付成功事件、用户维权），微信将会把消息数据推送给开发者，开发者在一段时间内（目前修改为48小时）可以调用客服消息接口，通过POST一个JSON数据包来发送消息给普通用户，在48小时内不限制发送次数。此接口主要用于客服等有人工消息处理环节的功能，方便开发者为用户提供更加优质的服务。',
                                                'url' => "http://www.ituxing.com/trip/detail?id=14TF1Y5",
                                                'picurl' => "http://www.ituxing.com/index/show/16712ed73fceae43fa033c0736f0abe0?type=7.html"
                                            ),
                                             array(
                                                'title' => '迈阿密+美东9日游（KW9-周一）',
                                                'description' => '1. 接机当天送特色游*+自由购物（独家）
                                                                        2. 特色风味餐：波士顿龙虾餐和新英格兰蛤蜊巧达浓汤
                                                                        3. 纵横美国深度走访美东各大城市名胜，还可游览迈阿密，西锁岛等渡假胜地，行程更丰富，省时省钱。
                                                                        4. 博览美南最大港口城市---迈阿密迷人风光
                                                                        5. 在沼泽河流中，“草上飞”风力船带您体验与“鳄鱼亲密接触”的惊险之旅
                                                                        6. 参观南部第一街“DECO Street”, 游览32个不同岛屿，穿越42座桥，带您领略佛罗里达最南端西锁岛美奂绝伦的独特风景',
                                                'url' => "http://www.ituxing.com/trip/detail?id=14KW9",
                                                'picurl' => "http://www.ituxing.com/index/show/213c06fd6ad3a4db36bce2d568b80211?type=7.html"
                                            ),
                                             array(
                                                'title' => '旧金山-圣塔芭芭拉-优胜美地三日游',
                                                'description' => '游览: 加州第一历史文化名城旧金山(知名学府斯坦福大学，金门大桥，渔人码头，自费深度游(叮当车，圣玛丽大教堂，双子峰，同性恋街)，优胜美地国家公园.',
                                                'url' => "http://www.ituxing.com/trip/detail?id=14TSFO",
                                                'picurl' => "http://www.ituxing.com/index/show/b4664d10bd88206bea082d051067f819?type=7.html"
                                            ),
                                        ),
                                    )
                                );
                            $this->_getJSON($repost);
                            
                            $ret = $this->curl->post($url, $repost);
           
                            return $ret;
                        }
                         break;
                    case 'text':
                        $this->responseMessage();
                    default:
                        break;
                }
        }
    }
}
new weixin();
