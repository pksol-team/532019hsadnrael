<script>
      if (typeof jQuery == 'function'){
            jQuery(function(){
                  <?php if (Swift_Performance::check_option('ajaxify-placeholder', 'blur')):?>
                  jQuery("<?php echo implode(',', $ajaxify); ?>").css({"filter": "blur(5px)", "pointer-events": "none"});
                  <?php elseif (Swift_Performance::check_option('ajaxify-placeholder', 'hidden')):?>
                  jQuery("<?php echo implode(',', $ajaxify); ?>").css({"opacity": "0", "pointer-events": "none"});
                  <?php endif;?>

                  jQuery.get(document.location.href + (document.location.href.match(/\?/) ? '&' : '?') + 'nocache=' + Math.random(), function(response){
                        <?php foreach ($ajaxify as $item) :?>
                              jQuery("<?php echo $item?>").each(function(i){
                                    jQuery(this).replaceWith(jQuery(response).find("<?php echo $item?>").get(i));
                              });
                              jQuery("<?php echo $item?>").trigger('swift-performance-ajaxify-item-done');
                        <?php endforeach; ?>
                        jQuery('body').trigger('swift-performance-ajaxify-done');
                  });
            });
      }
</script>