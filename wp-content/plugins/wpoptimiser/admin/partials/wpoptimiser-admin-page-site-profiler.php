<?php
/**
 * Provide a admin area view for the lazy load images settings
 *
 * This file is used to markup the admin-facing aspects of the plugin lazy load images settings page.
 *
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/admin/partials
 */

global $wp_version;

$ip = '';

if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
  //check ip from share internet
  $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
  //to check ip is pass from proxy
  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
  $ip = $_SERVER['REMOTE_ADDR'];
}

// CHECK FOR IPV6 address and deal with it?

// See if requested to delete a profile
if ( isset($_REQUEST['deleteprofile']) ) {
  delete_option('wpopti_profiler_scan_'.$_REQUEST['deleteprofile']);
  $updates = "Profile has been deleted";
}

// Show the requested profile or latest one if none selected
$viewProfileKey = isset($_REQUEST['viewprofile']) ? $_REQUEST['viewprofile'] : 0;
$lastProfileData = null;
$profiles = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE  'wpopti_profiler_scan_%' ORDER BY option_id");
if(count($profiles) > 0 ) {
  $profiles = array_reverse ($profiles);
  $lastProfile = $profiles[$viewProfileKey];
  $lastProfileData = new WPOptimiser_Scan_Parser( $lastProfile->option_value );
  arsort($lastProfileData->plugin_times);
}
?>
<script>
var WPOPSP_Pages =  <?php echo json_encode( $pages); ?>;
var WPOPSP_IP = '<?php echo $ip;?>';
var WPOPSP_Location = '<?php echo esc_url( menu_page_url('wpoptimiser-site-profiler', false) );?>';
<?php if ( !empty($lastProfileData) ){ ?>
  var data_plugin_runtime = [
  <?php foreach ( $lastProfileData->plugin_times as $k => $v ) { ?>
    	{	label: "<?php echo esc_js( $k ); ?> (<?php echo esc_js( $v ); ?> sec)",	data: <?php echo $v; ?>},
  <?php } ?>
  ];
	jQuery( document ).ready( function( $) {
    $("a.delete-profile").click(function(evt) {
			if (confirm( 'Are you sure you want to delete this profile?' )) {
        return true;
			}
      return false;
		});
    $.plot( $("#wpopti-pie-chart" ), data_plugin_runtime,
  		{	series: {	pie: { show: true,	combine: { threshold: .03 }, } },
  			grid: { hoverable: true },
        tooltip: { show: true, content: "%p.0%, %s", shifts: { x: 20, y: 0 },
        defaultTheme: false
      },
  		legend: { container: $( "#wpopti-pie-chart-legend" )}
		});
  });
<?php } ?>
</script>
<div class="wrap">
<div class="branding clearfix">
 <div class="logo">
  <img class="logo" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/wpopti-small-logo.png';?>">
  <span class="help-link"><a href="https://cybertactics.net/products" target="_blanl">Other Products</a> | <a href="https://cybertactics.net" target="_blanl">Blog</a></span>
 </div>
 <h1 class="page-title"><?php echo esc_html( get_admin_page_title() ); ?></h1>
</div>
<?php if ( version_compare( $wp_version, '4.5', '<' ) ) {
  echo '<div class="notice notice-error">This Plugin requires at least version 4.5 of WordPress. Please update your WordPress to the latest version in order to avoid any compatibility issues.</div>';
}
?>
<?php if(!empty($errors)) { ?>
<div class="notice notice-error"><?php echo $errors;?></div>
<?php } ?>
<?php if(!empty($updates)) { ?>
<div class="notice notice-info"><?php echo $updates;?></div>
<?php } ?>
<form method="post" action="">
<input type="hidden" name="action" value="wpoptimiser_save_lazyload_setup">
 <table class="form-table profiler">
  <tbody>
    <tr class="info">
     <td>
      <p class="info"><?php echo __('This section looks at the speed impact of your WordPress core, themes & plugins.  It also shows you a comparison of your site against a benchmark site with a default set of plugins and WordPress Default theme.<br><br>The objective here is to look and spot any slow themes & plugins and replace these with better options');?></p>
      <p class="submit"><input id="start-site-profile" type="button" value="Start Profiling" class="button button-primary"></p>
     </td>
    </tr>
    <tr class="panel hidden">
     <td>
       <iframe id="wpopti-profiler-frame" width="100%" height="400px" frameborder="1" src=""></iframe>
     </td>
    </tr>
    <tr class="panel">
     <td>
