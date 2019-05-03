
jQuery(window).load(function() {
	function ld_course_grid_resize() {
		var last_position = {left: 0, top: 0}, talest = 0, last_item = null;

		jQuery(".ld_course_grid").each(function (i, v) {
			var item = jQuery(this);
			var position = item.position();
			var item_height = item.height();

			if(position.left <= last_position.left ) {
				if(item_height < talest )
					last_item.height(talest);
				talest = 0;
			}
			if(item_height >= talest)
				talest = item_height + 5;

			last_position = position;
			last_item = item;
		});
	}
	ld_course_grid_resize();
	jQuery(window).resize(function() {
		ld_course_grid_resize();
	});

	function learndash_course_grid_course_edit_page_javascript() {
		jQuery("select[name=sfwd-courses_course_price_type]").change(function(){
			var price_type = 	jQuery("select[name=sfwd-courses_course_price_type]").val();
			if(price_type == "closed") 
				jQuery("#sfwd-courses_course_price").show();
		});
		jQuery("select[name=sfwd-courses_course_price_type]").change();
	}
	if(jQuery(".sfwd-courses_settings").length)
	setTimeout( function() {learndash_course_grid_course_edit_page_javascript();}, 1000);
});
