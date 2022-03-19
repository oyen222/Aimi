/* Testing Purpose 19032022 */

<html>
  <body>
    <script defer src="<?=base_url()?>assets/js/common/jquery.min.js"></script>
    <script src="<?=base_url()?>assets/js/common/polyfill.min.js"></script>
    <script src="<?=base_url()?>assets/js/common/babel.min.js"></script>
    <script type="text/babel">
      function checkout(payment_form) {
        $.ajax(
          {
            url: "<?=base_url()?>payment/_checkout",
            data: {
              'payment_form': payment_form,
              '<?=$csrf_name?>': '<?=$csrf_token?>'
            },
            method: 'post',
            success: function(json)
            {
              var status = json.status;
              var data = json.data;

              if (status == <?=RESPONSE_STATUS_SUCCESS?>) {
                var newForm = $('<form>', {
                  'action': data.url,
                  'method': 'POST'
                });
                $.each(data.form, function(index, i) {
                  newForm.append('<input name="' + index + '" value="' + i + '">');
                })
                newForm.appendTo('body');
                newForm.hide();
                newForm.submit();
              } else {
                alert(JSON.stringify(data));
                window.close();
              }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
              alert('Something wrong. Please try again');
              window.close();
            }
          }
        );
      }

      checkout('<?=$payment_form?>')
    </script>
  </body>
</html>