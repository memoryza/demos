<?php
/**
 * 系统助手类
 *
 */

class XUtils {

    /**
     * 友好显示var_dump
     */
    static public function dump( $var, $echo = true, $label = null, $strict = true ) {
        $label = ( $label === null ) ? '' : rtrim( $label ) . ' ';
        if ( ! $strict ) {
            if ( ini_get( 'html_errors' ) ) {
                $output = print_r( $var, true );
                $output = "<pre>" . $label . htmlspecialchars( $output, ENT_QUOTES ) . "</pre>";
            } else {
                $output = $label . print_r( $var, true );
            }
        } else {
            ob_start();
            var_dump( $var );
            $output = ob_get_clean();
            if ( ! extension_loaded( 'xdebug' ) ) {
                $output = preg_replace( "/\]\=\>\n(\s+)/m", "] => ", $output );
                $output = '<pre>' . $label . htmlspecialchars( $output, ENT_QUOTES ) . '</pre>';
            }
        }
        if ( $echo ) {
            echo $output;
            return null;
        } else
            return $output;
    }

    /**
     * 获取客户端IP地址
     */
    static public function getClientIP() {
        static $ip = NULL;
        if ( $ip !== NULL )
            return $ip;
        if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $arr = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
            $pos = array_search( 'unknown', $arr );
            if ( false !== $pos )
                unset( $arr[$pos] );
            $ip = trim( $arr[0] );
        } elseif ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $ip = ( false !== ip2long( $ip ) ) ? $ip : '0.0.0.0';
        return $ip;
    }

    /**
     * 循环创建目录
     */
    static public function mkdir( $dir, $mode = 0777 ) {
        if ( is_dir( $dir ) || @mkdir( $dir, $mode ) )
            return true;
        if ( ! mk_dir( dirname( $dir ), $mode ) )
            return false;
        return @mkdir( $dir, $mode );
    }

    /**
     * 格式化单位
     */
    static public function byteFormat( $size, $dec = 2 ) {
        $a = array ( "B" , "KB" , "MB" , "GB" , "TB" , "PB" );
        $pos = 0;
        while ( $size >= 1024 ) {
            $size /= 1024;
            $pos ++;
        }
        return round( $size, $dec ) . " " . $a[$pos];
    }

    /**
     * 下拉框，单选按钮 自动选择
     *
     * @param $string 输入字符
     * @param $param  条件
     * @param $type   类型
     *            selected checked
     * @return string
     */
    static public function selected( $string, $param = 1, $type = 'select' )
    {
        $true = '';
        $return = '';
        if ( is_array( $param ) ) {
            $true = in_array( $string, $param );
        }elseif ( $string == $param ) {
            $true = true;
        }
        if ( $true )
            $return = $type == 'select' ? 'selected="selected"' : 'checked="checked"';

        echo $return;
    }

    /**
     * 获得来源类型 post get
     *
     * @return unknown
     */
    static public function method() {
        return strtoupper( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : 'GET' );
    }

    /**
     * 提示信息
     */
    static public function message( $action = 'success', $content = '', $redirect = 'javascript:history.back(-1);', $timeout = 4 ) {

        switch ( $action ) {
            case 'success':
                $titler = '操作完成';
                $class = 'message_success';
                $images = 'message_success.png';
                break;
            case 'error':
                $titler = '操作未完成';
                $class = 'message_error';
                $images = 'message_error.png';
                break;
            case 'errorBack':
                $titler = '操作未完成';
                $class = 'message_error';
                $images = 'message_error.png';
                break;
            case 'redirect':
                header( "Location:$redirect" );
                break;
            case 'script':
                if ( empty( $redirect ) ) {
                    exit( '<script language="javascript">alert("' . $content . '");window.history.back(-1)</script>' );
                } else {
                    exit( '<script language="javascript">alert("' . $content . '");window.location=" ' . $redirect . '   "</script>' );
                }
                break;
        }

        // 信息头部
        $header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>操作提示</title>
<style type="text/css">
body{font:12px/1.7 "\5b8b\4f53",Tahoma;}
html,body,div,p,a,h3{margin:0;padding:0;}
.tips_wrap{ background:#F7FBFE;border:1px solid #DEEDF6;width:780px;padding:50px;margin:50px auto 0;}
.tips_inner{zoom:1;}
.tips_inner:after{visibility:hidden;display:block;font-size:0;content:" ";clear:both;height:0;}
.tips_inner .tips_img{width:80px;float:left;}
.tips_info{float:left;line-height:35px;width:650px}
.tips_info h3{font-weight:bold;color:#1A90C1;font-size:16px;}
.tips_info p{font-size:14px;color:#999;}
.tips_info p.message_error{font-weight:bold;color:#F00;font-size:16px; line-height:22px}
.tips_info p.message_success{font-weight:bold;color:#1a90c1;font-size:16px; line-height:22px}
.tips_info p.return{font-size:12px}
.tips_info .time{color:#f00; font-size:14px; font-weight:bold}
.tips_info p a{color:#1A90C1;text-decoration:none;}
</style>
</head>

<body>';
        // 信息底部
        $footer = '</body></html>';

        $body = '<script type="text/javascript">
        function delayURL(url) {
        var delay = document.getElementById("time").innerHTML;
        //alert(delay);
        if(delay > 0){
        delay--;
        document.getElementById("time").innerHTML = delay;
    } else {
    window.location.href = url;
    }
    setTimeout("delayURL(\'" + url + "\')", 1000);
    }
    </script><div class="tips_wrap">
    <div class="tips_inner">
        <div class="tips_img">
            <img alt="途星网消息提示小图标" src="' . Yii::app()->baseUrl . '/static/images/' . $images . '"/>
        </div>
        <div class="tips_info">

            <p class="' . $class . '">' . $content . '</p>
            <p class="return">系统自动跳转在  <span class="time" id="time">' . $timeout . ' </span>  秒后，如果不想等待，<a href="' . $redirect . '">点击这里跳转</a></p>
        </div>
    </div>
</div><script type="text/javascript">
    delayURL("' . $redirect . '");
    </script>';

        exit( $header . $body . $footer );
    }

    /**
     * 查询字符生成
     */
    static public function buildCondition( array $getArray, array $keys = array() ) {
        $arr = array();
        if ( $getArray ) {
            foreach ( $getArray as $key => $value ) {
                if ( in_array( $key, $keys ) && $value ) {
                    $arr[$key] = CHtml::encode(strip_tags($value));
                }
            }
            return $arr;
        }
    }

    /**
     * base64_encode
     */
    static function b64encode( $string ) {
        $data = base64_encode( $string );
        $data = str_replace( array ( '+' , '/' , '=' ), array ( '-' , '_' , '' ), $data );
        return $data;
    }

    /**
     * base64_decode
     */
    static function b64decode( $string ) {
        $data = str_replace( array ( '-' , '_' ), array ( '+' , '/' ), $string );
        $mod4 = strlen( $data ) % 4;
        if ( $mod4 ) {
            $data .= substr( '====', $mod4 );
        }
        return base64_decode( $data );
    }

    /**
     * 验证邮箱
     */
    public static function email( $str ) {
        if ( empty( $str ) )
            return true;
        $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
        if ( strpos( $str, '@' ) !== false && strpos( $str, '.' ) !== false ) {
            if ( preg_match( $chars, $str ) ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 验证手机号码
     */
    public static function mobile( $str ) {
        if ( empty( $str ) ) {
            return true;
        }

        return preg_match( '#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$#', $str );
    }

    /**
     * 验证固定电话
     */
    public static function tel( $str ) {
        if ( empty( $str ) ) {
            return true;
        }
        return preg_match( '/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/', trim( $str ) );

    }

    /**
     * 验证qq号码
     */
    public static function qq( $str ) {
        if ( empty( $str ) ) {
            return true;
        }

        return preg_match( '/^[1-9]\d{4,12}$/', trim( $str ) );
    }

    /**
     * 验证邮政编码
     */
    public static function zipCode( $str ) {
        if ( empty( $str ) ) {
            return true;
        }

        return preg_match( '/^[1-9]\d{5}$/', trim( $str ) );
    }

    /**
     * 验证ip
     */
    public static function ip( $str ) {
        if ( empty( $str ) )
            return true;

        if ( ! preg_match( '#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $str ) ) {
            return false;
        }

        $ip_array = explode( '.', $str );

        //真实的ip地址每个数字不能大于255（0-255）
        return ( $ip_array[0] <= 255 && $ip_array[1] <= 255 && $ip_array[2] <= 255 && $ip_array[3] <= 255 ) ? true : false;
    }

    /**
     * 验证身份证(中国)
     */
    public static function idCard( $str ) {
        $str = trim( $str );
        if ( empty( $str ) )
            return true;

        if ( preg_match( "/^([0-9]{15}|[0-9]{17}[0-9a-z])$/i", $str ) )
            return true;
        else
            return false;
    }

    /**
     * 验证网址
     */
    public static function url( $str ) {
        if ( empty( $str ) )
            return true;

        return preg_match( '#(http|https|ftp|ftps)://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?#i', $str ) ? true : false;
    }

    /**
     * 根据ip获取地理位置
     * @param $ip
     * return :ip,beginip,endip,country,area
     */
    public static function getlocation( $ip = '' ) {
        $ip = new XIp();
        $ipArr = $ip->getlocation( $ip );
        return $ipArr;
    }

    /**
     * 中文转换为拼音
     */
    public static function pinyin( $str ) {
        $ip = new XPinyin();
        return $ip->output( $str );
    }

    /**
     * 拆分sql
     *
     * @param $sql
     */
    public static function splitsql( $sql ) {
        $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=" . Yii::app()->db->charset, $sql);
        $sql = str_replace("\r", "\n", $sql);
        $ret = array ();
        $num = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach ($queriesarray as $query) {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach ($queries as $query) {
                $str1 = substr($query, 0, 1);
                if ($str1 != '#' && $str1 != '-')
                    $ret[$num] .= $query;
            }
            $num ++;
        }
        return ($ret);
    }

    /**
     * 字符截取
     *
     * @param $string
     * @param $length
     * @param $dot
     */
    public static function cutstr( $string, $length, $dot = '...', $charset = 'utf-8' ) {
        if ( strlen( $string ) <= $length )
            return $string;

        $pre = chr( 1 );
        $end = chr( 1 );
        $string = str_replace( array ( '&amp;' , '&quot;' , '&lt;' , '&gt;' ), array ( $pre . '&' . $end , $pre . '"' . $end , $pre . '<' . $end , $pre . '>' . $end ), $string );

        $strcut = '';
        if ( strtolower( $charset ) == 'utf-8' ) {

            $n = $tn = $noc = 0;
            while ( $n < strlen( $string ) ) {

                $t = ord( $string[$n] );
                if ( $t == 9 || $t == 10 || ( 32 <= $t && $t <= 126 ) ) {
                    $tn = 1;
                    $n ++;
                    $noc ++;
                } elseif ( 194 <= $t && $t <= 223 ) {
                    $tn = 2;
                    $n += 2;
                    $noc += 2;
                } elseif ( 224 <= $t && $t <= 239 ) {
                    $tn = 3;
                    $n += 3;
                    $noc += 2;
                } elseif ( 240 <= $t && $t <= 247 ) {
                    $tn = 4;
                    $n += 4;
                    $noc += 2;
                } elseif ( 248 <= $t && $t <= 251 ) {
                    $tn = 5;
                    $n += 5;
                    $noc += 2;
                } elseif ( $t == 252 || $t == 253 ) {
                    $tn = 6;
                    $n += 6;
                    $noc += 2;
                } else {
                    $n ++;
                }

                if ( $noc >= $length ) {
                    break;
                }

            }
            if ( $noc > $length ) {
                $n -= $tn;
            }

            $strcut = substr( $string, 0, $n );

        } else {
            for ( $i = 0; $i < $length; $i ++ ) {
                $strcut .= ord( $string[$i] ) > 127 ? $string[$i] . $string[++ $i] : $string[$i];
            }
        }

        $strcut = str_replace( array ( $pre . '&' . $end , $pre . '"' . $end , $pre . '<' . $end , $pre . '>' . $end ), array ( '&amp;' , '&quot;' , '&lt;' , '&gt;' ), $strcut );

        $pos = strrpos( $strcut, chr( 1 ) );
        if ( $pos !== false ) {
            $strcut = substr( $strcut, 0, $pos );
        }
        return $strcut . $dot;
    }

    /**
     * 描述格式化
     * @param  $subject
     */
    public static function clearCutstr ($subject, $length = 0, $dot = '...', $charset = 'utf-8')
    {
        if ($length) {
            return XUtils::cutstr(strip_tags(str_replace(array ("\r\n" ), '', $subject)), $length, $dot, $charset);
        } else {
            return strip_tags(str_replace(array ("\r\n" ), '', $subject));
        }
    }

    /**
     * 检测是否为英文或英文数字的组合
     *
     * @return unknown
     */
    public static function isEnglist( $param ) {
        if ( ! eregi( "^[A-Z0-9]{1,26}$", $param ) ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 将自动判断网址是否加http://
     *
     * @param $http
     * @return  string
     */
    public static function convertHttp( $url ) {
        if ( $url == 'http://' || $url == '' )
            return '';

        if ( substr( $url, 0, 7 ) != 'http://' && substr( $url, 0, 8 ) != 'https://' )
            $str = 'http://' . $url;
        else
            $str = $url;
        return $str;

    }

    /*
        标题样式格式化
    */
    public static function titleStyle( $style ) {
        $text = '';
        if ( $style['bold'] == 'Y' ) {
            $text .='font-weight:bold;';
            $serialize['bold'] = 'Y';
        }

        if ( $style['underline'] == 'Y' ) {
            $text .='text-decoration:underline;';
            $serialize['underline'] = 'Y';
        }

        if ( !empty( $style['color'] ) ) {
            $text .='color:#'.$style['color'].';';
            $serialize['color'] = $style['color'];
        }

        return array( 'text' => $text, 'serialize'=>empty( $serialize )? '': serialize( $serialize ) );

    }

    // 自动转换字符集 支持数组转换
    static public function autoCharset ($string, $from = 'gbk', $to = 'utf-8')
    {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        if (strtoupper($from) === strtoupper($to) || empty($string) || (is_scalar($string) && ! is_string($string))) {
            //如果编码相同或者非字符串标量则不转换
            return $string;
        }
        if (is_string($string)) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($string, $to, $from);
            } elseif (function_exists('iconv')) {
                return iconv($from, $to, $string);
            } else {
                return $string;
            }
        } elseif (is_array($string)) {
            foreach ($string as $key => $val) {
                $_key = self::autoCharset($key, $from, $to);
                $string[$_key] = self::autoCharset($val, $from, $to);
                if ($key != $_key)
                    unset($string[$key]);
            }
            return $string;
        } else {
            return $string;
        }
    }

    /*
        标题样式恢复
    */
    public static function titleStyleRestore( $serialize, $scope = 'bold' ) {
        $unserialize = unserialize( $serialize );
        if ( $unserialize['bold'] =='Y' && $scope == 'bold' )
            return 'Y';
        if ( $unserialize['underline'] =='Y' && $scope == 'underline' )
            return 'Y';
        if ( $unserialize['color'] && $scope == 'color' )
            return $unserialize['color'];

    }

    /**
     * 列出文件夹列表
     *
     * @param $dirname
     * @return unknown
     */
    public static function getDir( $dirname ) {
        $files = array();
        if ( is_dir( $dirname ) ) {
            $fileHander = opendir( $dirname );
            while ( ( $file = readdir( $fileHander ) ) !== false ) {
                $filepath = $dirname . '/' . $file;
                if ( strcmp( $file, '.' ) == 0 || strcmp( $file, '..' ) == 0 || is_file( $filepath ) ) {
                    continue;
                }
                $files[] =  self::autoCharset( $file, 'GBK', 'UTF8' );
            }
            closedir( $fileHander );
        }
        else {
            $files = false;
        }
        return $files;
    }

    /**
     * 列出文件列表
     *
     * @param $dirname
     * @return unknown
     */
    public static function getFile( $dirname ) {
        $files = array();
        if ( is_dir( $dirname ) ) {
            $fileHander = opendir( $dirname );
            while ( ( $file = readdir( $fileHander ) ) !== false ) {
                $filepath = $dirname . '/' . $file;

                if ( strcmp( $file, '.' ) == 0 || strcmp( $file, '..' ) == 0 || is_dir( $filepath ) ) {
                    continue;
                }
                $files[] = self::autoCharset( $file, 'GBK', 'UTF8' );;
            }
            closedir( $fileHander );
        }
        else {
            $files = false;
        }
        return $files;
    }


    /**
     * [格式化图片列表数据]
     *
     * @return [type] [description]
     */
    public static function imageListSerialize( $data ) {

        foreach ( (array)$data['file'] as $key => $row ) {
            if ( $row ) {
                $var[$key]['fileId'] = $data['fileId'][$key];
                $var[$key]['file'] = $row;
            }

        }
        return array( 'data'=>$var, 'dataSerialize'=>empty( $var )? '': serialize( $var ) );

    }

    /**
     * 反引用一个引用字符串
     * @param  $string
     * @return string
     */
    static function stripslashes($string) {
        if(is_array($string)) {
            foreach($string as $key => $val) {
                $string[$key] = self::stripslashes($val);
            }
        } else {
            $string = stripslashes($string);
        }
        return $string;
    }

    /**
     * 引用字符串
     * @param  $string
     * @param  $force
     * @return string
     */
    static function addslashes($string, $force = 1) {
        if(is_array($string)) {
            foreach($string as $key => $val) {
                $string[$key] = self::addslashes($val, $force);
            }
        } else {
            $string = addslashes($string);
        }
        return $string;
    }

    /**
     * 格式化内容
     */
    static function formatHtml($content, $options = ''){
        $purifier = new CHtmlPurifier();
        if($options != false)
            $purifier->options = $options;
        return $purifier->purify($content);
    }

    /*******************************2013/12/13 added 搜索的时候过滤数组用的***************************************************/
    //根据当前的url生成高级搜索的相关链接
    static function filter_array($keyValue,$keyOffset,$array)
    {
        switch ($keyOffset)
        {
            case 0:
                $array['startcity'] = $keyValue;
                break;
            case 1:
                if($keyValue == 'all'){
                    $array['passcity']=array();
                    $array['passcity'][] = 'all';
                }else{
                    if(in_array('all',$array['passcity'])){
                        $array['passcity']=array();
                        $array['passcity'][] = $keyValue;
                    }elseif (!in_array($keyValue, $array['passcity'])){
                        $array['passcity'][] = $keyValue;
                    }
                }
                break;
            case 2:
                $array['code'] = $keyValue;
                break;
            case 3:
                $array['when'] = $keyValue;
                break;
            case 4:
                $array['duration'] = $keyValue;
                break;
            case 5:
                $array['language'] = $keyValue;
                break;
        }
        $senior_url = self::_doSeniorParams($array['price']);
        $array['passcity'] = implode(',',$array['passcity']);
        unset($array['price']);
        return implode(':',$array).$senior_url;
    }
    //根据当前的url,点击删除,某个噻选条件
    static function del_search($key,$array,$row='')
    {
        if($key != 'passcity'){
            $array[$key] = 'all';
        }else{
            foreach ($array['passcity'] as $k=>$v){
                if($v == $row){
                    unset($array['passcity'][$k]);
                }
            }
        }
        $senior_url = self::_doSeniorParams($array['price']);
        $array['passcity'] = implode(',', $array['passcity']);
        if(empty($array['passcity'])){
            $array['passcity'] = 'all';
        }
        unset($array['price']);
        return __PURL__.'/search/trip?path='.implode(':',$array).$senior_url;
    }
    //清除赛选条件
    static function clear_search()
    {
        $a = array_fill(0, 6, 'all');
        $senior_url = self::_doSeniorParams('all');
        return __PURL__.'/search/trip?path='.implode(':',$a ).$senior_url;
    }
    static function _doSeniorParams($price)
    {
        $str = '';
        if($price != ''){
            $str .= '&price='.$price;
        }
        return $str;
    }
    /**
     * 从一个二维数组中返回指定键的所有值
     *
     * 用法：
     * @code php
     * $rows = array(
     *     array('id' => 1, 'value' => '1-1'),
     *     array('id' => 2, 'value' => '2-1'),
     * );
     * $values = Plugin_Array::cols($rows, 'value');
     *
     * dump($values);
     *   // 输出结果为
     *   // array(
     *   //   '1-1',
     *   //   '2-1',
     *   // )
     * @endcode
     *
     * @param array $arr 数据源
     * @param string $col 要查询的键
     *
     * @return array 包含指定键所有值的数组
     */
    static function _getCols($arr, $col)
    {
        $ret = array();
        if(!empty($arr)){
            foreach ($arr as $row)
            {
                if (isset($row->$col)) { $ret[] = $row->$col; }
            }
        }
        return $ret;
    }

    /**
     * Created by zhufanmao.
     * User: ${USER}
     * Date: ${DATE} ${TIME}
     * param string 图片地址
     * @return string 返回指定图片访问地址
     */
    static function showPic($path, $type=0){
        $result = '';
        if(empty($path)){
            return $result;
        }
        $photo_url = Yii::app()->params['photoUrl'];
        if(strlen($path) == 32 && stripos($path, '.') === false){
            $result = Yii::app()->createUrl('index/show/' . $path . '?type=' . $type);
        }else{
            $result = $photo_url . $path;
        }
        return $result;
    }
    /**
     * @param $price string 价格
     * @param $currency string 价格单位
     * @param $is_flag string 是否需要价格前面的字符，例如 $和 ¥
     * @return string 返回转换之后的人民币价格
     */
    static function showPrice($price,$currency, $is_flag=1)
    {
        $env = 1;
        if($currency == 'USD'){
            //判断域名是否含有.cn.
            if(XUtils::isCn()){
                $env = 0;
                $rate = RedisApi::getRate();
                $price = ceil($price/$rate);
            }
        }else{
            if(strstr(Yii::app()->request->hostInfo,'www.') !== false){
                $rate = RedisApi::getRate();
                $price = ceil($price*$rate);
            }
        }
        if($is_flag){
            if($env == 1){
                return "$" . $price;
            }else{
                return "¥" . $price;
            }
        }else{
            return $price;
        }
    }

    //判断是否是中文站
    static function isCn(){
        if(strstr(Yii::app()->request->hostInfo,'cn.') !== false){
            return true;
        }
        return false;
    }

    //对特殊字符进行过滤防止sql注入
    public static function daddslashes($string, $force=0){
        !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
        if (!MAGIC_QUOTES_GPC || $force)
        {
            if(is_array($string)){
                $keys = array_keys($string);
                foreach($keys as $key){
                    $var = $string[$key];
                    unset($string[$key]);
                    $string[addslashes($key)] = self::daddslashes($var, $force);
                }
            }else{
                $string = trim($string);
                $string = addslashes($string);
            }
        }
        return $string;
    }
    //对html标签等进行过滤，防止xss攻击
    public static function dhtmlspecialchars($string, $flag=null){
        if(is_array($string)){
            foreach($string as $k => $v){
                $string[$k] = self::dhtmlspecialchars($v, $flag);
            }
        }else{
            $string = strip_tags($string);
            if($flag == null){
                $string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
                if(strpos($string, '&amp;#') !== false) {
                    $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
                }
            }else{
                $string = htmlspecialchars($string, $flag);
            }
        }
        return $string;
    }

    /*
     * 统一对输入进行完全过滤，输出没有html的干净的文本
     * @param string text 文本内容
     * @return string 处理后内容
     */
    public static function t($text){
        $text = nl2br($text);
        $text = trim($text);
        return $text;
    }

    //发生支付行为的未登录用户,自动注册并且获取一些信息
    public static function autoSign($username, $data, $is_wap=0){
        if(!$username){
            return false;
        }
        $dbapi = new DbApi();
        //注册,pc需要提取一些必要的信息
        if($is_wap == 0){
            $firstname = $data['userinfo']['contact']['firstName'];
            $lastname  = $data['userinfo']['contact']['lastName'];
            $country   = $data['userinfo']['contact']['country'];
            $phone 	   = $data['userinfo']['contact']['takeThePhone'];
            $birthday  = '';
            $gender    = '';
            //遍历获取  注册的人的性别和生日
            foreach ($data['userinfo']['passengers'] as $person){
                if($person['firstName'] == $firstname && $person['lastName'] == $lastname){
                    $birth  = $person['birthday']; //将格式为月日年2/8/1978  处理成 1978-04-04 00:00:00
                    $birth  = explode( '/', $birth );
                    if( strlen($birth[0]) == 1){ $birth[0] = '0'.$birth[0]; }
                    if( strlen($birth[1]) == 1){ $birth[1] = '0'.$birth[1]; }
                    $birthday  = $birth[2] .'-'. $birth[0] .'-'. $birth[1] .' 00:00:00';
                    $gender = $person['gender'];  //性别
                    $age_region = substr( $birth[2], 2, 1 ).'0';
                    break;
                }
            }
            $sql = "insert into tour_user (username, password, firstname, lastname, sex, birthday, nationality, contact_phone, age_region, account_from, last_login_time, last_login_ip, login_times, is_active, login_salt)
                    values(:username, :password, :firstname, :lastname, :sex, :birthday, :nationality, :contact_phone, :age_region, :account_from, :last_time, :last_login_ip, 1, 1, :login_salt)";
            $login_salt = md5(time());  //兼容正常注册流程
            $passwd = rand(100000, 999999);
            $dbapi->execute($sql, array(":username" => $username, ":last_time" => date("Y-m-d H:i:s"), ":last_login_ip" => XUtils::getClientIP(),
                                        ":password" => md5(substr(md5($login_salt . $passwd), 0, 10)), ":login_salt" => $login_salt, ':firstname' => $firstname,
                                        ':lastname' => $lastname, ':sex' => $gender, ':birthday' => $birthday, ':nationality' => $country, ':contact_phone' => $phone,
                                        ':age_region' => $age_region, ':account_from' => 'A'));
        }else{
            $sql = "insert into tour_user (username, password, firstname, last_login_time, last_login_ip, login_times, is_active, login_salt) values(:username, :password, ' ', :last_time, :last_login_ip, 1, 1, :login_salt)";
            $login_salt = md5(time());  //兼容正常注册流程
            $passwd = rand(100000, 999999);
            $dbapi->execute($sql, array(":username" => $username, ":last_time" => date("Y-m-d H:i:s"), ":last_login_ip" => XUtils::getClientIP(), ":password" => md5(substr(md5($login_salt . $passwd), 0, 10)), ":login_salt" => $login_salt));
        }
        $uid = $dbapi->getLastInsertId();
        $mail = new TXMail();
        $mail->address = $username;
        $mail->forAutoRegister($passwd);
        return $uid;
    }
    //对象转换成数组
    public static function objToArr($obj){
        if(is_object($obj)){
            $obj = get_object_vars($obj);
        }
        $ret = array();
        if(!$obj) {
            return $ret;
        }
        foreach($obj as $key =>$value){
            if(is_object($value) || is_array($value)){
                $ret[$key] = self::objToArr($value);
            }else{
                $ret[$key] = $value;
            }
        }
        return $ret;
    }
    //记录前端日志
    public static function writeJsLog($info, $line, $log_type="redis") {
        if(!$info || !$line) return false;
        $server_addr = $_SERVER['SERVER_ADDR'];
        $ip = XUtils::getClientIP();
        $errfile = isset($info['errfile']) && $info['errfile'] ? $info['errfile'] : '';
        $url = isset($info['url']) && $info['url'] ? $info['url'] : '';
        if($log_type == 'redis') {
            $arr = array(
                'server' => $server_addr,
                'ip'      => $ip,
                'url' => $url,
                'errfile'     => $errfile,
                'file'    => $errfile,
                'line'    => $line,
                'info'    =>is_string($info) ? $info : var_export($info, true),
                'ctime'   => date('Y-m-d H:i:s'),
                'type'    => 'javascript',
            );
            $key = "nova2usa_list_log";
            Yii::app()->cache->lpush($key, serialize($arr));
        } else {
            $content = "\n[" . Date('Y-m-d h:i:s'). "]\n";
            $content .= "报错网址:{$url}\n";
            $content .= "报错文件:{$errfile}\n";
            $content .= "错误信息:{$info['msg']}\n";
            $content .= "报错行号:{$line}\n";
            $content .= "客户端浏览器环境:{$info['info']}\n";
            $dir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;
            file_put_contents($dir .  'client_js_log.log', $content, FILE_APPEND);
        }
    }
    //记录信息的分布
    public static function writeLog($info, $type='info', $log_type='redis'){
        $debug = debug_backtrace();
        if(count($debug) > 1){
            $debug = $debug[1];
        }else{
            $debug = $debug[0];
        }
        $time = date('Y-m-d H:i:s');
        $ip = XUtils::getClientIP();
        $url = $_SERVER['REQUEST_URI'];
        $file_name = isset($debug['file']) ? $debug['file'] : '';
        $line = isset($debug['line']) ? $debug['line'] : '';
        $function = isset($debug['function']) ? $debug['function'] : '';
        $info = is_string($info) ? $info : var_export($info, true);
        $server_addr = $_SERVER['SERVER_ADDR'];

        if($log_type == 'file'){
            $str = 'Time:' . $time . '| ' .
                'Server:' . $server_addr . '| ' .
                'IP:' . $ip . '| ' .
                'URL:' . $url . '| ' .
                'File:' . $file_name . '| ' .
                'Line:' . $line . '| ' .
                'Function:' . $function . '| ' .
                'Info:' . $info . "\n";
            $dir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;
            file_put_contents($dir . $type . '.log', $str, FILE_APPEND);
        }elseif($log_type == 'redis'){
            $arr = array(
                'server' => $server_addr,
                'ip'      => $ip,
                'url'     => $url,
                'file'    => $file_name,
                'line'    => $line,
                'info'    => $info,
                'ctime'   => $time,
                'type'    => $type,
            );
            $key = "nova2usa_list_log";
            Yii::app()->cache->lpush($key, serialize($arr));
        }

    }
    //判断数组是否为空
    public static function isnull($arr){
        if(empty($arr)){
            return true;
        }
        foreach($arr as $v){
            if($v){
                return false;
            }
        }
        return true;
    }
    //判断是移动端还是pc端
    public static function isFromMobile() {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }//如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        elseif (isset ($_SERVER['HTTP_VIA'])) {
            //找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }elseif (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array (
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        return false;
    }

    //根据tourcode获取产品tourname
    public static function getTourname($tourcode){
        if(!$tourcode){
            return;
        }
        $redis = new RedisApi();
        $data = $redis->getInstr($tourcode);
        if($data){
            return $data[0]->tourname;
        }
    }

    //根据tourcode获取产品的行程特色的标签
    //已废弃
    public static function getTourspe($tourcode){
        if(!$tourcode){
            return;
        }
        $redis = new RedisApi();
        $redis_result = $redis->getData();
        /*行程特色的标签*/
        $tags = array();
        foreach ($redis_result as $val){
            foreach ($val->tours as $tour){
                if($tour->tourcode == $tourcode){
                    foreach ($tour->special as $spe){
                        $tags[] = $spe->name;
                    }
                    break(2);
                }
            }
        }
        //去重
        $taglist = array_unique($tags);
        return $taglist;
    }
    /**
     * 处理图片
     */
    public static function handlePic($src){
        $product = new TXProduct();
        $logsrc = $product->img_from($src);
        if ($logsrc === 1) {
            $src = '/content/show?md5='.$src; //本地
        }elseif($logsrc === 0){
            $src = '';
        }
        return $src;
    }

    /**
     * XML:站点地图
     */
    public function sitemap(){
        $redis = new RedisApi();
        $ar = $redis->getTourArr();
        $content = '';	//链接池
        $start = <<<start
<?xml version="1.0" encoding="UTF-8"?>
<urlset>
start;
        foreach ($ar as $k=>$v){
            $lastmod = substr($v['mtime'], 0, 10);
            $content .= <<<url
<url>
	<loc>http://www.ituxing.com/trip/detail/{$k}.html</loc>
	<lastmod>{$lastmod}</lastmod>
	<changefreq>daily</changefreq>
	<priority>0.5</priority>
</url>
url;
        }

        $end = <<<end
</urlset>
end;
        $filename="sitemap.xml";
        $fp=fopen("$filename", "w+"); //打开文件指针，创建文件
        if ( !is_writable($filename) ){
            die("文件:" .$filename. "文件不可写，请检查！");
        }
        $data = $start.$content.$end;
        file_put_contents ($filename, $data);
        fclose($fp);  //关闭指针
    }
    //二维数据排序
    //field 排序字段 flag是否倒序
    public static function array_sort($arr, $field, $flag=0, $json=0){
        $sort_tmp = array();
        $arr_tmp  = array();
        if($json == 1){
            foreach($arr as $k=>$v){
                if(!isset($v->$field)){
                    $sort_tmp[$k] = 0;
                }else{
                    $sort_tmp[$k] = $v->$field;
                }
            }
        }else{
            foreach($arr as $k=>$v){
                if(!isset($v[$field])){
                    $sort_tmp[$k] = 0;
                }else{
                    $sort_tmp[$k] = $v[$field];
                }
            }
        }
        asort($sort_tmp);
        foreach($sort_tmp as $key => $value){
            $arr_tmp[] = $arr[$key];
        }
        return $flag ? array_reverse($arr_tmp) : $arr_tmp;
    }

    /**
     * 检查是否为robot机器人
     *
     */
    public static function checkrobot($useragent = '') {
        static $kw_spiders = array('bot', 'crawl', 'spider' ,'slurp', 'sohu-search', 'lycos', 'robozilla');
        static $kw_browsers = array('msie', 'netscape', 'opera', 'konqueror', 'mozilla');

        $useragent = strtolower(!empty($useragent) ? $useragent : (isset($_SERVER['HTTP_USER_AGENT'])? $_SERVER['HTTP_USER_AGENT'] : ''));
        if(strpos($useragent, 'http://') === false && self::dstrpos($useragent, $kw_browsers)) return false;
        if(self::dstrpos($useragent, $kw_spiders)) return true;
        return false;
    }

    /**
     *
     * 重写strpos方法，用于搜索的字符存在于数组的某个值中
     * $string:搜索的字符
     * $arr：被搜索的数组
     * $returnvalue : 如果命中搜索是否，是否返回命中的值。默认返回true,false
     * @return string|boolean
     */
    public static function dstrpos($string, &$arr, $returnvalue = false) {
        if(empty($string)) return false;
        foreach((array)$arr as $v) {
            if(strpos($string, $v) !== false) {
                $return = $returnvalue ? $v : true;
                return $return;
            }
        }
        return false;
    }

    /**
     * 获得页面需要渲染的js和css
     */
    public static function getJsCss($isDevEnviroment , $page) {
        $data = array();
        $config = Yii::app()->params['jscss'][$page];
        if ($isDevEnviroment){
            $data['css'] = isset($config['css']['dev']) ? $config['css']['dev'] : array();
            $data['js'] = isset($config['js']['dev']) ? $config['js']['dev'] : array();
        }else{
            $data['css'] = isset($config['css']['min']) ? $config['css']['min'] : array();
            $data['js'] = isset($config['js']['min']) ? $config['js']['min'] : array();
        }
        return $data;
    }
    
    public static function getUser($username) {
    	$db = new DbApi();
    	$sql = "select userid,username from tour_user where username = :username";
    	$data = $db->getRow($sql, array(':username'=>$username));
    	return $data;
    }
    /**
     * 设置渠道cookie
     */
    public static function setOrderCookie(){
        //设置cookie
        do{
            if(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']){
                $qstr = $_SERVER['QUERY_STRING'];
                if(($pos = stripos($qstr, 'fromid=')) !== false){
                    $fid = substr($qstr, $pos+7);
                    if(!is_numeric($fid)){
                        if(preg_match('/(^[0-9]+)/', $fid, $match)){
                            $fid = $match[1];
                        }else{
                            break;
                        }
                    }
                    $fcookie = new CHttpCookie('fromuid', $fid);
                    $fcookie->expire = time()+3600*24*30*12;
                    Yii::app()->request->cookies['fromuid'] = $fcookie;
                    return;
                }elseif(($pos = stripos($qstr, 'ztid=')) !== false){
                    $fid = substr($qstr, $pos+5);
                    if(!is_numeric($fid)){
                        if(preg_match('/(^[0-9]+)/', $fid, $match)){
                            $fid = $match[1];
                        }else{
                            break;
                        }
                    }
                    $fcookie = new CHttpCookie('ztuid', $fid);
                    $fcookie->expire = time()+3600*24*30*12;
                    Yii::app()->request->cookies['ztuid'] = $fcookie;
                    return;
                }
                break;
            }else{
                break;
            }
        } while (1);
        //判断是否搜索引擎过来
        if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']){
            $refer = $_SERVER['HTTP_REFERER'];
            //判断是否来自于百度
            if(stripos($refer, 'www.baidu.com') !== false){
                $fcookie = new CHttpCookie('fromuid', '200');
                $fcookie->expire = time()+3600*24*30*12;
                Yii::app()->request->cookies['fromuid'] = $fcookie;
            }elseif(stripos($refer, 'www.google.') !== false){  //是否来自于google
                $fcookie = new CHttpCookie('fromuid', '300');
                $fcookie->expire = time()+3600*24*30*12;
                Yii::app()->request->cookies['fromuid'] = $fcookie;
            }elseif(stripos($refer, 'www.so.com') !== false){   //是否来自于360
                $fcookie = new CHttpCookie('fromuid', '400');
                $fcookie->expire = time()+3600*24*30*12;
                Yii::app()->request->cookies['fromuid'] = $fcookie;
            }
        }
    }
    
}
?>
