/* Testing Purpose 19032022 */

<?php
defined("BASEPATH") or exit("No direct script access allowed");

class ctrl_payment extends MY_Base_Controller
{
	public function checkout()
	{
		$params = $this->input->get();
        $submission_form_id = $params['li'];
        $getparams = base64_decode($payment_form_id);
        $arrays = explode("&",$getparams);

        if(count($arrays)>0){
            $subid= str_replace('sf=','',$arrays[0]);
            $time= str_replace('ti=','',$arrays[1]);
            $time_now = round(microtime(true)*1000);


            if($time_now - $time > 7200000){
                echo "Payment link expired, please seek help from your our help desk";
            }else{
                $this->load->view("vw_payment_checkout", [
                    'payment_form' => $subid ?? "",
                    'csrf_name' => $this->security->get_csrf_token_name(),
                    'csrf_token' => $this->security->get_csrf_hash(),
                ]);
            }
        }else{
            echo "Invalid payment link";
        }
	}

    public function ajax_checkout()
    {
        $this->validateAjaxRequest();

        $data = $this->input->post();
        $this->load->model("mdl_payment");
        $result = $this->mdl_payment->create(
            $data["payment_form"] ?? "",
        );
        if ($result["status"] == RESPONSE_STATUS_SUCCESS) {

            $result_content = [
                "payment_buyerId" => $data["payment_form"]
            ];

            $form = $result["data"]["form"];
            $form["ServerResponseURL"] = base_url() . "payment/ResultResponse";      
           
            $form["ServerResponseContent"] = htmlentities(
                json_encode([
                "payment_buyerId" => $data["payment_form"],
            ])
            );
            $form["ServerResponseMethod"] = "POST";

			
            $form["RedirectResultURL"] = base_url() . "payment/response";
            $form["RedirectResultContent"] = htmlentities(
                json_encode([
                    "payment_buyerId" => $data["payment_form"],
                    "payment_paymentTxnId" => time(),
                    "completed" => true,
                ])
            );
            $form["RedirectResultMethod"] = "POST";


            $form["RedirectTerminateURL"] = base_url() . "payment/response";
            $form["RedirectTerminateContent"] = htmlentities(
                json_encode([
                    "payment_buyerId" => $data["payment_form"],
                    "payment_paymentTxnId" => time(),
                    "completed" => false,
                ])
            );
            $form["RedirectTerminateMethod"] = "POST";


//            $form["ReturnResult"] = 'payment_debitAuthCode|payment_sellerExOrderNo|payment_sellerOrderNo|payment_paymentTxnId';
            $form["ReturnResult"] = 'payment_debitAuthCode|payment_sellerExOrderNo|payment_paymentTxnId|payment_txnAmount|payment_buyerName';
            $result["data"]["form"] = $form;
     

        }
        $this->ajaxResponse($result);
    }

    public function response()
    {
        $data = $this->input->post();
        log_message('debug','PAYMENT Resp : '.json_encode($data));

        $txtContent = json_decode($data["txtContent"], true);
        $payment_form = $txtContent['payment_buyerId'];
        $completed = $txtContent['completed'] ?? false;
		

        $this->load->view("vw_payment_response", [
            'completed' => $completed,
        ]);
    }

public function ResultResponse()
	{
	
		$input_data = json_decode($this->input->raw_input_stream, true)[0];
		log_message("debug","Payment Result Response Callback : ".json_encode($input_data) );

		$txtContent = $input_data["txtContent"];
		$txtContent = explode("^_^", $txtContent, 2);
		$myData = $txtContent[0] ?? "{}";
        $paymentData = json_decode($txtContent[1],true) ?? json_decode("{}",true);
		$completed = false;
		
		 if (isset($paymentData['payment_debitAuthCode'])) {
			 if($paymentData['payment_debitAuthCode'] == '00') $completed = true;
			 else $completed = false;   
			}
		
        $paymentData['payment_complete'] = $completed;

		
         $this->load->model("mdl_payment");
         $result = $this->mdl_payment->callback(json_decode($myData,true)['payment_buyerId'], $paymentData);
         log_message(
                "debug",
                "ctrl_payment->response() " . json_encode($paymentData)
            );
        

		$this->load->view("vw_payment_response", [
			'completed' => $completed ?? false,
		]);
	}


    public function check()
    {
        $data = $this->input->post();
        $payment_form = $data['payment_form_id'];

        $this->load->model("mdl_payment");
        $result = $this->mdl_payment->check($payment_form);
        $this->ajaxResponse($result);


    }

    public function checkAPI()
    {
        $data = $this->input->get();
        $payment_form = $data['payment_form_id'];

        $this->load->model("mdl_payment");
        $result = $this->mdl_payment->checkAPI($payment_form);
        $this->ajaxResponse($result);
    }

    public function paymentRoutineCheck()
    {

        $this->load->model("mdl_payment");
        $result = $this->mdl_payment->paymentRoutineCheck();
        $this->ajaxResponse($result);
    }
}
