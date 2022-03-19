/* Testing Purpose 19032022 */

<script type="text/babel">
    let preset = branch_ref != '' ? true : false

    autoHideLoadingOnInit = false;

    $("#btn-pay-now").click(function () {
        showLoading();

        let millis = Date.now()
        let link = "sf="+$(this).data('payment_form')+"&ti="+millis;
        let links = "<?=base_url()?>payment/checkout?li="+convertString(link);

        let newWindow = open(links, 'Payment', 'width=600,height=600')
        newWindow.focus();
    });

    $("#btn-confirm-submit").click(function () {
        showLoading();
        $("#btn-toggle-modalformanswer").click();
        files = [];
        var data = generateAnswer();
        ajaxRequest({
            url: '<?=base_url()?>_SubmitPayment',
            data: {
                Type: Type_of_Payment,
                questions: data,
                files: files
            },
            method: 'post',
            success: function (json) {
                var status = json.status;
                if (status == <?=RESPONSE_STATUS_SUCCESS?>) {

                    var price = "RM0";
                    if (Type_of_Payment == 'Test')  price = "RM0";
                    $("#btn-pay-now").data('payment_form', json.data.payment_form);
                    $('#div-payment .s-payment-amount').html(price);
                    $('#div-payment').fadeIn();
                    checkPaymentStatus(json.data.payment_form);
                    autoCacheTimer = false;

                } else {
                  //  alert(JSON.stringify(json.data.msg) ?? 'Payment submission fail. Please try to resubmit again.')
                    hideLoading();
                }
            }
        })
    })

    function onCompletePayment(){
        $('#div-success').fadeIn();
    }

    function checkPaymentStatus(paymentd) {
        var timer = setTimeout(function () {
            ajaxRequest({
                url: "<?=base_url()?>payment/_checkStatus",
                method: 'post',
                data: {'payment_form_id': payment_form_id},
                success: function (json) {
                    var data = json.data[0];
                    // console.log(json);
                    if (data.payment_payment_code != '') { //Indicate Payment Success
                        hideLoading();
                        clearTimeout(timer);
                        onCompletePayment();
                    } else {
                        checkPaymentStatus(payment_form_id);
                    }
                }
            });
        },3000);
    }

    window.addEventListener("storage", function (e) {
        if (
            e.key == "payment_success" &&
            e.newValue == 1) {
            localStorage.removeItem("payment_success");
            $('#div-payment').fadeOut();
            $('#div-success').fadeIn();
        }else if(e.key == "payment_success" &&
            e.newValue == 0){
            let newWindow = open('<?=base_url()?>/payment/checkout?sf=' + $('#btn-pay-now').data('payment_form'), 'Payment', 'width=600,height=600')
            newWindow.focus();

        }

    })




</script>
