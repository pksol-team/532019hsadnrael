<?php
/**
 * The class that performs the site check
 *
 * @link       http://wpoptimiser.com
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/includes
 */

class WPOptimiser_Site_Check {

  /**
   * A list of the Wordpress tables and their status.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $ip    The IP Address of the current running instance
   */
	private static $ip = '';

	/**
	 * Constructor
	 * Initialize the object, figure out if profiling is enabled, and if so,
	 * start the profile.
	 * @return WPOptimiser_Site_Check
	 */
	public function __construct() {

    // Find the users IP address
    self::$ip = '';

    if ( ! empty( $_SERVER['SERVER_ADDR'] ) ) {
      //check ip from share internet
      self::$ip = $_SERVER['SERVER_ADDR'];
    } elseif ( ! empty( $_SERVER['LOCAL_ADDR'] ) ) {
      //to check ip is pass from proxy
      self::$ip = $_SERVER['LOCAL_ADDR'];
    }
  }

  public function check() {

    // Do Ping tests
    $pingtime[0] = $this->ping('bbc.co.uk');
    $pingtime[1] = $this->ping('foxnews.com');
    $pingtime[2] = $this->ping('www.aljazeera.com');
    $pingtime[3] = $this->ping('www.channelnewsasia.com');

    // Get number of active plugin_calls
    $active_plugins = count(wp_get_active_and_valid_plugins());

    // Work out storage stats
    $disk_total_space = disk_total_space(WPOPTI_PLUGIN_DIR);
    $disk_used_space  = $disk_total_space - disk_free_space(WPOPTI_PLUGIN_DIR);
    $disk_free_pos = round( (	(	$disk_used_space 	/ $disk_total_space	) * 100	), 0 );

    // Get PHP limitations`
    $memory_usage_total = $this->get_php_memory_limit() / 1024 / 1024;
    $memory_usage_MB = function_exists('memory_get_usage') ? round(memory_get_usage() / 1024 / 1024, 2) : 0;
    $memory_usage_pos = round ( ( ( $memory_usage_MB / $memory_usage_total ) * 100 ), 0);

    /* If shell_exec available then gather the CPU Load, Memory Load, RAM Load and Uptime */
    if( $this->is_shell_exec_Enabled() ) {
      $cpu_load = $this->getServerLoad();

      $serverRam = $this->getServerMemoryUsage();
      if(!is_null($serverRam)) {
        $total_ram_server = $this->format_filesize_kB($serverRam['total'] / 1024);
        $free_ram_server = $this->format_filesize_kB($serverRam['free'] / 1024);
        $used_ram_server = $this->format_filesize_kB($serverRam['used'] / 1024, 2);
        $ram_usage_pos = round($serverRam['perc'], 2);
      }

    	$uptime = trim( $this->wp_shell_exec("cut -d. -f1 /proc/uptime") );

    /* Otherwise just run the memory load check */
    } else {
      $cpu_load = "NA";
    	$total_ram_server = 'NA';
    	$free_ram_server = 'NA';
      $used_ram_server = 'NA';
    	$ram_usage_pos = 'NA';
      $total_ram_server = 'NA';
      $uptime = 'NA';
    }

    $serverStats = array (
        'time' => time(),
        'user_ip' => self::$ip,
        'shell_exec_enabled' => $this->is_shell_exec_Enabled(),
    		'cpu_load'			=> $cpu_load,
        'cpu_count'     => $this->get_cpu_count(),
        'cpu_core_count' => $this->get_core_count(),
        'memory_usage_total' => $memory_usage_total,
    		'memory_usage_MB'	=> $memory_usage_MB,
    		'memory_usage_pos'	=> $memory_usage_pos,
    		'total_ram'			=> $total_ram_server,
    		'free_ram'			=> $free_ram_server,
        'used_ram'			=> $used_ram_server,
    		'ram_usage_pos'		=> $ram_usage_pos,
    		'uptime' 			=> $uptime,
        'disk_total_space' => $disk_total_space,
        'disk_used_space'  => $disk_used_space,
        'disk_free_pos' => $disk_free_pos,
        'php_max_upload' => $this->php_max_upload_size(),
        'php_max_execution_time' =>$this->php_max_execution_time(),
        'ping_times' => $pingtime,
        'active_plugins' => $active_plugins,
    	);

      return $serverStats;
  }

