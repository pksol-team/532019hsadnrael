<?php
/**
 * Provide a admin area view for the site health check page
 *
 * This file is used to markup the admin-facing aspects of the site health check page.
 *
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/admin/partials
 */

include_once WPOPTI_PLUGIN_DIR . 'includes/site-check.php';
$siteCheck = new WPOptimiser_Site_Check();
$serverStats = $siteCheck->check();
// Update the last check options
update_option('wpoptimiser_last_heatlh_check', $serverStats);
//----------------------------
?>
<div class="wrap">
<div class="branding clearfix">
 <div class="logo">
  <img class="logo" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/wpopti-small-logo.png';?>">
  <span class="help-link"><a href="https://cybertactics.net/products" target="_blanl">Other Products</a> | <a href="https://cybertactics.net" target="_blanl">Blog</a></span>
 </div>
 <h1 class="page-title"><?php echo esc_html( get_admin_page_title() ); ?></h1>
</div>
<?php if(!empty($message)) { ?>
<?php echo $message;?>
<?php } ?>
<table class="form-table">
 <tbody>
   <tr class="info">
    <td colspan="2">
     <p class="info">Site Health Check is designed to give you an accurate overview of how good or bad your site environment is. It tests your software setup, server status + connectivity and gives you recommendations of what action to take.</p>
    </td>
   </tr>
  </tbody>
</table>
<table class="info-table">
  <tbody>
   <tr>
    <td class="label"><?php _e( 'Daily Site Health Monitoring'); ?></td>
    <td class="icon"></td>
    <td>
     <form method="post" action="">
     <input type="hidden" name="action" value="wpoptimiser_site_check_setup">
     <label>
      <input type="checkbox" class="ace ace-switch ace-switch-4 btn-empty" name="wpoptimiser-site-check-options[active_site_check]" value="Y" <?php checked( 'Y', $this->gen_settings['active_site_check']); ?>/>
      <span class="lbl"></span>
      <input type="submit" value="Save" class="button button-primary button-small" id="update" name="update">
     </label>
     <p class="description marginLeft8"><?php _e( 'When On, WP Optimiser will perform a site check once a day. Any problems found will be emailed to the site admins email address.'); ?></p>
     </form>
    </td>
   </tr>
   <tr>
    <td class="label">YOUR SERVER IP</td>
    <td class="icon green"><span class="dashicons dashicons-yes"></span></td>
    <td class="status green"><?php echo $serverStats['user_ip'];?></td>
   </tr>
<?php
    if($serverStats['shell_exec_enabled']) {
        $status = "green";
        $icon = '<span class="dashicons dashicons-yes"></span>';
        $desc = "All Fine";
    }
    else {
      $status = "red";
      $icon = '<span class="dashicons dashicons-no"></span>';
      $desc = "Your hosting setup appears to be restricted as PHP Shell Exec function is disabled, as a result some of the values below will show 'NA'. Speak to your host to get this function enabled.";
    }
?>
   <tr>
    <td class="label">PHP SHELL EXEC FUNCTION</td>
    <td class="icon <?php echo $status;?>"><?php echo $icon;?></td>
    <td class="status <?php echo $status;?>"><?php echo $desc; ?></td>
   </tr>
<?php
    if($serverStats['uptime'] == 'NA' || empty($serverStats['uptime'])) {
      $status = "amber";
      $icon = '<span class="dashicons dashicons-warning"></span>';
      $desc = " We are not able to determine the server uptime due to the issue with PHP Shell Exec Function detailed above.";
      $uptime = -1;
    }
    else {
      var_dump($serverStats['uptime']);
      $uptime = number_format_i18n( ( $serverStats['uptime']/60/60/24 ) );
      if($uptime < 14) {
        $status = "amber";
        $icon = '<span class="dashicons dashicons-warning"></span>';
        $desc = " Hmm it appears that your server has not been up very long... if this is regular contact your host to resolve the issue.";
      }
      else {
        $status = "green";
        $icon = '<span class="dashicons dashicons-yes"></span>';
        $desc = " days since your server was started.";
      }
    }
?>
   <tr>
    <td class="label">CURRENT SERVER UPTIME</td>
    <td class="icon <?php echo $status;?>"><?php echo $icon;?></td>
    <td class="status <?php echo $status;?> "><?php echo ($uptime > -1 ? ($uptime.' '._n( 'day', 'days', $uptime).$desc) : $desc)?></td>
   </tr>
