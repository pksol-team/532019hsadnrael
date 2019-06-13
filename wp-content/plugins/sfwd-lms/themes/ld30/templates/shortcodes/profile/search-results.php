<div class="ld-item-search ld-profile-search-string">
    <div class="ld-item-search-wrapper">
        <span class="ld-text"><?php echo sprintf( __( 'You searched for "%s"', 'learndash' ), $_GET['ld-profile-search'] ); ?></span>
        <a class="ld-reset-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Reset', 'learndash' ); ?></a>
    </div>
</div> <!--/.ld-profile-search-string-->
