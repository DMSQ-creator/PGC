<?php
include("include/class.phpmailer.php");
include("include/class.smtp.php"); 
include("IpLocation.php");
include("include/function.base.php");

//你只需填写以下信息即可****************************

$smtp = "smtp.xxx.com";//必填，设置SMTP服务器 QQ邮箱是smtp.qq.com ，QQ邮箱默认未开启，请在邮箱里设置开通。网易的是 smtp.163.com 或 smtp.126.com

$youremail =  'xxxxxxxx@xxx.com'; // 必填，开通SMTP服务的邮箱；也就是发件人Email。(本系统功能也就是自己给自己发邮件)

$password = "password"; //必填， 以上邮箱对应的密码

$ymail = "xxxxxxxx@xxx.com"; //收信人的邮箱地址，也就是你自己收邮件的邮箱

$yname = "xxxx"; //收件人称呼

$url = $_SERVER["HTTP_REFERER"];//取得来源网址



function get_ip(){
   if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
       $ip = getenv("HTTP_CLIENT_IP");
   else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
       $ip = getenv("HTTP_X_FORWARDED_FOR");
   else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
       $ip = getenv("REMOTE_ADDR");
   else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
       $ip = $_SERVER['REMOTE_ADDR'];
   else
       $ip = "unknown";
   return($ip);
}



if($_SERVER['REQUEST_METHOD']=='POST'){

$username		=	isset($_POST['name'])?$_POST['name']:null;
$useremail		=	isset($_POST['email'])?$_POST['email']:null;
$userinquiry	=	isset($_POST['content'])?$_POST['content']:null;
$captcha		=	isset($_POST['g-recaptcha-response'])?$_POST['g-recaptcha-response']:null;
	
	
	check(array(

                // 用户名不能为空

                array('name','VALIDATE_EMPTY','The username field is empty.'),

                // 用户名长度必须是2-30个字符

                array('name','VALIDATE_LENGTH','The username field length must be %d-%d characters.',2,30),

				

            ));
			
	//如果有邮箱，验证它

	if($useremail!=null)

		check(array(

                array('email','VALIDATE_EMPTY','Please enter an e-mail address.'),

                array('email','IS_EMAIL','You must provide an correct e-mail address.')

            ));

	// 验证留言内容

    check(array(

                array('content','VALIDATE_EMPTY','Please input your inquiry.'),

				array('content','VALIDATE_HAVE_LINK','The content can not contain links.'),

                array('content','VALIDATE_LENGTH','The content field length must be %d-%d characters.',5,2000)

            ));
	
	// 验证谷歌验证码 申请地址：https://www.google.com/recaptcha/admin
	if(!$captcha){
        echo "<script>alert('Please check the the captcha form.');window.history.back(-1);</script>";die;
          //echo '<h2>Please check the the captcha form.</h2>';
		  //echo '<p class="step"><a href="javascript:history.back(-1);" class="button"><< Back</a></p>';
          //exit;
        }
	$secretKey = "6Lf7OKshAAAAAFWtm_fuMIBV4NErLTVdEdElx2pB";//密钥
	$ip = $_SERVER['REMOTE_ADDR'];
        $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secretKey."&response=".$captcha."&remoteip=".$ip);
	$responseKeys = json_decode($response,true);
		if(intval($responseKeys["success"]) !== 1) {
		  //echo "<script>alert('You are spammer ! Get the @$%K out')</script>";
		  echo "<script>alert('You are spammer ! Get the @$%K out');window.history.back(-1);</script>";die;
          //echo '<h2>You are spammer ! Get the @$%K out</h2>';
		  //echo '<p class="step"><a href="javascript:history.back(-1);" class="button"><< Back</a></p>';
		  //exit;
        }
		
	$mail = new PHPMailer();

	$mail->CharSet ='utf-8'; //编码要指定 
	$mail->Encoding = 'base64'; //加密方式
	$mail->SMTPSecure = "ssl";  //解决25端口被屏蔽 参考源：https://github.com/LuhangRui/phpmailer/blob/master/example.php
	$mail->IsSMTP();
	$mail->Port=465; //使用端口
	$mail->SMTPAuth = true; 
	$mail->Host = $smtp; 


	$mail->Username = $youremail; 
	$mail->Password = $password; //必填， 以上邮箱对应的密码

	$mail->From = $youremail; 
	$mail->FromName = "xxxx"; //发件人名称

	$mail->AddAddress($ymail,$yname);
	
	$userinquiry=str_replace(" "," ",str_replace("\r\n","<br/>",$_POST['content']));//回车转成换行
	$ip1 = get_ip();
	
	//echo $ip1;
		
	//$baidu="<a href='https://www.baidu.com/s?wd=".$ip1."'>";//百度查询ip地址国家地区
	$iplocation = new IpLocation();     //实例化IpLocation()类
	$location = $iplocation->getlocation($ip1);  	  //查询 
	//echo $location;
	$ct=$location['country'];//获取城市
	$area=$location['area'];//获取运营商
	
	$mail->Subject = $username." -【网站询盘】"; //邮件标题
	date_default_timezone_set('Asia/Shanghai');
	$time = date("Y-m-d H:i:s",time());
	
	$htmlcss="<div style='border: #93b5ff 1px solid;padding:10px;background-color: #e5fbe9;width:50%;margin-left:auto;margin-right:auto;'>";	
	
	//$html = $htmlcss.'Name：'.$yourname.'<br>Tel：'.$tel.'<br>QQ：'.$qq.'<br>Email：'.$email.'<br>IP：'.$ip.'<br>Time：'.$time.'<br>Content：<br>'.$message."</div>";	
	//$html = $htmlcss.'Name：'.$username.'<br>Email：'.$useremail.'<br>IP：'.$ip1.'<br>Location：'.$ct.' '.$area.'<br>Time：'.$time.'<br>Content：<br>'.$userinquiry.'<br>来源网址：'.$url."</div>";
	$html = $htmlcss.'<table border="0" cellspacing="0" cellpadding="4" style="width:100%;table-layout:fixed;"><tr><td style="width:5em;">Name:</td><td>'.$username.'</td></tr><tr><td style="width:5em;">Email:</td><td>'.$useremail.'</td></tr><tr><td style="width:5em;">IP:</td><td>'.$ip1.'</td></tr><tr><td style="width:5em;">Location:</td><td>'.$ct.' '.$area.'</td></tr><tr><td style="width:5em;">Time:</td><td>'.$time.'</td></tr><tr><td style="width:5em;">来源网址:</td><td>'.$url.'</td></tr><tr><td style="width:5em;">Content:</td><td style="word-wrap:break-word;">'.$userinquiry.'</td></tr></table>'."</div>";
	
	$mail->MsgHTML($html);
	
	$mail->IsHTML(true); 

	if(!$mail->Send()) {
		header("Content-Type: text/html; charset=utf-8");

		echo '<script>alert("Submission failed!");history.go(-1);</script>';
	} else {
		header("Content-Type: text/html; charset=utf-8");
	    echo '<script>alert("We will get back to you within one business day, thank you!");history.go(-1);</script>';
	}


}
?>