<?php
		$phpversion = explode('.', phpversion());

    $status = "red";
    $icon = '<span class="dashicons dashicons-no"></span>';
    $desc = " You are Running an old Version of PHP. Speak to your host to upgrade this to the latest 5.6 version or some plugins & themes may not work. Please note we don't currently recommend PHP7 (yet)";

    if($phpversion[0] == 5 && $phpversion[1] >= 6) {
      $status = "green";
      $icon = '<span class="dashicons dashicons-yes"></span>';
      $desc = " You are Running The Most Compatible Version of PHP - Perfect!";
    }
    else if($phpversion[0] == 7 && $phpversion[1] >= 2) {
      $status = "green";
      $icon = '<span class="dashicons dashicons-yes"></span>';
      $desc = " Great! you are running the latest version of PHP - please note this may not be compatible with some plugins or themes - if so, ask your host to change your PHP to 5.6.3 (most compatible version).";
    }
?>
   <tr>
    <td class="label">PHP VERSION CHECK</td>
    <td class="icon <?php echo $status;?>"><?php echo $icon;?></td>
    <td class="status <?php echo $status;?>"><?php echo phpversion().$desc?></td>
   </tr>
<?php
    if($serverStats['ram_usage_pos'] == 'NA') {
      $status = "red";
      $icon = '<span class="dashicons dashicons-no"></span>';
      $desc = "";
    }
    else if($serverStats['ram_usage_pos'] < 80) {
      $status = "green";
      $icon = '<span class="dashicons dashicons-yes"></span>';
      $desc = " - All Fine";
    }
    else if($serverStats['ram_usage_pos'] < 90) {
      $status = "amber";
      $icon = '<span class="dashicons dashicons-warning"></span>';
      $desc = " - All Fine";
    }
    else {
      $status = "red";
      $icon = '<span class="dashicons dashicons-no"></span>';
      $desc = " - You are close to your servers memory Limit - You should consider upgrading your RAM (speak to your host)";
    }
?>
   <tr>
    <td class="label">REAL TIME MEMORY USAGE</td>
    <td class="icon <?php echo $status;?>"><?php echo $icon;?></td>
    <td class="status <?php echo $status;?>">
<?php if ($serverStats['ram_usage_pos'] != 'NA') { ?>
        <div class="progress">
            <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $serverStats['ram_usage_pos']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $serverStats['ram_usage_pos']; ?>%"></div>
        </div>
<?php } ?>
      Total System Memory = <?php echo $serverStats['total_ram']; ?> / <?php echo $serverStats['used_ram']; ?> = <?php echo $serverStats['ram_usage_pos']; ?>% Usage <?php echo $desc; ?></td>
   </tr>
<?php
    if($serverStats['memory_usage_pos'] < 80) {
      $status = "green";
      $icon = '<span class="dashicons dashicons-yes"></span>';
      $desc = " - All Fine";
    }
    else if($serverStats['memory_usage_pos'] < 90) {
      $status = "amber";
      $icon = '<span class="dashicons dashicons-warning"></span>';
      $desc = " - All Fine";
    }
    else {
      $status = "red";
      $icon = '<span class="dashicons dashicons-no"></span>';
      $desc = " - You are close to your sites PHP memory Limit - Your should increase your PHP Memory Limit setting. (speak to your host)";
    }
?>
   <tr>
    <td class="label">REAL TIME PHP MEMORY USAGE</td>
    <td class="icon <?php echo $status;?>"><?php echo $icon;?></td>
    <td class="status <?php echo $status;?>">
      <div class="progress">
          <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $serverStats['memory_usage_pos']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $serverStats['memory_usage_pos']; ?>%"></div>
      </div>
      Total Available PHP Memory = <?php echo $serverStats['memory_usage_total']; ?>M / <?php echo $serverStats['memory_usage_MB']; ?>M = <?php echo $serverStats['memory_usage_pos']; ?>% Usage <?php echo $desc; ?></td>
   </tr>
