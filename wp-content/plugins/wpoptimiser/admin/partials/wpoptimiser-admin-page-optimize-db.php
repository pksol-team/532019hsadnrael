<?php
/**
 * Provide a admin area view for the plugin database optimization
 *
 * This file is used to markup the admin-facing aspects of the plugin database optimization settings page.
 *
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/admin/partials
 */
?>
<div class="wrap">
<div class="branding clearfix">
 <div class="logo">
  <img class="logo" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/wpopti-small-logo.png';?>">
  <span class="help-link"><a href="https://cybertactics.net/products" target="_blanl">Other Products</a> | <a href="https://cybertactics.net" target="_blanl">Blog</a></span>
 </div>
 <h1 class="page-title"><?php echo esc_html( get_admin_page_title() ); ?></h1>
</div>

<table class="form-table">
 <tbody>
  <tr class="info">
   <td colspan="2">
    <p class="info"><?php echo __('Your WordPress database consists of many tables, that over time become inefficient as you add, change and remove data. Optimizing these tables every once in a while will keep your site running as efficient as possible.<br><br>Each time you create or edit a post or page, WordPress will create a revision of that post or page. So for example if you edit a post 6 times, then you might get 5 copies of that post as revisions. This can quickly add lots of rarely used data to your database, making it bloated and slower to access. You might have thousands of spam and un-approved comments in your comments table which can also be cleaned.<br><br>Please take regular site backups. WP Optimiser is very safe to use but we can\'t be responsible for any ad-hoc data loss through poor site management or database corruptions.');?></p>
   </td>
  </tr>
 <tr class="panel db-action">
  <td class="db-status">
   <h3>Database Optimization</h3>
   <div class="infobox infobox-blue2  small">
    <div class="infobox-progress">
      <div class="db-efficiency" data-percent="<?php echo $db_optimizer->efficiency_perc;?>"></div>
     </div>
    <div class="infobox-data">
      <div class="infobox-text">Size: <?php echo $db_optimizer->format_size($db_optimizer->total_size);?></div>
      <div class="infobox-text">Overhead: <?php echo $db_optimizer->format_size($db_optimizer->total_gain);?></div>
      <div class="infobox-text">Efficiency: <?php echo number_format_i18n($db_optimizer->efficiency_perc,2);?>%</div>
    </div>
  </div>
  </td>
  <td class="db-action db-optimize">
<?php if ($db_optimizer->efficiency_perc < 100)  { ?>
       <p>Your wordpress database '<?php echo htmlspecialchars(DB_NAME);?>' is currently running at <?php echo number_format_i18n($db_optimizer->efficiency_perc,2);?>% efficiency and has an overhead of <?php echo $db_optimizer->format_size($db_optimizer->total_gain);?>. You can regain this overhead by performing a database optimization.
       <?php if ($db_optimizer->inno_db_tables > 0)  { ?>
        Tables using InnoDB engine will not be optimized.
       <?php } ?>
       </p>
       <p class="dbo-problem">Oops Problem Found - Perform Database Optimization -->
         <input type="button" value="Perform Database Optimization" class="button button-primary button-action" name="db-optimize" data-action="db-optimize"></p>
<?php } else { ?>
       <p>Great News! Your WordPress database '<?php echo htmlspecialchars(DB_NAME);?>' is fully optimized.</p>
       <p class="dbo-good">All good - no action required.</p>
<?php } ?>
  </td>
 </tr>
 <tr class="panel db-action">
  <td>
   <h3>Post Revisions</h3>
   <div class="infobox infobox-blue small">
     <div class="infobox-icon">
        <i class="ace-icon dashicons dashicons-slides"></i>
     </div>
    <div class="infobox-data">
     <span class="infobox-text">Old Revisions: <?php echo number_format($post_revisions_count, 0); ?></span>
    </div>
   </div>
  </td>
  <td class="db-action db-post-revs">
