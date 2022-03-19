/* Testing Purpose 19032022 */

<html>
  <body>
    <script>
      localStorage.removeItem("payment_success");
      localStorage.setItem("payment_success", <?=$completed ? 1 : 0?>);
      window.close();
    </script>
  </body>
</html>