<?php
    if($serverStats['cpu_load'] == 'NA') {
      $status = "red";
      $icon = '<span class="dashicons dashicons-no"></span>';
      $desc = "";
    }
    else if($serverStats['cpu_load'] < 80) {
      $status = "green";
      $icon = '<span class="dashicons dashicons-yes"></span>';
      $desc = " - All Fine";
    }
    else if($serverStats['cpu_load'] < 90) {
      $status = "amber";
      $icon = '<span class="dashicons dashicons-warning"></span>';
      $desc = " - All Fine";
    }
    else {
      $status = "red";
      $icon = '<span class="dashicons dashicons-no"></span>';
      $desc = " - You are close to your servers Load Limit - You should consider upgrading CPU & number of available cores (speak to your host)";
    }
?>
   <tr>
    <td class="label">REAL TIME CPU LOAD</td>
    <td class="icon <?php echo $status;?>"><?php echo $icon;?></td>
    <td class="status <?php echo $status;?>">
<?php if($serverStats['cpu_load'] != 'NA') {   ?>
      <div class="progress">
          <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $serverStats['cpu_load']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $serverStats['cpu_load']; ?>%"></div>
      </div>
<?php } ?>
      <?php echo  $serverStats['cpu_count']; ?> CPU's / <?php echo  $serverStats['cpu_core_count']; ?> Cores - <?php echo $serverStats['cpu_load'];?>% Usage<?php echo $desc; ?></td>
   </tr>
<?php
    if($serverStats['disk_free_pos'] < 80) {
      $status = "green";
      $icon = '<span class="dashicons dashicons-yes"></span>';
      $desc = " - All Fine";
    }
    else if($serverStats['disk_free_pos'] < 90) {
      $status = "amber";
      $icon = '<span class="dashicons dashicons-warning"></span>';
      $desc = " - All Fine";
    }
    else {
      $status = "red";
      $icon = '<span class="dashicons dashicons-no"></span>';
      $desc = " - You are close to your servers storage capacity - You should consider Increasing your Disk Size (speak to your host)";
    }
?>
   <tr>
    <td class="label">ACTUAL DISK USAGE</td>
    <td class="icon <?php echo $status;?>"><?php echo $icon;?></td>
    <td class="status <?php echo $status;?>">
      <div class="progress">
          <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $serverStats['disk_free_pos']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $serverStats['disk_free_pos']; ?>%"></div>
      </div>
      Total Storage = <?php echo $siteCheck->format_filesize_kB($serverStats['disk_total_space'] / 1024); ?> / <?php echo $siteCheck->format_filesize_kB($serverStats['disk_used_space'] / 1024, 2); ?> - <?php echo $serverStats['disk_free_pos'];?>% Usage<?php echo $desc; ?></td>
   </tr>
<?php
    if($serverStats['php_max_upload'] < 2097152) {
      $status = "amber";
      $icon = '<span class="dashicons dashicons-warning"></span>';
      $desc = " - You may have difficulties uploading large themes or plugins, if so talk to your host to increase this - it's free (suggest 16Mb)";
    }
    else if($serverStats['php_max_upload'] < 16777216) {
      $status = "green";
      $icon = '<span class="dashicons dashicons-yes"></span>';
      $desc = " - All Fine. We recommend you ask your host to raise this to 16mb to cope with bigger themes and plugins.";
    }
    else {
      $status = "green";
      $icon = '<span class="dashicons dashicons-yes"></span>';
      $desc = " - All Fine";
    }
?>
   <tr>
    <td class="label">MAX FILE UPLOAD SIZE</td>
    <td class="icon <?php echo $status;?>"><?php echo $icon;?></td>
    <td class="status <?php echo $status;?>"><?php echo $siteCheck->format_filesize_kB($serverStats['php_max_upload'] / 1024); ?><?php echo $desc; ?></td>
   </tr>
<?php $serverStats['php_max_execution_time']=60;
    if($serverStats['php_max_execution_time'] < 30) {
      $status = "amber";
      $icon = '<span class="dashicons dashicons-warning"></span>';
      $desc = " - You may have issues backing-up large sites or sites with loads of media, if so talk to your host to increase this - it's free (suggest 180s)";
    }
    else if($serverStats['php_max_execution_time'] < 180) {
      $status = "green";
      $icon = '<span class="dashicons dashicons-yes"></span>';
      $desc = " - All Fine. We recommend you ask your host to raise this to 180 seconds if you are running backup scripts on larger sites.";
    }
    else {
      $status = "green";
      $icon = '<span class="dashicons dashicons-yes"></span>';
      $desc = " - All Fine";
    }