<?php if ( !empty($lastProfile) ){?>
<div class="panel pull-left">
  <div class="panel-body">
   <div class="panel-header">
    <h3 class="align-center">Your Site Speed Breakdown</h3>
   </div>
       <h4 class="text-muted">Profile Created <?php echo date(get_option( 'date_format' ),$lastProfileData->report_date).' '.date(get_option( 'time_format' ),$lastProfileData->report_date);?></h4>
    <span>Site Load Time</small></span><span class="pull-right value"><?php echo number_format($lastProfileData->averages['site'],4).' sec';?></span>
    <div class="progress">
        <div class="progress-bar progress-bar-primary" role="progressbar" style="width: 100%"></div>
    </div><!-- /.progress -->
    <span>Plugins Load Time</span><span class="pull-right"><?php echo number_format($lastProfileData->averages['plugins'], 4).' sec';?></span>
    <div class="progress">
        <div class="progress-bar progress-bar-warning" role="progressbar" style="width: <?php echo $lastProfileData->averages['perc_site']['plugins'];?>%"></div>
    </div><!-- /.progress -->
    <span>Theme Load Time</span><span class="pull-right"><?php echo number_format($lastProfileData->averages['theme'], 4).' sec';?></span>
    <div class="progress">
        <div class="progress-bar progress-bar-success" role="progressbar" style="width: <?php echo $lastProfileData->averages['perc_site']['theme'];?>%"></div>
    </div><!-- /.progress -->
    <span>Wordpress Core Load Time</span><span class="pull-right"><?php echo number_format($lastProfileData->averages['core'], 4).' sec';?></span>
    <div class="progress last">
        <div class="progress-bar progress-bar-info" role="progressbar" style="width: <?php echo $lastProfileData->averages['perc_site']['core'];?>%"></div>
    </div><!-- /.progress -->
    <h3>Plugin Speed Impact</h3>
    <div class="no-progress clearfix"></div>
<?php
  foreach ( $lastProfileData->plugin_times as $k => $v ) {
  $pluginPerc = round($v / $lastProfileData->averages['plugins'] * 100, 2);
?>
    <span><?php echo $k;?></span><span class="pull-right"><?php echo number_format($v, 4).' sec';?></span>
    <div class="progress">
        <div class="progress-bar progress-bar-warning" role="progressbar" style="width: <?php echo $pluginPerc;?>%"></div>
    </div><!-- /.progress -->
<?php } ?>
  <hr>
  <span>Plugins Impact on Site Load Time</span><span class="pull-right"><?php echo $lastProfileData->averages['perc_site']['plugins'];?>%</span>
  <div class="no-progress clearfix"></div>
  <span>Total Active Plugins</span><span class="pull-right"><?php echo $lastProfileData->active_plugins; ?></span>

  </div><!-- /.panel-body -->
 </div>
<?php
$arrows = Array('up' => '&#8593;', 'down' => '&#8595;', 'amber' => '&#8595;');

$benchmarkData['site'] = 2.6000;
$benchmarkData['plugins'] = 0.6000;
$benchmarkData['theme'] = 1.5000;
$benchmarkData['core'] = 0.5000;

$profileVBenchData['site'] = $lastProfileData->averages['site'] - $benchmarkData['site'];
$profileVBenchData['perc']['site'] = (1-($lastProfileData->averages['site'] / $benchmarkData['site'])) * 100;
if($lastProfileData->averages['site'] >= $benchmarkData['site'] && $profileVBenchData['perc']['site'] < -5.00) {
  if($profileVBenchData['perc']['site'] < -26)
    $benchmarkData['arrow']['site'] = 'amber';
  else
    $benchmarkData['arrow']['site'] = 'down';
}
else
  $benchmarkData['arrow']['site'] = 'up';

$profileVBenchData['plugins'] = $lastProfileData->averages['plugins'] - $benchmarkData['plugins'];
$profileVBenchData['perc']['plugins'] = (1-($lastProfileData->averages['plugins'] / $benchmarkData['plugins'])) * 100;

if($lastProfileData->averages['plugins'] >= $benchmarkData['plugins'] && $profileVBenchData['perc']['plugins'] < -5.00) {
  if($profileVBenchData['perc']['plugins'] < -26)
    $benchmarkData['arrow']['plugins'] = 'amber';
  else
    $benchmarkData['arrow']['plugins'] = 'down';
}
else
  $benchmarkData['arrow']['plugins'] = 'up';
$benchmarkData['perc_total']['plugins'] = round($benchmarkData['plugins'] / $benchmarkData['site'] * 100, 2);

$profileVBenchData['theme'] = $lastProfileData->averages['theme'] - $benchmarkData['theme'];
$profileVBenchData['perc']['theme'] = (1-($lastProfileData->averages['theme'] / $benchmarkData['theme'])) * 100;
if($lastProfileData->averages['theme'] >= $benchmarkData['theme'] && $profileVBenchData['perc']['theme'] < -5.00) {
  if($profileVBenchData['perc']['theme'] < -26)
    $benchmarkData['arrow']['theme'] = 'amber';
  else
    $benchmarkData['arrow']['theme'] = 'down';
}
else
  $benchmarkData['arrow']['theme'] = 'up';
$benchmarkData['perc_total']['theme'] = round($benchmarkData['theme'] / $benchmarkData['site'] * 100, 2);

$profileVBenchData['core'] = $lastProfileData->averages['core'] - $benchmarkData['core'];
$profileVBenchData['perc']['core'] = (1-($lastProfileData->averages['core'] / $benchmarkData['core'])) * 100;
if($lastProfileData->averages['core'] >= $benchmarkData['core'] && $profileVBenchData['perc']['core'] < -5.00) {
  if($profileVBenchData['perc']['core'] < -26)
    $benchmarkData['arrow']['core'] = 'amber';
  else
    $benchmarkData['arrow']['core'] = 'down';
}
else
  $benchmarkData['arrow']['core'] = 'up';
$benchmarkData['perc_total']['core'] = round($benchmarkData['core'] / $benchmarkData['core'] * 100, 2);

?>
<div class="panel pull-left">
  <div class="panel-body">
  <h3 class="align-center">Your Site V Benchmark Site</small></h3>
   <table class="benchmark-stats">
     <tr>
      <td class="profile-stat align-center">Your Site<br>Comparison
      </td>
      <td class="profile-stat">
      <span class="pull-right">Benchmark<br>Site Speed</span>
      </td>
     </tr>
    <tr>
     <td class="profile-stat align-right arrow-<?php echo $benchmarkData['arrow']['site'];?>"><?php echo sprintf("%+0.4f",$profileVBenchData['site']);?><span class="stat-arrow"><?php echo ($profileVBenchData['perc']['site'] >= 0.00 ? $arrows['up'] : $arrows['down']);?></span>
     </td>
     <td class="profile-stat">
       <span>Site Load Time</span><span class="pull-right"><?php echo number_format($benchmarkData['site'], 4).' sec';?></span>
       <div class="progress">
         <div class="progress-bar progress-bar-primary" role="progressbar" style="width: 100%"></div>
       </div><!-- /.progress -->
     </td>
    </tr>
    <tr>
     <td class="profile-stat align-right arrow-<?php echo $benchmarkData['arrow']['plugins'];?>"><?php echo sprintf("%+0.4f",$profileVBenchData['plugins']);?><span class="stat-arrow"><?php echo ($profileVBenchData['perc']['plugins'] >= 0.00 ? $arrows['up'] : $arrows['down']);?></span>
     </td>
     <td class="profile-stat">
       <span>Plugins Load Time</span><span class="pull-right"><?php echo number_format($benchmarkData['plugins'], 4).' sec';?></span>
       <div class="progress">
         <div class="progress-bar progress-bar-warning" role="progressbar" style="width: <?php echo $benchmarkData['perc_total']['plugins'];?>%"></div>
       </div><!-- /.progress -->
     </td>
    </tr>
    <tr>
     <td class="profile-stat align-right arrow-<?php echo $benchmarkData['arrow']['theme'];?>"><?php echo sprintf("%+0.4f",$profileVBenchData['theme']);?><span class="stat-arrow"><?php echo ($profileVBenchData['perc']['theme'] >= 0.00 ? $arrows['up'] : $arrows['down']);?></span>
     </td>
     <td class="profile-stat">
       <span>Theme Load Time</span><span class="pull-right"><?php echo number_format($benchmarkData['theme'], 4).' sec';?></span>
       <div class="progress">
         <div class="progress-bar progress-bar-success" role="progressbar" style="width: <?php echo $benchmarkData['perc_total']['theme'];?>%"></div>
       </div><!-- /.progress -->
     </td>
    </tr>
    <tr>
     <td class="profile-stat align-right arrow-<?php echo $benchmarkData['arrow']['core'];?>"><?php echo sprintf("%+0.4f",$profileVBenchData['core']);?><span class="stat-arrow"><?php echo ($profileVBenchData['perc']['core'] >= 0.00 ? $arrows['up'] : $arrows['down']);?></span>
     </td>
     <td class="profile-stat">
       <span>Wordpress Core  Load Time</span><span class="pull-right"><?php echo number_format($benchmarkData['core'], 4).' sec';?></span>
       <div class="progress">
         <div class="progress-bar progress-bar-info" role="progressbar" style="width: <?php echo $benchmarkData['perc_total']['core'];?>%"></div>
       </div><!-- /.progress -->
     </td>
    </tr>
    <tr>
      <td colspan="2"><h3 class="align-center" style="margin-top:10px;">Overall Results</h3></td>
    <tr>
     <td class="profile-stat align-right arrow-<?php echo $benchmarkData['arrow']['site'];?>"><?php echo sprintf("%0.0f%%",abs($profileVBenchData['perc']['site'])); ?> <?php echo ($profileVBenchData['perc']['site'] >= 0.00 ? 'Faster' : 'Slower');?><span class="stat-arrow">&nbsp;</span></span>
     </td>
     <td><span class="status">Than the Benchmark Site</span>
     </td>
   </tr>
    <tr>
     <td class="profile-stat align-right arrow-<?php echo $benchmarkData['arrow']['plugins'];?>"><?php echo sprintf("%0.0f%%",abs($profileVBenchData['perc']['plugins'])); ?> <?php echo ($profileVBenchData['perc']['plugins'] > 0.00 ? 'Faster' : 'Slower');?><span class="stat-arrow">&nbsp;</span></span>
     </td>
     <td><span class="status">Plugin Speed Impact</span>
     </td>
   </tr>
   <tr>
    <td class="profile-stat align-right arrow-<?php echo $benchmarkData['arrow']['theme'];?>"><?php echo sprintf("%0.0f%%",abs($profileVBenchData['perc']['theme'])); ?> <?php echo ($profileVBenchData['perc']['theme'] >= 0.00 ? 'Faster' : 'Slower');?><span class="stat-arrow">&nbsp;</span></span></span>
    </td>
    <td><span class="status">Theme Speed Impact</span>
    </td>
  </tr>
    </table>
   <hr>
    <h3 class="align-center">Your Site Plugin Speed Overview</h3>
    <div id="wpopti-pie-chart"></div>
    <div id="wpopti-pie-chart-legend"></div>
   </div>
  </div>
 </td>
</tr>
<tr class="panel">
 <td>
<?php
} // End Last Profiles Stats section
else { ?>
       You have not created your first profile yet - Click the 'Start Profiling' button to create one.
<?php } ?>
 </td>
