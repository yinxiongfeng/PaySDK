<?php
require __DIR__ . '/common.php';

// 公共配置
$params = new \Yurun\PaySDK\Weixin\Params\PublicParams;
$params->appID = $GLOBALS['PAY_CONFIG']['appid'];
$params->mch_id = $GLOBALS['PAY_CONFIG']['mch_id'];
$params->key = $GLOBALS['PAY_CONFIG']['key'];

// SDK实例化，传入公共配置
$sdk = new \Yurun\PaySDK\Weixin\SDK($params);

class PayNotify extends \Yurun\PaySDK\Weixin\Notify\PayMode1
{
	/**
	 * 后续执行操作
	 * @return void
	 */
	protected function __exec()
	{
		// 处理
		file_put_contents(__DIR__ . '/paymode1_notify_result.txt', date('Y-m-d H:i:s') . ':' . var_export($this->data, true));
		// 支付接口
		$request = new \Yurun\PaySDK\Weixin\Native\Params\Pay\Request;
		$request->body = 'test';
		$request->out_trade_no = 'test' . mt_rand(10000000,99999999);
		$request->total_fee = 1;
		$request->spbill_create_ip = '127.0.0.1';
		$request->notify_url = $GLOBALS['PAY_CONFIG']['pay_notify_url'];

		// 调用接口
		try{
			$data = $this->sdk->execute($request);
			$this->replyData->appid = $this->sdk->publicParams->appID;
			$this->replyData->mch_id = $this->sdk->publicParams->mch_id;
			$this->replyData->nonce_str = $data['nonce_str'];
			$this->replyData->prepay_id = $data['prepay_id'];
			$this->replyData->result_code = 'SUCCESS'; // 交易是否成功
			// $this->replyData->err_code_des = ''; // 错误信息
			$this->reply('SUCCESS');
		}catch(Exception $e){
			$this->reply('FAIL', $e->getMessage());
		}
	}
}
$payNotify = new PayNotify;
try{
	$sdk->notify($payNotify);
}catch(Exception $e){
	file_put_contents(__DIR__ . '/notify_result.txt', $e->getMessage() . ':' . var_export($payNotify->data, true));
}