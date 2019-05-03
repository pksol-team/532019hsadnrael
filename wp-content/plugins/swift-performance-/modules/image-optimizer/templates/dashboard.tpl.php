<?php defined('ABSPATH') or die("KEEP CALM AND CARRY ON");?>
<?php
	if (!Swift_Performance::is_function_disabled('ini_set')){
		ini_set('memory_limit', -1);
	}
	include_once SWIFT_PERFORMANCE_IMAGE_OPTIMIZER_DIR . 'image-table.php';
	$image_table = new Swift_Performance_Image_Optimizer_Table();
	$image_table->prepare_items();
	$percent = (int)($image_table->stat['optimized']/max(1,$image_table->stat['total'])*100);
?>
<div class="wrap">
	<div id="swift-image-optimizer-status-box" class="swift-box" data-total="<?php echo (int)$image_table->stat['total'];?>">
		<h3><?php esc_html_e('Statistics', 'swift-performance');?></h3>
		<div class="swift-box-inner">
			<ul class="swift-tiles">
				<li class="swift-tile">
					<div class="swift-pie-chart-container">
						<div class="swift-pie-chart" data-value="<?php echo esc_attr($percent);?>"></div>
						<div class="swift-pie-inner"><span class="swift-counter percent" data-duration="3000" data-count="<?php echo (int)($percent);?>">0</span></div>
					</div>
				</li>
				<li class="swift-tile wide">
					<ul id="swift-images-stat-container">
						<li>
							<label><?php esc_html_e('Queued Images', 'swift-performance');?></label>
							<div class="swift-enqueued-image-count swift-image-count"><?php echo esc_html($image_table->stat['queued']);?></div>
						</li>
						<li>
							<label><?php esc_html_e('Optimized Images', 'swift-performance');?></label>
							<div class="swift-optimized-image-count swift-image-count"><?php echo esc_html($image_table->stat['optimized']);?></div>
						</li>
						<li>
							<label><?php esc_html_e('Saved', 'swift-performance');?></label>
							<div class="saved-space-count swift-image-count"><?php echo esc_html(Swift_Performance_Image_Optimizer::formatted_size(max(0, $image_table->stat['original_size'] - $image_table->stat['current_size'])));?></div>
						</li>
					</ul>
					<a href="#" id="swift-clear-image-queue" class="swift-hidden"><?php esc_html_e('Clear queue', 'swift-performance');?></a>
					<div class="swift-bar-chart-container">
						<div class="swift-bar-chart gray original-size">
							<div class="swift-bar-chart-bar-description">
								<label>
									<?php esc_html_e('Original size', 'swift-performance')?>
									<span><?php echo esc_html(Swift_Performance_Image_Optimizer::formatted_size($image_table->stat['original_size']));?></span>
								</label>
							</div>
							<div class="swift-bar-chart-bar">
								<span class="swift-bar-chart-bar-outer" data-value="100">
									<span class="swift-bar-chart-bar-inner"></span>
								</span>
							</div>
						</div>
						<div class="swift-bar-chart green optimized-size">
							<div class="swift-bar-chart-bar-description">
								<label>
									<?php esc_html_e('Current size', 'swift-performance')?>
									<span><?php echo esc_html(Swift_Performance_Image_Optimizer::formatted_size($image_table->stat['current_size']));?></span>
								</label>
							</div>
							<div class="swift-bar-chart-bar">
								<span class="swift-bar-chart-bar-outer" data-value="<?php echo min(100,(int)($image_table->stat['current_size']/max(1,$image_table->stat['original_size'])*100))?>">
									<span class="swift-bar-chart-bar-inner"></span>
								</span>
							</div>
						</div>
					</div>
				</li>
			</ul>
		</div>
	</div>
	<div class="swift-box">
		<h3><?php esc_html_e('Images', 'swift-performance')?></h3>
		<div class="swift-box-inner no-max-height">
			<div class="swift-button-container">
				<a href="#" id="swift-performance-refresh-list-table" class="swift-btn swift-btn-gray swift-btn-thin"><i class="fa fa-refresh"></i></a>
				<a href="#" id="swift-performance-scan-images" class="swift-btn swift-btn-gray"><?php esc_html_e('Scan Images', 'swift-performance');?></a>
				<a href="#" id="swift-performance-optimize-images" class="swift-btn swift-btn-green"><?php esc_html_e('Optimize Images', 'swift-performance');?> (<span class="selected-images-count"></span><span class="selected-images-count-default"><?php esc_html_e('All', 'swift-performance')?></span>)</a>
				<a href="#" id="swift-performance-restore-images" class="swift-btn swift-btn-blue"><?php esc_html_e('Restore Original Images', 'swift-performance');?> (<span class="selected-images-count"></span><span class="selected-images-count-default"><?php esc_html_e('All', 'swift-performance')?></span>)</a>
				<a href="#" id="swift-performance-delete-original-images" class="swift-btn swift-btn-brand"><?php esc_html_e('Delete Original Images', 'swift-performance');?> (<span class="selected-images-count"></span><span class="selected-images-count-default"><?php esc_html_e('All', 'swift-performance')?></span>)</a>
				<a href="#" id="swift-performance-optimizer-settings" class="swift-custom-settings-trigger swift-btn swift-btn-gray"><?php esc_html_e('Settings', 'swift-performance');?></a>
			</div>
			<ul id="swift-selected-images" class="swift-coverflow"></ul>
			<div class="swift-selected-images">
				<div class="swift-coverflow-max-number-message swift-hidden"><i class="fa fa-warning"></i> <?php esc_html_e('Coverflow can show maximum 1000 images', 'swift-performance')?></div>
				<span class="selected-images-count"></span> <?php esc_html_e('images selected', 'swift-performace');?> <a href="#" id="clear-all-selected-images" class="swift-btn swift-btn-gray"><?php esc_html_e('Clear All', 'swift-performace');?></a>
			</div>
			<div id="swift-performance-list-table-container">
				<span id="swift-optimizer-ids" class="swift-hidden"><?php echo json_encode($image_table->stat['ids'])?></span>
				<?php
					$image_table->display();
				?>
			</div>
		</div>
	</div>
