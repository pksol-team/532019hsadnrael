<?php defined('ABSPATH') or die("KEEP CALM AND CARRY ON");?>
<?php
      $active_plugins   = (array) get_option( 'active_plugins', array() );
      $rule_modes       = Swift_Performance_Plugin_Organizer::get_rule_modes();
?>
      <form id="plugin-organizer" method="post">
            <?php
                  foreach ($active_plugins as $plugin):
                        if (basename(dirname(WP_PLUGIN_DIR . '/' . $plugin)) == basename(SWIFT_PERFORMANCE_DIR)) {
                              continue;
                        }
                        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
                        $rules = '';
                        foreach (Swift_Performance_Plugin_Organizer::get_rules($plugin) as $rule){
            		      $rules .= '<li class="plugin-rule">'.Swift_Performance_Plugin_Organizer::get_formatted_rule($rule, mt_rand(0,PHP_INT_MAX)) . '</li>';
                        }
            ?>
            <div class="swift-box">
                  <h3><?php echo esc_html($plugin_data['Name']);?></h3>

                  <div class="swift-box-inner" data-plugin="<?php echo esc_attr($plugin);?>">
            		<ul class="rule-container"><?php echo $rules;?></ul>
                        <div class="empty-rule-message"><?php esc_html_e('No rules', 'swift-performance');?></div>
                        <select class="rule-mode-selector">
                              <option value=""><?php esc_html_e('Please Select', 'swift-performance')?></option>
                              <optgroup label="<?php esc_html_e('Disable', 'swift-performance');?>">
                              <?php foreach ($rule_modes as $mode): ?>
                                    <option value="<?php echo esc_attr($mode)?>" data-type="disable"><?php echo Swift_Performance_Plugin_Organizer::get_formatted_mode_name($mode);?></option>
                              <?php endforeach;?>
                              </optgroup>
                              <optgroup label="<?php esc_html_e('Enable', 'swift-performance');?>">
                              <?php foreach ($rule_modes as $mode): ?>
                                    <option value="<?php echo esc_attr($mode)?>" data-type="exception"><?php echo Swift_Performance_Plugin_Organizer::get_formatted_mode_name($mode);?></option>
                              <?php endforeach;?>
                              </optgroup>
                        </select>
                        <a href="#" class="swift-add-plugin-rule swift-btn swift-btn-green"><?php esc_html_e('Add Rule', 'swift-performance');?></a>
                        <div class="swift-plugin-rule-help swift-help-frontend swift-hidden"><?php esc_html_e('This rule will be true on every frontend requests (except AJAX requests)', 'swift-performance');?></div>
                        <div class="swift-plugin-rule-help swift-help-url-match swift-hidden"><?php esc_html_e('If the URL contains the given string, the rule will be true. Use leading/trailing # for regex. Eg: #product/(.*)#', 'swift-performance');?></div>
                        <div class="swift-plugin-rule-help swift-help-admin swift-hidden"><?php esc_html_e('This rule will be true on selected admin pages', 'swift-performance');?></div>
                        <div class="swift-plugin-rule-help swift-help-ajax swift-hidden"><?php esc_html_e('This rule will be true on all AJAX requests', 'swift-performance');?></div>
                        <div class="swift-plugin-rule-help swift-help-ajax-action swift-hidden"><?php esc_html_e('This rule will be true on specific AJAX request. Eg: heartbeat', 'swift-performance');?></div>
                        <div class="swift-plugin-rule-help swift-help-user-role swift-hidden"><?php esc_html_e('This rule will be true if the user has the selected user role(s)', 'swift-performance');?></div>
                        <div class="swift-plugin-rule-help swift-help-query-string swift-hidden"><?php esc_html_e('This rule will be true if the query string contains the given value. Eg: test=123', 'swift-performance');?></div>
                        <div class="swift-plugin-rule-help swift-help-cookie swift-hidden"><?php esc_html_e('This rule will be true if the cookies contain the given value. Eg: test=123', 'swift-performance');?></div>
                        <div class="swift-plugin-rule-help swift-help-desktop swift-hidden"><?php esc_html_e('This rule will be true for desktop user agent', 'swift-performance');?></div>
                        <div class="swift-plugin-rule-help swift-help-mobile swift-hidden"><?php esc_html_e('This rule will be true for mobile user agent', 'swift-performance');?></div>
                  </div>
      	</div>
            <?php endforeach;?>
            <?php wp_nonce_field('swift-save-plugin-organizer', 'swift-save-plugin-organizer-nonce')?>
      </form>

<ul id="swift-plugin-rule-samples">
      <?php
      foreach ($rule_modes as $mode){
            $is_editable = !in_array($mode, array('frontend', 'ajax', 'desktop', 'mobile'));
            echo '<li class="'.$mode.'-sample plugin-rule'.($is_editable ? ' editable' : '').'">';
            echo Swift_Performance_Plugin_Organizer::get_formatted_rule(array(
                  'slug'      => '%SLUG%',
                  'type'      => '%TYPE%',
                  'mode'      => $mode,
                  'rule'      => ''
            ));
            echo '</li>';
      }
      ?>
</ul>
