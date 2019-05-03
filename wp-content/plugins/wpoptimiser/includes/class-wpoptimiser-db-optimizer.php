<?php
/**
 * The database optimizer class to hanlde all database optmization duties
 *
 * @link       http://wpoptimiser.com
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/includes
 */

if (!class_exists('WPOptimiser_DB_Optimizer')) {

class WPOptimiser_DB_Optimizer {
  /**
	 * The Wordpress Table Prefix.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $table_prefix    The string used to hold the Wordpress table prefix.
	 */
  private $table_prefix;

  /**
	 * A list of the Wordpress tables and their status.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $tables    Array of each table and its status.
	 */
  public $tables;

  /**
	 * The current version of SQL.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $sqlversion    The current version of SQL.
	 */
  public $sqlversion;

  /**
	 * The amount of space that can be regained by optimizationL.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $total_gain    The total gain.
	 */
   public $total_gain;

   /**
 	 * The efficiency of the database taking into account the amount of overhead it has
 	 *
 	 * @since    1.0.0
 	 * @access   public
 	 * @var      string    $efficiency_perc    The efficiency percentage of the database.
 	 */
    public $efficiency_perc;


   /**
   * The current size of the database.
   *
   * @since    1.0.0
   * @access   public
   * @var      string    $sqlversion    The current total size of the database.
   */
   public $total_size;

   /**
   * Holds the number of innoDB tables and number of non innoDB tables
   *
   * @since    1.0.0
   * @access   public
   * @var      string    $non_inno_db_tables  The number of non innoDB tables in the database.
   * @var      string    $inno_db_tables      The number of innoDB tables in the database.
   */
   public $non_inno_db_tables;
   public $inno_db_tables;

   /**
   * Store the number of weeks to retain data, for post revisions, auto drafts etc
   *
   * @since    1.0.0
   * @access   public
   * @var      string    $retain_interval  The number of weeks to retain data.
   */
   public $retain_interval = 2;

	public function __construct( ) {
    global $wpdb;

    // Discover the wordpress table prefix
    if (!is_multisite()) {
      $this->table_prefix = $wpdb->base_prefix;
    } else {
      $this->table_prefix = $wpdb->get_blog_prefix();
    }

    $this->update();

    $this->sqlversion = $wpdb->get_var("SELECT VERSION() AS version");
	}

  /**
	 * Updates the list of tables and database statistics
	 *
	 * @since    1.0.0
	 */
  public function update() {
    $this->non_inno_db_tables = 0;
    $this->inno_db_tables = 0;

    $this->get_tables();
    $this->calculate_efficiency();
  }

  /**
	 * Gets a list of the tables in the datbase along with their status
	 *
	 * @since    1.0.0
	 */
  public function get_tables() {
    global $wpdb;

    $data_usage = 0;
    $index_usage = 0;
    $this->total_gain = 0;

    $table_status = $wpdb->get_results("SHOW TABLE STATUS");

    if (is_array($table_status)) {
      foreach ($table_status as $index => $table) {
        $table_name = $table->Name;

        $include_table = (0 === stripos($table_name, $this->table_prefix));

        if (!$include_table) {
          unset($table_status[$index]);
          continue;
        }

        $data_usage += $table->Data_length;
        $index_usage +=  $table->Index_length;

        if ($table->Engine != 'InnoDB'){
          $this->total_gain += $table->Data_free;
          $this->non_inno_db_tables++;
        }else {
          $this->inno_db_tables++;
        }
      }
    }

    $this->total_size = $data_usage + $index_usage;

    $this->tables = $table_status;
  }

  /**
   * Format the passed Bytes Into KB/MB string
   *
   * @since    1.0.0
   */
  public function format_size($bytes) {
    if ($bytes > 1073741824) {
      return number_format_i18n($bytes/1073741824, 2) . ' GB';
    } elseif ($bytes > 1048576) {
      return number_format_i18n($bytes/1048576, 1) . ' MB';
    } elseif ($bytes > 1024) {
      return number_format_i18n($bytes/1024, 1) . ' KB';
    } else {
      return number_format_i18n($bytes, 0) . ' bytes';
    }
  }

  /**
   * Calculates the efficiency of the database taking into account the amount
   * of overhead it has
   *
   * @since    1.0.0
   */
  public function calculate_efficiency() {

    if($this->total_gain <= 0)
      $this->efficiency_perc = 100;
    else {
      $perc = ($this->total_gain / $this->total_size) * 100.00;
      $this->efficiency_perc = 100.00 - $perc;
    }

  }

  /**
   * Optimizes the tables
   *
   * @since    1.0.0
   */
  public function optimize_tables() {

    global $wpdb;

    $tables = $this->tables;

    foreach ($tables as $table) {
      if ($table->Data_free <= 0)
        continue;

      $result_query = true;
      $result_query  = $wpdb->query('OPTIMIZE TABLE '.$table->Name);
      if($result_query === false)
        return false;
    }

    return true;
  }

  /**
   * Gets the post revision stats
   *
   * @since    1.0.0
   */
  public function get_post_revision_stats() {
    global $wpdb;

		$sql = "SELECT COUNT(*) FROM `".$wpdb->posts."` WHERE post_type = 'revision'";
    if ($this->retain_interval > 0) {
      $sql .= ' and post_modified < NOW() - INTERVAL ' .  $this->retain_interval . ' WEEK';
    }
    $sql .= ';';

		$rev_count = $wpdb->get_var($sql);

		if($rev_count == 0 || $rev_count == NULL) {
			$rev_count = 0;
		}

    return $rev_count;

	}

  /**
   * Optimizes the post revisions
   *
   * @since    1.0.0
   */
  public function optimize_post_revisions() {
    global $wpdb;

		$sql = "DELETE FROM `".$wpdb->posts."` WHERE post_type = 'revision'";
    if ($this->retain_interval > 0) {
      $sql.= ' AND post_modified < NOW() - INTERVAL ' . $this->retain_interval . ' WEEK';
    }
		$sql .= ';';

		$result_query = $wpdb->query($sql);

    if($result_query === false)
      return false;

    return true;
	}

  /**
   * Gets the posts auto-saved and trash stats
   *
   * @since    1.0.0
   */
  public function get_auto_save_trash_stats() {
    global $wpdb;

		$sql = "SELECT COUNT(*) FROM `".$wpdb->posts."` WHERE post_status = 'auto-draft'";
    if ($this->retain_interval > 0) {
      $sql .= ' AND post_modified < NOW() - INTERVAL ' .  $this->retain_interval  . ' WEEK';
    }
    $sql .= ';';

		$autosave = $wpdb->get_var($sql);

    if($autosave == 0 || $autosave == NULL) {
			$autosave = 0;
		}

		$sql = "SELECT COUNT(*) FROM `".$wpdb->posts."` WHERE post_status = 'trash'";
    if ($this->retain_interval > 0) {
      $sql.= ' and post_modified < NOW() - INTERVAL ' .  $this->retain_interval . ' WEEK';
    }
    $sql .= ';';

		$trash = $wpdb->get_var($sql);

    if($trash == 0 || $trash == NULL) {
			$trash = 0;
		}
    return array('auto-save' => $autosave, 'trash' => $trash);
	}

  /**
   * Optimizes the auto-saved and trash posts
   *
   * @since    1.0.0
   */
  public function optimize_auto_save_trash_stats() {
    global $wpdb;

		$sql = "DELETE FROM `".$wpdb->posts."` WHERE post_status = 'auto-draft'";
		if ($this->retain_interval > 0) {
			$sql .= ' AND post_modified < NOW() - INTERVAL ' .  $this->retain_interval  . ' WEEK';
		}
    $sql .= ';';

		$result_query = $wpdb->query($sql);

    if($result_query === false)
      return false;

		$sql = "DELETE FROM `".$wpdb->posts."` WHERE post_status = 'trash'";
	  if ($this->retain_interval > 0) {
			$sql .= ' AND post_modified < NOW() - INTERVAL ' .  $this->retain_interval  . ' WEEK';
		}
    $sql .= ';';

		$result_query = $wpdb->query($sql);

    if($result_query === false)
      return false;

    return true;
	}

  /**
   * Gets the unapproved, spam & Trash comments
   *
   * @since    1.0.0
   */
  public function get_comments_stats() {
    global $wpdb;

    $sql = "SELECT COUNT(*) FROM `".$wpdb->comments."` WHERE comment_approved = '0'";
    if ($this->retain_interval > 0) {
			$sql .= ' and comment_date < NOW()';
		}
    $sql .= ';';

    $unapproved = $wpdb->get_var($sql);

    if($unapproved == 0 || $unapproved == NULL) {
      $unapproved = 0;
    }

    $sql = "SELECT COUNT(*) FROM `".$wpdb->comments."` WHERE comment_approved = 'spam'";
    if ($this->retain_interval > 0) {
      $sql .= ' and comment_date < NOW() - INTERVAL ' . $this->retain_interval  . ' WEEK';
    }
    $sql .= ';';

    $spam = $wpdb->get_var($sql);

    if($spam == 0 || $spam == NULL) {
      $spam = 0;
    }

    $sql = "SELECT COUNT(*) FROM `".$wpdb->comments."` WHERE comment_approved = 'trash'";
		if ($this->retain_interval > 0) {
			$sql .= ' and comment_date < NOW() - INTERVAL ' . $this->retain_interval . ' WEEK';
		}
		$sql .= ';';

		$trash = $wpdb->get_var($sql);

    if($trash == 0 || $trash == NULL) {
      $trash = 0;
    }

    return array('unapproved' => $unapproved, 'spam' => $spam, 'trash' => $trash);

  }

  /**
   * Optimizes the unapproved, spam & Trash comments
   *
   * @since    1.0.0
   */
  public function optimize_comments_stats() {
    global $wpdb;

    $sql = "DELETE FROM `".$wpdb->comments."` WHERE comment_approved = '0'";
    if ($this->retain_interval > 0) {
      $sql .= ' and comment_date < NOW() - INTERVAL ' . $this->retain_interval . ' WEEK';
    }
    $sql .= ';';

    $result_query = $wpdb->query($sql);

    if($result_query === false)
      return false;

    $sql = "DELETE FROM `".$wpdb->comments."` WHERE comment_approved = 'spam'";
    if ($this->retain_interval > 0) {
      $sql .= ' and comment_date < NOW() - INTERVAL ' . $this->retain_interval  . ' WEEK';
    }
    $sql .= ';';

    $result_query = $wpdb->query($sql);

    if($result_query === false)
      return false;

    $sql = "DELETE FROM `".$wpdb->comments."` WHERE comment_approved = 'trash'";
    if ($this->retain_interval > 0) {
      $sql .= ' and comment_date < NOW() - INTERVAL ' . $this->retain_interval . ' WEEK';
    }
    $sql .= ';';

    $result_query = $wpdb->query($sql);

    if($result_query === false)
      return false;

    return true;

  }

  /**
   * Gets the post and comments orphaned meta data stats
   *
   * @since    1.0.0
   */
  public function get_orphaned_meta_data_stats() {
    global $wpdb;

    $sql = "SELECT COUNT(*) FROM `".$wpdb->postmeta."` pm LEFT JOIN `".$wpdb->posts."` wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL;";

    $post_meta = $wpdb->get_var($sql);

    if($post_meta == 0 || $post_meta == NULL) {
      $post_meta = 0;
    }

    $sql = "SELECT COUNT(*) FROM `".$wpdb->commentmeta."` WHERE comment_id NOT IN (SELECT comment_id FROM `".$wpdb->comments."`);";

    $comments_meta = $wpdb->get_var($sql);

    if($comments_meta == 0 || $comments_meta == NULL) {
      $comments_meta = 0;
    }

    return array('post_meta' => $post_meta, 'comments_meta' => $comments_meta);

  }

  /**
   * Optimizes the post and comments orphaned meta data
   *
   * @since    1.0.0
   */
  public function optimize_orphaned_meta_data() {
    global $wpdb;

    $sql = "DELETE pm FROM `".$wpdb->postmeta."` pm LEFT JOIN `".$wpdb->posts."` wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL;";
    $result_query = $wpdb->query($sql);

    if($result_query === false)
      return false;

    $sql = "DELETE FROM `".$wpdb->commentmeta."` WHERE comment_id NOT IN (SELECT comment_id FROM `".$wpdb->comments."`);";

    $result_query = $wpdb->query($sql);

    if($result_query === false)
      return false;

    return true;

  }
}

}
