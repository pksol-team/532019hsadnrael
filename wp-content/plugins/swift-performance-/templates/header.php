<?php defined('ABSPATH') or die("KEEP CALM AND CARRY ON");?>
<?php if (Swift_Performance::is_developer_mode_active()):?>
      <div class="update-nag">
      <?php echo sprintf(__('%s Developer Mode is active until <strong>%s</strong>'), SWIFT_PERFORMANCE_PLUGIN_NAME, get_date_from_gmt( date( 'Y-m-d H:i:s', get_option('swift-performance-developer-mode') ), get_option('date_format') . ' ' .get_option('time_format') ));?>
      </div>
<?php endif;?>

<h1><?php echo SWIFT_PERFORMANCE_PLUGIN_NAME;?></h1>
<ul class="swift-menu">
      <?php foreach(Swift_Performance::get_menu() as $element):?>
            <li<?php echo((isset($_GET['subpage']) && $_GET['subpage'] == $element['slug']) || (!isset($_GET['subpage']) && $element['slug'] == 'dashboard') ? ' class="active"' : '');?>><a href="<?php echo esc_url(add_query_arg('subpage', $element['slug'], menu_page_url(SWIFT_PERFORMANCE_SLUG, false)));?>"><?php echo esc_html($element['name']);?></a></li>
      <?php endforeach;?>
</ul>
