<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

$service = new LandingPageService(ApiFactory::create_api());
$pages = $service->get_pages();
$connected_pages = (array)$service->get_connected_pages();
/**
 * Display Landing Pages page.
 */
gr_load_template( 'admin/settings/header.php' ); ?>

<h2 class="nav-tab-wrapper"><?php echo gr_get_admin_tabs( 'gr-integration-landing-pages' ) ?></h2>

<?php gr_load_template( 'admin/settings/partials/messages.php' ); ?>

<table class="form-table subscription-settings gr-landing-pages gr_box">
    <tbody>
        <tr>
            <td class="subscription-settings-cell">
                <h2>
                    <?php _e( 'Landing Pages', 'Gr_Integration' ); ?>
                    <a href="#" id="gr_lp_add" class="button"><?php _e( 'Add page', 'Gr_Integration' ); ?></a>
                </h2>
                <p class="description gr_description">
                    <?php _e( 'Publish your GetResponse landing pages on WordPress', 'Gr_Integration' ); ?>
                </p>
                <br>
                <table id="pagesTable" class="wp-list-table widefat fixed striped posts">
                    <tr>
                        <td class="manage-column column-cb check-column"></td>
                        <td class="manage-column column-title column-primary">Landing Page</td>
                        <td>Location</td>
                    </tr>
                    <?php if (empty($connected_pages)): ?>
                    <tr id="gr_lp_nodata">
                        <td></td>
                        <td colspan="2">
                            <?php _e( 'No pages yet. Click Add page to get started', 'Gr_Integration' ); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php foreach ($connected_pages as $url => $connected_page): ?>
                        <tr data-id="<?php echo $url; ?>">
                            <td class="manage-column column-cb check-column">
                                <span class="hidden">
                                        <span class="edit-id"><?php echo $connected_page['id']; ?></span>
                                        <span class="edit-url"><?php echo $url; ?></span>
                                        <span class="edit-status"><?php echo $connected_page['status']; ?></span>
                                    </span>
                            </td>
                            <td class="title column-title has-row-actions column-primary page-title">
                                <span class="gr-cp-title"><?php echo $connected_page['title']; ?></span>
                                <span>—</span>
                                <span><?php if ($connected_page['status'] == 1): ?>
                                        <?php _e( 'Published', 'Gr_Integration' ); ?>
                                    <?php else: ?>
                                        <?php _e( 'Unpublished', 'Gr_Integration' ); ?>
                                    <?php endif; ?>
                                </span>
                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="#">Edit</a> |
                                    </span>
                                    <span class="trash">
                                        <a class="submitdelete">Trash</a>
                                    </span>
                                </div>
                            </td>
                            <td><a target="_blank" href="<?php echo get_home_url(); ?>/<?php echo $url; ?>"><?php echo get_home_url(); ?>/<?php echo $url; ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="gr_edit_lp_box" class="hidden inline-edit-row inline-edit-row-post quick-edit-row quick-edit-row-post inline-edit-post inline-editor" style="">
                        <td colspan="3" class="colspanchange">
                            <br class="clear">
                            <fieldset class="inline-edit-col-left">
                                <div class="inline-edit-col">
                                    <label>
                                        <span class="title"><?php _e( 'Name', 'Gr_Integration' ); ?></span>
                                        <span class="input-text-wrap">
                                            <select name="pageId" required class="pageId">
                                                <option disabled selected value="">Select a landing page</option>
                                                <?php foreach ($pages as $page_id => $page_name): ?>
                                                <option value="<?php echo $page_id; ?>"><?php echo $page_name; ?></option>
                                            <?php endforeach; ?>
                                            </select>
                                            <?php if (0 === count($pages)): ?>
                                            <span class="lpage-error-hint">
                                                <?php _e( 'You can select published landing pages only. In GetResponse, go to', 'Gr_Integration' ); ?>
                                                <strong>
                                                    <?php _e( 'Landing pages', 'Gr_Integration' ); ?>
                                                </strong>
                                                <?php _e( 'and change the status of the pages you want to use to', 'Gr_Integration' ); ?>
                                                <strong>
                                                    <?php _e( 'Published', 'Gr_Integration' ); ?>
                                                </strong>.
                                            </span>
                                            <?php endif; ?>
                                        </span>
                                    </label>
                                    <label class="gr-url-validator">
                                        <span class="title"><?php _e( 'URL', 'Gr_Integration' ); ?></span>
                                        <span><?php echo get_home_url(); ?>/<input class="pageUrl" required pattern="[A-Za-z]{1,128}" name="url" type="text"></span>
                                        <span class="url-error-hint"><?php _e( 'This URL is already in use. Please enter a different page name.', 'Gr_Integration' ); ?></span>
                                    </label>
                                    <label>
                                        <span class="title"><?php _e( 'Set status', 'Gr_Integration' ); ?></span>
                                        <span class="input-text-wrap">
                                            <label for="gr-status-radio-0">
                                                <input class="gr-status-radio-0" id="gr-status-radio-0" name="pageStatus" type="radio" value="0"> <?php _e( 'Unpublished', 'Gr_Integration' ); ?>
                                            </label>
                                            <br>
                                            <label for="gr-status-radio-1">
                                                <input class="gr-status-radio-1" id="gr-status-radio-1" name="pageStatus" type="radio" value="1"> <?php _e( 'Published', 'Gr_Integration' ); ?>
                                            </label>
                                        </span>
                                    </label>
                                    <br class="clear">
                                </div>
                            </fieldset>
                            <div class="submit inline-edit-save">
                                <button type="button" class="button cancel alignleft"><?php _e( 'Cancel', 'Gr_Integration' ); ?></button>
                                <button type="button" class="button button-primary save alignright"><?php _e( 'Save', 'Gr_Integration' ); ?></button>
                            </div>
                            <br class="clear">
                            <br class="clear">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </tbody>
</table>

<script>
    var homeUrl = '<?php echo get_home_url(); ?>';
    var controllerUrl = "<?php echo admin_url( 'admin.php?page=gr-integration-landing-pages' ); ?>";
    var deleteConfirmationMsg = "<?php _e( 'Please confirm that you want to delete this page', 'Gr_Integration' ); ?>";
    var editText = "<?php _e( 'Edit', 'Gr_Integration' ); ?>";
    var trashText = "<?php _e( 'Trash', 'Gr_Integration' ); ?>";
    var publishedText = "<?php _e( 'Published', 'Gr_Integration' ); ?>";
    var unpublishedText = "<?php _e( 'Unpublished', 'Gr_Integration' ); ?>";
</script>
<script type="text/javascript" src="<?php echo gr()->asset_path . '/js/gr-landing-pages.js'; ?>"></script>
