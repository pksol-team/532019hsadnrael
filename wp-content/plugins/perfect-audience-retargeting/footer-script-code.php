<script type="text/javascript">
  (function() {
    window._pa = window._pa || {};
    // _pa.orderId = "myUser@email.com"; // OPTIONAL: include your user's email address or order ID
    // _pa.revenue = "19.99"; // OPTIONAL: include dynamic purchase value for the conversion
    // _pa.onLoadEvent = "sign_up"; // OPTIONAL: name of segment/conversion to be fired on script load
    var pa = document.createElement('script'); pa.type = 'text/javascript'; pa.async = true;
    pa.src = ('https:' == document.location.protocol ? 'https:' : 'http:') + "//tag.marinsm.com/serve/<?php echo $site_id ?>.js";
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(pa, s);
  })();
</script>