<?php if ($post_revisions_count > 0)  { ?>
       <p>There are currently <?php echo number_format($post_revisions_count, 0); ?> old post revisions greater than <?php echo number_format($db_optimizer->retain_interval, 0); ?>  weeks old. You can remove these unnecessary post revisions, freeing up valuable space and increasing the speed and efficiency of your database by performing a revisions optimization.</p>
       <p class="dbo-problem">Oops Problem Found - Perform Revisions Optimization -->
         <input type="button" value="Perform Revisions Optimization" class="button button-primary button-action" name="db-post-revs" data-action="db-post-revs"></p>
<?php } else { ?>
       <p>Great News! Your WordPress database currently has no post revisions greater than <?php echo number_format($db_optimizer->retain_interval, 0); ?>  weeks old.</p>
       <p class="dbo-good">All good - no action required.</p>
<?php } ?>
  </td>
 </tr>
 <tr class="panel db-action">
  <td>
   <h3>Auto Saves & Trash Posts</h3>
   <div class="infobox infobox-blue2  small">
     <div class="infobox-icon">
        <i class="ace-icon dashicons dashicons-trash"></i>
     </div>
    <div class="infobox-data">
      <div class="infobox-text">Auto Saves Posts: <?php echo number_format($post_auto_trash_count['auto-save'], 0); ?></div>
      <div class="infobox-text">Trash Posts: <?php echo number_format($post_auto_trash_count['trash'], 0); ?></div>
    </div>
  </div>
  </td>
  <td class="db-action db-post-optimize">
<?php if ($post_auto_trash_count['auto-save'] > 0 || $post_auto_trash_count['trash'] > 0)  { ?>
       <p>There are currently <?php echo number_format($post_auto_trash_count['auto-save'], 0); ?> auto saved posts and <?php echo number_format($post_auto_trash_count['trash'], 0); ?> trash posts greater than <?php echo number_format($db_optimizer->retain_interval, 0); ?>  weeks old. You can remove these unnecessary posts, freeing up valuable space and increasing the speed and efficiency of your database by performing a post optimization.</p>
       <p class="dbo-problem">Oops Problem Found - Perform Post Optimization -->
         <input type="button" value="Perform Post Optimization" class="button button-primary button-action" name="db-post-optimize" data-action="db-post-optimize"></p>
<?php } else { ?>
       <p>Excellent! Your WordPress database currently has no auto saved or trash posts greater than <?php echo number_format($db_optimizer->retain_interval, 0); ?>  weeks old.</p>
       <p class="dbo-good">All good - no action required.</p>
<?php } ?>
  </td>
 </tr>
 <tr class="panel db-action">
  <td>
   <h3>Comments</h3>
   <div class="infobox infobox-blue2  small">
     <div class="infobox-icon">
        <i class="ace-icon dashicons dashicons-welcome-comments"></i>
     </div>
    <div class="infobox-data">
      <div class="infobox-text">Unapproved: <?php echo number_format($post_comments_count['unapproved'], 0); ?></div>
      <div class="infobox-text">Trash: <?php echo number_format($post_comments_count['trash'], 0); ?></div>
      <div class="infobox-text">Spam: <?php echo number_format($post_comments_count['spam'], 0); ?></div>
    </div>
  </div>
  </td>
  <td class="db-action db-post-comments">
<?php if ($post_comments_count['unapproved']> 0 || $post_comments_count['trash'] > 0 || $post_comments_count['spam'] > 0)  { ?>
       <p>There are currently <?php echo number_format($post_comments_count['unapproved'], 0); ?> unapproved comments, <?php echo number_format($post_comments_count['trash'], 0); ?> trash comments and  <?php echo number_format($post_comments_count['spam'], 0); ?> spam comments. You can remove these comments and increase the efficiency of your database by performing a comments optimization.</p>
       <p class="dbo-problem">Oops Problem Found - Perform Comments Optimization -->
         <input type="button" value="Perform Comments Optimization" class="button button-primary button-action" name="db-post-comments" data-action="db-post-comments"></p>
<?php } else { ?>
       <p>Perfect! Your WordPress database currently has no unapproved spam and trash comments.</p>
       <p class="dbo-good">All good - no action required.</p>
<?php } ?>
  </td>
 </tr>
 <tr class="panel db-action">
  <td>
   <h3>Orphaned Meta Data</h3>
   <div class="infobox infobox-blue2  small">
     <div class="infobox-icon">
        <i class="ace-icon dashicons dashicons-nametag"></i>
     </div>
    <div class="infobox-data">
      <div class="infobox-text">Post Meta Data: <?php echo number_format($orphaned_meta_data['post_meta'], 0); ?></div>
      <div class="infobox-text">Comments Meta Data: <?php echo number_format($orphaned_meta_data['comments_meta'], 0); ?></div>
    </div>
  </div>
  </td>
  <td class="db-action db-meta-data">