  // Wrapper around shell_exec
  public function wp_shell_exec($cmd) {
    //return shell_exec("C:\cygwin64\bin\bash.exe --login  -c \"".$cmd."\"");
    return shell_exec($cmd);
  }

  // Check if shell_exec() is enabled on the server
  public function is_shell_exec_Enabled() {

    if( function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(', ', ini_get('disable_functions')))) && strtolower(ini_get('safe_mode')) != 1 ) {
    	/* If enabled, check if shell_exec() can be executed */
    	$returnVal = $this->wp_shell_exec('echo "hello world"');
    	if( !empty( $returnVal ) ) {
    		return true;
    	} else {
    		return false;
    	}
    } else {
    	return false;
    }
  }

  function getServerMemoryUsage() {
    $memoryTotal = null;
    $memoryFree = null;

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Get total physical memory (this is in bytes)
        $cmd = "wmic ComputerSystem get TotalPhysicalMemory";
        $outputTotalPhysicalMemory = $this->wp_shell_exec($cmd);

        // Get free physical memory (this is in kibibytes!)
        $cmd = "wmic OS get FreePhysicalMemory";
        $outputFreePhysicalMemory = $this->wp_shell_exec($cmd);

        if ($outputTotalPhysicalMemory && $outputFreePhysicalMemory) {
          // Find total value
          if (preg_match("/^[0-9]+/m", $outputTotalPhysicalMemory, $matches)) {
              $memoryTotal = $matches[0];
          }

          // Find free value
          if (preg_match("/^[0-9]+/m", $outputFreePhysicalMemory, $matches)) {
              $memoryFree = $matches[0];
              $memoryFree *= 1024;
          }
        }
    }
    else
    {
        if (is_readable("/proc/meminfo"))
        {
        		$memoryTotal = $this->wp_shell_exec( "grep -w 'MemTotal' /proc/meminfo | grep -o -E '[0-9]+'" );
            $memoryFree = $this->wp_shell_exec( "grep -w 'MemFree' /proc/meminfo | grep -o -E '[0-9]+'" );
        }
    }

    if (is_null($memoryTotal) || is_null($memoryFree)) {
        return null;
    } else {
        $memoryPerc = (100 - ($memoryFree * 100 / $memoryTotal));
        return array(
            "total" => $memoryTotal,
            "free" => $memoryFree,
            "used" => $memoryTotal-$memoryFree,
            "perc" => $memoryPerc
        );
    }
  }

  public function get_cpu_count() {

  	$cpu_count = get_transient( 'wpopti_cpu_count' );
  	if( $cpu_count !== FALSE ) return $cpu_count;

    if ('WIN' == strtoupper(substr(PHP_OS, 0, 3))) {
      $process = $this->wp_shell_exec('wmic computersystem get numberofprocessors');
      if (false !== $process) {
        if (preg_match("/^[0-9]+/m", $process, $matches)) {
            $cpu_count = $matches[0];
        }
      }
    }
    else if (is_readable('/proc/cpuinfo')) {
  		$cpu_count = $this->wp_shell_exec("cat /proc/cpuinfo |grep 'physical id' | sort | uniq | wc -l");
  	}

  	set_transient( 'wpopti_cpu_count', $cpu_count, DAY_IN_SECONDS );

  	return $cpu_count;
  }

  function get_core_count() {

    $cpu_count = get_transient( 'wpopti_cpu_core_count' );
    if( $cpu_count !== FALSE ) return $cpu_count;

    $cpu_count = 1;
    if ('WIN' == strtoupper(substr(PHP_OS, 0, 3))) {
      $process = $this->wp_shell_exec('wmic cpu get NumberOfCores');
      if (false !== $process) {
        if (preg_match("/^[0-9]+/m", $process, $matches)) {
            $cpu_count = $matches[0];
        }
      }
    }
    else if (is_readable('/proc/cpuinfo')) {
      $cpuinfo = file_get_contents('/proc/cpuinfo');
      preg_match_all('/^processor/m', $cpuinfo, $matches);
      $cpu_count = count($matches[0]);
    }

    set_transient( 'wpopti_cpu_core_count', $cpu_count, DAY_IN_SECONDS );

    return $cpu_count;
  }

  // Returns server load in percent (just number, without percent sign)
  function getServerLoad() {
    $load = null;

    if ('WIN' == strtoupper(substr(PHP_OS, 0, 3))) {
        $output = $this->wp_shell_exec('wmic cpu get loadpercentage /all');
        if (false !== $output) {
          if (preg_match("/^[0-9]+/m", $output, $matches)) {
              $load = $matches[0];
          }
        }
    }
    else if (is_readable("/proc/cpuinfo")) {
      $load = trim( $this->wp_shell_exec("echo $((`ps aux|awk 'NR > 0 { s +=$3 }; END {print s}'| cut -d . -f 1` / `cat /proc/cpuinfo | grep cores | grep -o '[0-9]' | wc -l`))") );
    }

    return $load;
  }

  public function php_max_upload_size() {

  	$php_max_upload_size = get_transient( 'wpopti_php_max_upload_size' );

  	if( $php_max_upload_size === FALSE ) {
    	if( ini_get( 'upload_max_filesize' ) ) {
            $php_max_upload_size = ini_get( 'upload_max_filesize' );
            $php_max_upload_size = $this->format_php_size( $php_max_upload_size );
            set_transient( 'wpopti__php_max_upload_size', $php_max_upload_size, WEEK_IN_SECONDS );
        } else {
            $php_max_upload_size = 'N/A';
      	}
  	}

      return $php_max_upload_size;
  }

  public function php_max_execution_time() {
    if( ini_get( 'max_execution_time' ) ) {
        $max_execute = ini_get('max_execution_time');
    } else {
        $max_execute = 'N/A';
    }
    return $max_execute;
  }

  public function format_filesize_kB( $kiloBytes, $decimals = 0 ) {
      if( ( $kiloBytes / pow( 1024, 4 ) ) > 1) {
          return number_format_i18n( ( $kiloBytes/pow( 1024, 4 ) ), $decimals ).' PB';
      } elseif( ( $kiloBytes / pow( 1024, 3 ) ) > 1 ) {
      	return number_format_i18n( ( $kiloBytes/pow( 1024, 3 ) ), $decimals ).' TB';
      } elseif( ( $kiloBytes / pow( 1024, 2 ) ) > 1) {
        return number_format_i18n( ( $kiloBytes/pow( 1024, 2 ) ), $decimals ).' GB';
      } elseif( ( $kiloBytes / 1024 ) > 1 ) {
        return number_format_i18n( $kiloBytes/1024, $decimals ).' MB';
      } elseif( $kiloBytes >= 0 ) {
          return number_format_i18n( $kiloBytes/1, $decimals).' KB';
      } else {
          return 'Unknown';
      }
  }

  public function format_php_size($size) {
    if (!is_numeric($size)) {
        if (strpos($size, 'M') !== false) {
            $size = intval($size)*1024*1024;
        } elseif (strpos($size, 'K') !== false) {
            $size = intval($size)*1024;
        } elseif (strpos($size, 'G') !== false) {
            $size = intval($size)*1024*1024*1024;
        }
    }
    return $size;
  }

  public function  get_php_memory_limit() {
    $memory_limit = ini_get('memory_limit');

  	$memory_limit = strtolower( trim( $memory_limit ) );
  	$bytes = (int) $memory_limit;

  	if ( false !== strpos( $memory_limit, 'g' ) ) {
  		$bytes *= GB_IN_BYTES;
  	} elseif ( false !== strpos( $memory_limit, 'm' ) ) {
  		$bytes *= MB_IN_BYTES;
  	} elseif ( false !== strpos( $memory_limit, 'k' ) ) {
  		$bytes *= KB_IN_BYTES;
  	}

    return  $bytes;
  }

  public function icmp_checksum($data) {
    if (strlen($data) % 2) {
      $data .= "\x00";
    }
    $bit = unpack('n*', $data);
    $sum = array_sum($bit);
    while  ($sum  >> 16) {
      $sum = ($sum >> 16) + ($sum & 0xffff);
    }
    return pack('n*', ~$sum);
  }

  public function pingSocket($host) {

    if(!function_exists('socket_create')) return false;

    $tmp = "\x08\x00\x00\x00\x00\x00\x00\x00PingTest";
    $checksum = $this->icmp_checksum($tmp);
    $package = "\x08\x00".$checksum."\x00\x00\x00\x00PingTest";
    $socket = @socket_create(AF_INET, SOCK_RAW, 1);
    if($socket === false) return false;
    @socket_connect($socket, $host, null);
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>5, 'usec'=>0));
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec'=>5, 'usec'=>0));
    $timer = microtime(1);
    @socket_send($socket, $package, strlen($package), 0);
    if (@socket_read($socket, 255)) {
      return round((microtime(1) - $timer) * 1000, 2);
    }

    return false;
  }

  private function pingExec($host=NULL) {

    if(!function_exists('exec')) return false;

    $latency = false;
    $ttl = 64;
    $timeout = 10;

    // Exec string for Windows-based systems.
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      // -n = number of pings; -i = ttl; -w = timeout (in milliseconds).
      $exec_string = 'ping -n 1 -i ' . $ttl . ' -w ' . ($timeout * 1000) . ' ' . $host;
    }
    // Exec string for Darwin based systems (OS X).
    else if(strtoupper(PHP_OS) === 'DARWIN') {
      // -n = numeric output; -c = number of pings; -m = ttl; -t = timeout.
      $exec_string = 'ping -n -c 1 -m ' . $ttl . ' -t ' . $timeout . ' ' . $host;
    }
    // Exec string for other UNIX-based systems (Linux).
    else {
      // -n = numeric output; -c = number of pings; -t = ttl; -W = timeout
      $exec_string = 'ping -n -c 1 -t ' . $ttl . ' -W ' . $timeout . ' ' . $host . ' 2>&1';
    }
    exec($exec_string, $output, $return);

    // Strip empty lines and reorder the indexes from 0 (to make results more
    // uniform across OS versions).
    $this->commandOutput = implode($output, '');
    $output = array_values(array_filter($output));
    // If the result line in the output is not empty, parse it.
    if (!empty($output[1])) {
      // Search for a 'time' value in the result line.
      $response = preg_match("/time(?:=|<)(?<time>[\.0-9]+)(?:|\s)ms/", $output[1], $matches);
      // If there's a result and it's greater than 0, return the latency.
      if ($response > 0 && isset($matches['time'])) {
        $latency = round($matches['time']);
      }
    }
    return $latency;
  }

  private function pingFsockopen($host) {

    if(!function_exists('fsockopen')) return false;

    $start = microtime(true);
    // fsockopen prints a bunch of errors if a host is unreachable. Hide those
    // irrelevant errors and deal with the results instead.
    $fp = @fsockopen($host, 80, $errno, $errstr, 10);
    if (!$fp) {
      $latency = false;
    }
    else {
      $latency = microtime(true) - $start;
      $latency = round($latency * 1000);
    }
    return $latency;
  }

  public function ping($host=NULL) {
    if(empty($host)) {$host = $_SERVER['REMOTE_ADDR'];}

    $latency = $this->pingExec($host);
    if($latency !== false) return $latency;

    $latency = $this->pingSocket($host);
    if($latency !== false) return $latency;

    $latency = $this->pingFsockopen($host);
    if($latency !== false) return $latency;

    return false;

  }
}