</div>

<div class="swift-custom-settings-container">
	<div class="swift-custom-settings-trigger"><span class="dashicons dashicons-admin-generic"></span></div>
	<div class="swift-custom-settings">
		<h3><?php esc_html_e('Optimizer Settings', 'swift-performance');?></h3>
		<div class="swift-range-slider">
		 	<div class="swift-custom-settings-cell">
				<label><?php esc_html_e('JPEG quality', 'swift-performance');?></label>
	  		</div>
			<div class="swift-custom-settings-cell">
				<input class="swift-range-slider__range" type="range" name="jpeg-quality" type="range" value="<?php echo esc_attr(Swift_Performance::get_option('jpeg-quality'));?>" data-global="<?php echo esc_attr(Swift_Performance::get_option('jpeg-quality'));?>" min="0" max="100">
				<input class="swift-range-slider__value" type="number" value="<?php echo esc_html(Swift_Performance::get_option('jpeg-quality'));?>">
		  	</div>
		</div>
		<div class="swift-range-slider">
		 	<div class="swift-custom-settings-cell">
				<label><?php esc_html_e('PNG quality', 'swift-performance');?></label>
	  		</div>
			<div class="swift-custom-settings-cell">
				<input class="swift-range-slider__range" type="range" name="png-quality" type="range" value="<?php echo esc_attr(Swift_Performance::get_option('png-quality'));?>" data-global="<?php echo esc_attr(Swift_Performance::get_option('png-quality'));?>" min="0" max="100">
				<input class="swift-range-slider__value" type="number" value="<?php echo esc_html(Swift_Performance::get_option('png-quality'));?>">
		  	</div>
		</div>
		<div class="swift-custom-settings-row">
			<div class="swift-custom-settings-cell">
				<label><?php esc_html_e('Resize Large Images', 'swift-performance');?></label>
			</div>
			<div class="swift-custom-settings-cell">
				<input type="checkbox" name="resize-large-images" value="enabled" <?php checked(Swift_Performance::get_option('resize-large-images'),1);?> data-global="<?php echo esc_attr(Swift_Performance::get_option('resize-large-images'));?>">
				<span><input type="number" name="maximum-image-width" value="<?php echo esc_attr(Swift_Performance::get_option('maximum-image-width'));?>" data-global="<?php echo esc_attr(Swift_Performance::get_option('maximum-image-width'));?>">px</span>
			</div>
		</div>
		<div class="swift-custom-settings-row">
			<div class="swift-custom-settings-cell">
				<label><?php esc_html_e('Keep Original Images', 'swift-performance');?> </label>
			</div>
			<div class="swift-custom-settings-cell">
				<input type="checkbox" name="keep-original-images" value="enabled" <?php checked(Swift_Performance::get_option('keep-original-images'),1);?> data-global="<?php echo esc_attr(Swift_Performance::get_option('keep-original-images'));?>">
			</div>
		</div>
		<br>
		<button id="swift-close-custom-settings" class="swift-btn swift-btn-green"><?php esc_html_e('Close', 'swift-performance');?></button> <button id="swift-reset-custom-settings" class="swift-btn swift-btn-gray"><?php esc_html_e('Use Global Settings', 'swift-performance');?></button>
	</div>
</div>
