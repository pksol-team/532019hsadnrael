<?php
/**
 * Custom meta box class of type Number
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */

/**
 * Custom meta box class of type number
 *
 *
 * @package    PTB
 * @subpackage PTB/includes
 * @author     Themify <ptb@themify.me>
 */
class PTB_CMB_Number extends PTB_CMB_Base {

    /**
     * Adds the custom meta type to the plugin meta types array
     *
     * @since 1.0.0
     *
     * @param array $cmb_types Array of custom meta types of plugin
     *
     * @return array
     */
    public function filter_register_custom_meta_box_type($cmb_types) {

        $cmb_types[$this->get_type()] = array(
            'name' => __('Number', 'ptb')
        );

        return $cmb_types;
    }

    /**
     * @param string $id the id template
     * @param array $languages
     */
    public function action_template_type($id, array $languages) {
        ?>
        <div class="ptb_cmb_input_row">
            <label for="<?php echo $id; ?>_range" class="ptb_cmb_input_label">
                <?php _e("Show as range", 'ptb'); ?>
            </label>
            <div class="ptb_cmb_input">
                <input type="checkbox" id="<?php echo $id; ?>_range" name="<?php echo $id; ?>_showrange" value="1" />
            </div>
        </div>
        <?php
    }

    /**
     * Renders the meta boxes for themplates
     *
     * @since 1.0.0
     *
     * @param string $id the metabox id
     * @param string $type the type of the page(Arhive or Single)
     * @param array $args Array of custom meta types of plugin
     * @param array $data saved data
     * @param array $languages languages array
     */
    public function action_them_themplate($id, $type, $args, $data = array(), array $languages = array()) {
        $currency = PTB_Utils::get_currencies();
        $currency_pos = PTB_Utils::get_currency_position();
        ?>  
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[currency]"><?php _e('Currency', 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input ptb_change_disable" data-disabled="0" data-action="1">
                    <div class="ptb_custom_select">
                        <select id="ptb_<?php echo $id ?>[currency]" name="[<?php echo $id ?>][currency]">
                            <option value="0" <?php if(empty($data['currency'])):?>selected="selected"<?php endif;?>>---</option>
                            <?php foreach ($currency as $c => $name): ?>
                            <option <?php if (isset($data['currency']) && $data['currency'] === $c): ?>selected="selected"<?php endif; ?> value="<?php echo $c ?>"><?php echo $name. ' (' . PTB_Utils::get_currency_symbol($c) . ')' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="ptb_back_active_module_row ptb_maybe_disabled">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[currency_pos]"><?php _e('Currency Position', 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <div class="ptb_custom_select">
                        <select id="ptb_<?php echo $id ?>[currency_pos]" name="[<?php echo $id ?>][currency_pos]">
                            <?php foreach ($currency_pos as $c => $name): ?>
                                <option <?php if (isset($data['currency_pos']) && $data['currency_pos'] === $c): ?>selected="selected"<?php endif; ?> value="<?php echo $c ?>"><?php echo $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[thousand]"><?php _e('Thousand Seperator', 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <input type="text" id="ptb_<?php echo $id ?>[thousand]"
                           name="[<?php echo $id ?>][thousand]" value="<?php echo isset($data['thousand']) ? $data['thousand'] : ',' ?>"
                           />
                </div>
            </div>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[decimal]"><?php _e('Decimal Seperator', 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <input type="text" id="ptb_<?php echo $id ?>[decimal]"
                           name="[<?php echo $id ?>][decimal]" value="<?php echo isset($data['decimal']) ? $data['decimal'] : '.' ?>"
                           />
                </div>
            </div>
            <div class="ptb_back_active_module_row">
                <div class="ptb_back_active_module_label">
                    <label for="ptb_<?php echo $id ?>[ndecimals]"><?php _e('Number of Decimals', 'ptb') ?></label>
                </div>
                <div class="ptb_back_active_module_input">
                    <input type="text" id="ptb_<?php echo $id ?>[ndecimals]"
                           name="[<?php echo $id ?>][ndecimals]" value="<?php echo isset($data['ndecimals']) ? (int)$data['ndecimals'] : 2 ?>"
                           />
                </div>
            </div>
            <?php if(!empty($args['range'])):?>
                <div class="ptb_back_active_module_row">
                    <div class="ptb_back_active_module_label">
                        <label for="ptb_<?php echo $id ?>[seperator]"><?php _e('Range seperator', 'ptb') ?></label>
                    </div>
                    <div class="ptb_back_active_module_input">
                        <input type="text" id="ptb_<?php echo $id ?>[rangeseperator]"
                               name="[<?php echo $id ?>][seperator]" value="<?php echo isset($data['seperator']) ? $data['seperator'] : ' - ' ?>"
                               />
                    </div>
                </div>
            <?php endif;?>
        <?php
    }