</tr>
<?php if(count($profiles) > 0 ) { ?>
<tr class="divider">
  <td>
    <hr>
  </td>
</tr>
<tr class="panel header">
 <td>
   <h3>Previous Profiles</h3>
   <table class="widefat db-tables">
    	 <thead>
    		<tr>
    			<th>Name</th>
    			<th>Date</th>
    			<th>Actions</th>
    		</tr>
    	 </thead>
       <tbody>
<?php

foreach ($profiles as $key => $value) {

  $profileName = str_replace('wpopti_', '', $value->option_name);
  $profileTimestamp = str_replace('wpopti_profiler_scan_', '', $value->option_name);
  $profileTime = date( 'D, M jS', $profileTimestamp ) . ' at ' . date( 'g:i a', $profileTimestamp );

  if($viewProfileKey == $key)
    $action = '<a class="delete-profile" href="'.esc_url( remove_query_arg('viewprofile', add_query_arg( array(	'deleteprofile' => $profileTimestamp ) ) ) ).'">Delete</a>';
  else
    $action = '<a href="'.esc_url( remove_query_arg('deleteprofile', add_query_arg( array(	'viewprofile' => $key ) ) ) ).'">View</a> | <a class="delete-profile" href="'.esc_url( remove_query_arg('viewprofile',  add_query_arg( array(	'deleteprofile' => $profileTimestamp ) ) ) ).'">Delete</a>';

  echo '<tr><td>'.$profileName.'</td><td>'.$profileTime.'</td><td>'.$action.'</td></tr>';
}
?>
</tbody>
      <thead>
       <tr>
  			 <th>Name</th>
  			 <th>Date</th>
  			 <th>Actions</th>
      </tr>
     </thead>
    </table>

   <?php // var_dump($profiles); ?>
</td>
</tr>
<?php } ?>
  </tbody>
 </table>
</form>
<?php // var_dump($lastProfileData); ?>
<?php // var_dump($page_runtimes_stats); ?>

</div>
