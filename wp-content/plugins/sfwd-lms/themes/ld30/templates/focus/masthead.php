<?php
global $post;
$logo = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'login_logo' ); ?>

<div class="ld-focus-header">

    <?php do_action('learndash-focus-header-mobile-nav-before', $course_id, $user_id ); ?>

    <div class="ld-mobile-nav">
        <a href="#" class="ld-trigger-mobile-nav" aria-label="<?php esc_attr_e( 'Menu', 'learndash' ); ?>">
            <span class="bar-1"></span>
            <span class="bar-2"></span>
            <span class="bar-3"></span>
        </a>
    </div>

    <?php do_action('learndash-focus-header-logo-before', $course_id, $user_id ); ?>

    <div class="ld-brand-logo">
        <?php if (!empty($logo)): ?>
            <img src="<?php echo esc_url(wp_get_attachment_url($logo)); ?>" alt="<?php echo esc_attr(get_post_meta($logo , '_wp_attachment_image_alt', true)); ?>">
        <?php endif; ?>
    </div>

    <?php
    do_action('learndash-focus-header-logo-after', $course_id, $user_id );

    learndash_get_template_part( 'modules/progress.php', array(
        'course_id' =>  $course_id,
        'user_id'   =>  $user_id,
        'context'   =>  'focus'
    ), true ); ?>

    <?php
    do_action('learndash-focus-header-nav-before', $course_id, $user_id );

    $can_complete    = learndash_30_focus_mode_can_complete();

    learndash_get_template_part(
            'modules/course-steps.php',
            array(
                'course_id'          => $course_id,
                'course_step_post'   => $post,
                'user_id'            => $user_id,
                'course_settings'    => isset( $course_settings ) ? $course_settings : array(),
                'can_complete'       => $can_complete,
                'context'            => 'focus'
            ),
            true
        );

    do_action('learndash-focus-header-nav-after', $course_id, $user_id ); ?>

    <?php if( is_user_logged_in() ): ?>
        <div class="ld-user-menu">
            <?php
            do_action('learndash-focus-header-user-menu-before', $course_id, $user_id );

            $user_data = get_userdata($user_id); ?>
            <span class="ld-text ld-user-welcome-text"><?php echo sprintf( esc_html_x( 'Hello, %s!', 'Focus mode welcome placeholder', 'learndash' ), $user_data->user_nicename ); ?></span>

            <span class="ld-profile-avatar">
                <?php
                do_action('learndash-focus-header-avatar-before', $course_id, $user_id );
                echo get_avatar($user_id);
                do_action('learndash-focus-header-avatar-after', $course_id, $user_id ); ?>
            </span> <!--/.ld-profile-avatar-->

            <?php
            do_action('learndash-focus-header-user-dropdown-before', $course_id, $user_id ); ?>

            <span class="ld-user-menu-items">
                <?php
                $custom_menu_items = learndash_30_get_custom_focus_menu_items();

                $menu_items = array(
                    'course-home'   =>  array(
                        'url'   =>  get_the_permalink($course_id),
                        'label' =>  sprintf( esc_html_x( '%s Home', 'Placeholder for course home link', 'learndash' ), LearnDash_Custom_Label::get_label('course')
                    ),
                ) );

                if( $custom_menu_items ): foreach( $custom_menu_items as $menu_item ):

                    $menu_items[$menu_item->post_name] = array(
                        'url'   =>  $menu_item->url,
                        'label' =>  $menu_item->title
                    );

                endforeach; endif;

                $menu_items['logout'] = array(
                    'url'   =>  wp_logout_url( get_the_permalink($course_id) ),
                    'label' =>  __( 'Logout', 'learndash' )
                );

                if( $menu_items && !empty($menu_items) ):
                    foreach( $menu_items as $slug => $item ): ?>
                        <a class="<?php echo esc_attr( 'ld-focus-menu-link ld-focus-menu-' . $slug ); ?>" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
                    <?php endforeach;
                endif; ?>
            </span> <!--/.ld-user-menu-items-->

            <?php
            do_action('learndash-focus-header-user-dropdown-after', $course_id, $user_id ); ?>

        </div>
    <?php
    endif;
    do_action('learndash-focus-header-usermenu-after', $course_id, $user_id );  ?>
</div> <!--/.ld-focus-header-->