    /**
     * Renders the meta boxes  in public
     *
     * @since 1.0.0
     *
     * @param array $args Array of custom meta types of plugin
     * @param array $data themplate data
     * @param array or string $meta_data post data
     * @param string $lang language code
     * @param boolean $is_single single page
     */
    public function action_public_themplate(array $args, array $data, $meta_data, $lang = false, $is_single = false) {
        if (isset($meta_data[$args['key']]) && $meta_data[$args['key']]) {
            $value = $meta_data[$args['key']];
            $range =  !empty($args['range']);
            $is_array = is_array($value);
            if(!$range && $is_array){
                $value = $value['from'];
            }
            if(!$value){
                return;
            }
            $thousand_separator =  !empty($data['thousand'])?$data['thousand']:false;
            if($thousand_separator){
                $decimals =  isset($data['ndecimals'])?(int)$data['ndecimals']:'';
                $decimal_separator = !empty($data['decimal'])?$data['decimal']:'';
            }
            $currency = !empty($data['currency'])?$data['currency']:'';
            $currency_pos = $currency && !empty($data['currency_pos'])?$data['currency_pos']:'left';
            ?>
            <?php if($range && $is_array):?>
            <?php
                if($thousand_separator){
                    $value['from'] = number_format( $value['from'], $decimals, $decimal_separator, $thousand_separator );
                }
                if($currency_pos){
                    $value['from'] = PTB_Utils::get_price_format($currency_pos, $currency, $value['from']);
                }
                echo $value['from'];
                if(!isset($data['seperator'])){
                    $data['seperator'] = ' - ';
                }
            ?>
            <?php if($data['seperator'] && $value['to']):?>
                <span class="number_seperator"><?php echo $data['seperator'] ?></span>
            <?php endif;?>
                <?php
                if($thousand_separator){
                    $value['to'] = number_format( $value['to'], $decimals, $decimal_separator, $thousand_separator );
                }
                if($currency_pos){
                    $value['to'] = PTB_Utils::get_price_format($currency_pos, $currency, $value['to']);
                }
                echo $value['to']?>
            <?php else:?>
                <?php 
                if($thousand_separator){
                    $value = number_format( $value, $decimals, $decimal_separator, $thousand_separator );
                }
                if($currency_pos){
                    $value = PTB_Utils::get_price_format($currency_pos, $currency, $value);
                }
                echo $value;?>
            <?php endif;?>
            <?php
        }
    }

    /**
     * Renders the meta boxes on post edit dashboard
     *
     * @since 1.0.0
     *
     * @param WP_Post $post
     * @param string $meta_key
     * @param array $args
     */
    public function render_post_type_meta($post, $meta_key, $args) {
        $value = get_post_meta($post->ID, 'ptb_' . $meta_key, true);
        ?>
        <?php if(isset($args['range']) && $args['range']):?>
            <input class="ptb_number" placeholder="<?php _e('From', 'ptb') ?>" type="text" id="<?php echo $meta_key; ?>_from" name="<?php echo $meta_key; ?>[from]"  value="<?php echo !is_array($value)?$value:(isset($value['from'])?$value['from']:''); ?>"/>
            <span class="ti-arrow-right"></span>
            <input class="ptb_number" placeholder="<?php _e('To', 'ptb') ?>" type="text" id="<?php echo $meta_key; ?>_to" name="<?php echo $meta_key; ?>[to]"  value="<?php echo is_array($value) && isset($value['to'])?$value['to']:''; ?>"/>
        <?php else:?>
            <input class="ptb_number" type="text" id="<?php echo $meta_key; ?>_from" name="<?php echo $meta_key; ?>" value="<?php echo !is_array($value)?$value:(isset($value['from'])?$value['from']:''); ?>"/>
        <?php endif;?>
        <?php
    }

}
