<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	private $token;

	function __construct(){
		parent::__construct();
		$this->load->model('Qrcode_model', 'Qrcode');
	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		
		$id = $this->input->get("id");

		$exit = $this->Qrcode->get($id);

		if (empty(exit)) {
			echo "无效的二维码";
			return;
		}

		$sign = $this->getSignPackage($id);

		// print_r($sign);

		if (!empty(exit) && $exit->has_record) { //播放录音


			$this->load->view('play', array("record_path" => $exit->record_path));
		} else { // 进行录音
			$this->load->view('index', array("sign" => $sign, "qrcodeId" => id));
		}

	}

	public function uploadVoice() {
		$serverId = $this->input->post("serverId");
		$qrcodeId = $this->input->post("qrcodeId");

		$res = array("code" => -1);

		$exit = $this->Qrcode->get($qrcodeId);
		if (empty($exit)) {
			
		} else {
			$path = "./record/{$serverId}.mp3";
			copy("http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".$this->token."&media_id={$serverId}", $path);
			$exit->record_path = substr ($path, 1);
			$cnt = $this->Qrcode->update(json_decode(json_encode($exit), true));
			if ($cnt > 0) {
				$res['code'] = 0;
			}
		}
		echo json_encode(res);
		return;
	}


	private function getAccessToken()
    {
 
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".'wx1efb2e01089bc47c'."&secret=".'6317e68bdd96c40fa9b345e130b8ac02';
        // 微信返回的信息
        $returnData = json_decode($this->curlHttp($url));
		$resData['accessToken'] = $returnData->access_token;
		$this->token = $resData['accessToken'];
        $resData['expiresIn'] = $returnData->expires_in;
        $resData['time'] = date("Y-m-d H:i",time());
 
        $res = $resData;
        return $res;
    }
 
    private function curlHttp($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($curl, CURLOPT_TIMEOUT, 500 );
        curl_setopt($curl, CURLOPT_URL, $url );
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
	}
	
	private function getJsApiTicket($accessToken) {
 
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$accessToken&&type=jsapi";
        // 微信返回的信息
        $returnData = json_decode($this->curlHttp($url));
 
        $resData['ticket'] = $returnData->ticket;
        $resData['expiresIn'] = $returnData ->expires_in;
        $resData['time'] = date("Y-m-d H:i",time());
        $resData['errcode'] = $returnData->errcode;
 
        return $resData;
	}
	
	private function getSignPackage($id) {
        // 获取token
        $token = $this->getAccessToken();
        // 获取ticket
        $ticketList = $this->getJsApiTicket($token['accessToken']);
        $ticket = $ticketList['ticket'];
        
        // 该url为调用jssdk接口的url
        $url = 'http://sheaned.com/index.php/welcome/index/id/' . $id;
        // 生成时间戳
        $timestamp = time();
        // 生成随机字符串
        $nonceStr = $this->createNoncestr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序 j -> n -> t -> u
        $string = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = array (
            "appId" => 'wx1efb2e01089bc47c',
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string,
            "ticket" => $ticket,
            "token" => $token['accessToken']
        );
 
        // 返回数据给前端
		// echo json_encode($signPackage);
		return $signPackage;
    }
 
    // 创建随机字符串
    private function createNoncestr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for($i = 0; $i < $length; $i ++) {
            $str .= substr ( $chars, mt_rand ( 0, strlen ( $chars ) - 1 ), 1 );
        }
        return $str;
    }




}
