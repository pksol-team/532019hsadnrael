<?php
require_once UCD_APPLICATION_PATH . '/services/View/Helper/AbstractHelper.php';

class Ucd_Service_View_Helper_WysiwygEditor
    extends Ucd_Service_View_Helper_AbstractHelper
{
    protected $_contentFilters = array(
        'wp_richedit_pre',
        'wp_htmledit_pre',
    );

    public function __invoke($content, $id, array $options)
    {
        if (!function_exists('wp_editor')) {
            return $this->_renderTextarea($content, $id, $options);
        }

        if (!isset($options['tinymce'])) {
            $options['tinymce'] = array();
        }

        // buttons
        if (isset($options['buttons'])) {
            $buttons = $options['buttons'];
            unset ($options['buttons']);

            for ($i=0; $i<=4; $i++) {
                $optionName = 'toolbar' . ($i+1);
                $options['tinymce'][$optionName] = isset($buttons[$i])
                    ? implode(',', $buttons[$i])
                    : '';

                // 3.x name
                $options['tinymce']['theme_advanced_buttons' . ($i+1)] =
                    $options['tinymce'][$optionName];
            }
        }

        // fonts
        if (isset($options['fonts'])) {
            $fonts = $options['fonts'];
            unset ($options['fonts']);

            $list = array();
            foreach ($fonts as $key=>$value) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $key = str_replace('_', ' ', $key);
                $list[] = "{$key}={$value}";
            }
            $list = implode(';', $list);

            $options['tinymce']['font_formats'] = $list;
            // 3.x name:
            $options['tinymce']['theme_advanced_fonts'] = $list;
        }

        // unhide_kitchensink
        if (isset($options['unhide_kitchensink'])
            && $options['unhide_kitchensink']
        ) {
            unset($options['unhide_kitchensink']);
            $options['tinymce']['wordpress_adv_hidden'] = FALSE;
        }

        // Common settings
        $options['tinymce']['cleanup_on_startup'] = false;
        $options['tinymce']['trim_span_elements'] = false;
        $options['tinymce']['verify_html'] = false;
        $options['tinymce']['cleanup'] = false;
        $options['tinymce']['convert_urls'] = false;

        $options['tinymce']['theme_advanced_resizing'] = false;
        //$options['tinymce']['forced_root_block'] = false;

        // Customized settings to apply align on form tag
        // @see: class-wp-editor.php _WP_Editors::editor_settings()
        $options['tinymce']['formats'] =
            "{
                alignleft : [
                    {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,form', styles : {textAlign : 'left'}},
                    {selector : 'img:not(.mceItemIframe),table', classes : 'alignleft'}
                ],
                aligncenter : [
                    {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,form', styles : {textAlign : 'center'}},
                    {selector : 'img:not(.mceItemIframe),table', classes : 'aligncenter'}
                ],
                alignright : [
                    {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,form', styles : {textAlign : 'right'}},
                    {selector : 'img:not(.mceItemIframe),table', classes : 'alignright'}
                ],
                strikethrough : {inline : 'del'}
            }";

        // Disable wpautop call through filters
        $fixWpautop = isset($options['wpautop']) && !$options['wpautop'];
        if ($fixWpautop) {
            $this->_addWpautopFix();
        }

        //
        // Editor call
        //

        wp_editor($content, $id, $options);

        // Cleanup
        if ($fixWpautop) {
            $this->_removeWpautopFix();
        }
    }

    function handleWpautopFix($value)
    {
        foreach ($this->_contentFilters as $filter) {
            if (has_filter('the_editor_content', $filter)) {
                remove_filter('the_editor_content', $filter);
                add_filter('the_editor_content', array($this, $filter));
            }
        }

        return $value;
    }

    protected function _addWpautopFix()
    {
        add_filter('the_editor', array($this, 'handleWpautopFix'));
    }

    protected function _removeWpautopFix()
    {
        remove_filter('the_editor', array($this, 'handleWpautopFix'));

        foreach ($this->_contentFilters as $filter) {
            remove_filter('the_editor_content', array($this, $filter));
        }
    }

    public function wp_richedit_pre($output)
    {
        if (!empty($output)) {
            $output = convert_chars($output);
        }
        // Removed due to breaking JS code
        //$output = htmlspecialchars($output, ENT_NOQUOTES);

        return apply_filters('richedit_pre', $output);
    }

    public function wp_htmledit_pre($output)
    {
        return apply_filters('htmledit_pre', $output);
    }

    protected function _renderTextarea($content, $id, $options)
    {
        $name = isset($options['textarea_name'])
            ? $options['textarea_name']
            : $id;
        $rows =  isset($options['textarea_rows'])
            ? $options['textarea_rows']
            : 4;
        $class = isset($options['editor_class'])
            ? $options['editor_class']
            : '';
        ?>
            <textarea name="<?php echo $name;?>"
                id="<?php echo $id;?>"
                rows="<?php echo $rows; ?>"
                class="<?php echo $this->scape($class); ?>"
            ><?php echo $this->escape($content);?></textarea>
        <?php
    }
}