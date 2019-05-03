<?php defined('ABSPATH') or die("KEEP CALM AND CARRY ON");?>
<div class="media-modal wp-core-ui swift-modal swift-hidden swift-quickview">
      <button type="button" class="media-modal-close swift-modal-close"><span class="media-modal-icon"></span></button>
      <div class="media-modal-content">
            <div class="edit-attachment-frame mode-select hide-menu hide-router">

                  <div class="media-frame-title">
                        <h1><?php esc_html_e('Image details', 'swift-performance');?></h1>
                  </div>
                  <div class="media-frame-content">
                        <div tabindex="0" data-id="82110" class="attachment-details save-ready">
                              <div class="attachment-media-view landscape">
                                    <div class="thumbnail thumbnail-image">
                                          <img class="details-image" src="<?php echo esc_url(add_query_arg('cache-buster', $cache_buster, $image_src));?>" draggable="false">
                                          <?php if (!empty($original_src)):?>
                                                <img class="details-image swift-hidden" src="<?php echo esc_url($original_src);?>" draggable="false">
                                          <?php endif;?>
                                          <div class="attachment-actions">
                                                <?php if (!empty($original_src) && !empty($optimized_size)):?>
                                                <button type="button" class="button swift-toggle-quickview"><span class="swift-hidden"><?php esc_html_e('Show optimized', 'swift-performance');?></span><span><?php esc_html_e('Show original', 'swift-performance');?></span></button>
                                                <?php endif;?>
                                          </div>
                                    </div>
                              </div>
                              <div class="attachment-info">
                                    <div class="details">
                                          <div><strong><?php esc_html_e('File name:','swift-performance');?></strong> <?php echo esc_html($basename);?></div>
                                          <div><strong><?php esc_html_e('Original size:','swift-performance');?></strong> <?php echo esc_html($original_size);?></div>
                                          <?php if(!empty($optimized_size)):?>
                                          <div><strong><?php esc_html_e('Optimized size:','swift-performance');?></strong> <?php echo esc_html($optimized_size);?></div>
                                          <div><strong><?php esc_html_e('Quality:','swift-performance');?></strong> <?php echo esc_html($quality);?>%</div>
                                          <?php endif;?>
                                          <div><strong><?php esc_html_e('Dimensions:','swift-performance');?></strong> <?php echo esc_html($dimensions);?></div>
                                    </div>
                              </div>
                        </div>
                  </div>
            </div>
      </div>
</div>