?>
   <tr>
    <td class="label">PHP MAX EXECUTION TIME</td>
    <td class="icon <?php echo $status;?>"><?php echo $icon;?></td>
    <td class="status <?php echo $status;?>"><?php echo $serverStats['php_max_execution_time']; ?> sec<?php echo $desc; ?></td>
   </tr>
<?php
    $desc = "This test shows you the response time from your host to these famous news networks. This should be 40ms or less in the continent closest to your server & audience.";
    if($serverStats['ping_times'][0] == false) {
      $status = "red";
      $icon = '<span class="dashicons dashicons-no"></span>';
      $desc = "We cannot determine Ping Times.";
    }
    else if(max($serverStats['ping_times']) < 40) {
      $status = "green";
      $icon = '<span class="dashicons dashicons-yes"></span>';
    }
    else if(max($serverStats['ping_times']) < 100) {
      $status = "amber";
      $icon = '<span class="dashicons dashicons-warning"></span>';
    }
    else {
      $status = "red";
      $icon = '<span class="dashicons dashicons-no"></span>';
      $desc.= "<br><br>100ms+ We strongly recommend you change host to one with a better peering agreements & move your site closer to your target audience.";
    }
?>
   <tr class="thin">
    <td class="label">HOST CONNECTIVITY SPEED CHECK</td>
    <td class="icon <?php echo $status;?>"><?php echo $icon;?></td>
    <td class="status <?php echo $status;?>"><?php echo $desc;?></td>
  </tr>

<?php
  if($serverStats['ping_times'][0] !== false) {
    $status = $serverStats['ping_times'][0] < 100 ? ($serverStats['ping_times'][0] < 40 ? 'green' : 'amber') : 'red';
    echo '<tr class="thin"><td class="label"></td><td class="icon '.$status.'"><td class="status '.$status.'">Europe: bbc.co.uk - '.$serverStats['ping_times'][0].'ms</td></tr>';
    $status = $serverStats['ping_times'][1] < 100 ? ($serverStats['ping_times'][1] < 40 ? 'green' : 'amber') : 'red';
    echo '<tr class="thin"><td class="label"></td><td class="icon '.$status.'"><td class="status '.$status.'">America: foxnews.com - '.$serverStats['ping_times'][1].'ms</td></tr>';
    $status = $serverStats['ping_times'][2] < 100 ? ($serverStats['ping_times'][2] < 40 ? 'green' : 'amber') : 'red';
    echo '<tr class="thin"><td class="label"></td><td class="icon '.$status.'"><td class="status '.$status.'">Middle East: www.aljazeera.com - '.$serverStats['ping_times'][2].'ms</td></tr>';
    $status = $serverStats['ping_times'][3] < 100 ? ($serverStats['ping_times'][3] < 40 ? 'green' : 'amber') : 'red';
    echo '<tr class="thin"><td class="label"></td><td class="icon '.$status.'"><td class="status '.$status.'">Asia: www.channelnewsasia.com - '.$serverStats['ping_times'][3].'ms</td></tr>';
  }
?>
    </td>
   </tr>
<?php

    if($serverStats['active_plugins'] < 20) {
      $status = "green";
      $icon = '<span class="dashicons dashicons-yes"></span>';
      $desc = "";
    }
    else if($serverStats['active_plugins'] < 40) {
      $status = "amber";
      $icon = '<span class="dashicons dashicons-warning"></span>';
      $desc = "Oops - you are running a high number of plugins this reduces your site speed, exposes it to exploits + increases the likelihood of things not working - deactivate any redundant plugins - we recommend running 20 or less.";
    }
    else {
      $status = "red";
      $icon = '<span class="dashicons dashicons-no"></span>';
      $desc = "Warning - you are running very high number of plugins this reduces your site speed & exposes it to exploits + increases the likelihood of things not working - deactivate any redundant plugins - we recommend running 20 or less.";
    }
?>
   <tr>
    <td class="label">ACTIVE PLUGINS</td>
    <td class="icon <?php echo $status;?>"><?php echo $icon;?></td>
    <td class="status <?php echo $status;?>"><?php echo $serverStats['active_plugins']; ?> <?php echo $desc; ?></td>
   </tr>
  </tbody>
</table>

</div>