<?php if ($orphaned_meta_data['post_meta']> 0 || $orphaned_meta_data['comments_meta'] > 0)  { ?>
       <p>There are currently <?php echo number_format($orphaned_meta_data['post_meta'], 0); ?> orphaned post meta data and  <?php echo number_format($orphaned_meta_data['comments_meta'] , 0); ?> orphaned comments meta data. You can remove this orphaned meta data and increase the efficiency of your database by performing a meta data optimization.</p>
       <p class="dbo-problem">Oops Problem Found - Perform Meta Data Optimization -->
         <input type="button" value="Perform Meta Data Optimization" class="button button-primary button-action" name="db-meta-data" data-action="db-meta-data"></p>
<?php } else { ?>
       <p>Nice! Your WordPress database currently has no orphaned meta data.</p>
       <p class="dbo-good">All good - no action required.</p>
<?php } ?>
  </td>
 </tr>
  <tr>
   <td class="spacer" colspan="2"></td>
  </tr>
   <tr class="panel database-tables">
    <td colspan="2">
     <h3>Database Information</h3>
      <div class="table-list">
       <table class="widefat db-tables">
     	 <thead>
     		<tr>
     			<th>No.</th>
     			<th>Table</th>
     			<th class="right">Records</th>
     			<th class="right">Data Size</th>
     			<th class="right">Index Size</th>
     			<th class="right">Type</th>
     			<th class="right">Overhead</th>
     		</tr>
     	 </thead>
        <tbody>
 <?php
      	$total_gain = 0;
      	$no = 0;
      	$row_usage = 0;
      	$data_usage = 0;
      	$index_usage = 0;
      	$overhead_usage = 0;
      	$tablesstatus = $db_optimizer->tables;

      	foreach ($tablesstatus as $tablestatus) {

         $style = (0 == $no % 2) ? '' : ' class="alternate"';

         $no++;
         echo "<tr$style>";
         echo '<td>'.number_format_i18n($no).'</td>';
         echo "<td>".htmlspecialchars($tablestatus->Name)."</td>";
         echo '<td align="right">'.number_format_i18n($tablestatus->Rows).'</td>';
         echo '<td align="right">'.$db_optimizer->format_size($tablestatus->Data_length).'</td>';
         echo '<td align="right">'.$db_optimizer->format_size($tablestatus->Index_length).'</td>';;

         if ($tablestatus->Engine != 'InnoDB') {
           echo '<td align="right">'.htmlspecialchars($tablestatus->Engine).'</td>';
           echo '<td align="right">';
           $font_colour = (($tablestatus->Data_free>0) ? '#0000FF' : '#004600');
           echo '<span style="color:'.$font_colour.';">';
           echo $db_optimizer->format_size($tablestatus->Data_free);
           echo '</span>';
           echo '</td>';

           $overhead_usage += $tablestatus->Data_free;
           $total_gain += $tablestatus->Data_free;
         } else {
           echo '<td align="right">'.htmlspecialchars($tablestatus->Engine).'</td>';
           echo '<td align="right">';
           echo '<span style="color:#0000FF;">-</span>';
           echo '</td>';
         }

         $row_usage += $tablestatus->Rows;
         $data_usage += $tablestatus->Data_length;
         $index_usage +=  $tablestatus->Index_length;

         echo '</tr>';

       }

       echo '<tr class="thead">';
       echo '<th>Total:</th>';
       echo '<th>'.sprintf(_n('%d Table', '%d Tables', $no), number_format_i18n($no)).'</th>';
       echo '<th class="right">'.sprintf(_n('%d Record', '%d Records', $row_usage), number_format_i18n($row_usage)).'</th>';
       echo '<th class="right">'.$db_optimizer->format_size($data_usage).'</th>';
       echo '<th class="right">'.$db_optimizer->format_size($index_usage).'</th>';
       echo '<th class="right">'.'-'.'</th>';
       echo '<th class="right">';
       $font_colour = ($overhead_usage>0) ? '#0000FF' : '#004600';
       echo '<span style="color:'.$font_colour.'">'.$db_optimizer->format_size($overhead_usage).'</span>';
       echo '</th>';
 ?>
       </tbody>
       <thead>
        <tr>
         <th>No.</th>
         <th>Table</th>
   			<th class="right">Records</th>
   			<th class="right">Data Size</th>
   			<th class="right">Index Size</th>
   			<th class="right">Type</th>
   			<th class="right">Overhead</th>
       </tr>
      </thead>
     </table>
    </div>
   </td>
  </tr>
 </tbody>
</table>
</div>
