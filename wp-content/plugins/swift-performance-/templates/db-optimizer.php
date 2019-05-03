<?php defined('ABSPATH') or die("KEEP CALM AND CARRY ON");?>
<?php
	// Extend timeout
	Swift_Performance::set_time_limit(120, 'db_optimizer_dashboard');
?>
<div class="swift-message swift-backup-warning">
	<p>
		<span class="dashicons dashicons-warning"></span>
		<span class="swift-message-text"><?php esc_html_e('Please first backup your database, because any optimization is irreversible.', 'swift-performance')?></span>
	</p>
	<a href="#" class="swift-btn swift-btn-green swift-confirm-backup"><?php esc_html_e('I have backup!', 'swift-performance')?></a>
</div>
<div class="swift-dashboard content-blurred">
	<div class="swift-dashboard-item">
            <h3><?php esc_html_e('General', 'swift-performance');?></h3>
            <ul>
                  <li>
				<ul>
                              <li>
                                    <strong><?php esc_html_e('Database:', 'swift-performance');?></strong>
                                    <span class="count"><?php echo Swift_Performance_DB_Optimizer::count_tables();?> <?php esc_html_e('tables', 'swift-performance');?></span>
                              </li>
                              <li class="text-right">
                                    <a href="#" class="swift-db-optimizer-action" id="reindex-tables"><?php esc_html_e('Reindex tables', 'swift-performance')?></a> | <a href="#" class="swift-db-optimizer-action" id="optimize-tables"><?php esc_html_e('Optimize tables', 'swift-performance')?></a>
                              </li>
                        </ul>
                        <ul>
                              <li>
                                    <strong><?php esc_html_e('Expired transients:', 'swift-performance');?></strong>
                                    <span class="count"><?php echo Swift_Performance_DB_Optimizer::count_expired_transients();?></span>
                              </li>
                              <li class="text-right">
                                    <a href="#" class="swift-db-optimizer-action" id="clear-expired-transients"><?php esc_html_e('Clear', 'swift-performance')?></a> |
						<?php echo Swift_Performance_DB_Optimizer::schedule('clear_expired_transients');?>
                              </li>
                        </ul>
				<?php echo Swift_Performance_DB_Optimizer::schedule_form('clear_expired_transients');?>
                        <ul>
                              <li>
                                    <strong><?php esc_html_e('Autoload size:', 'swift-performance');?></strong>
                                    <?php echo number_format(Swift_Performance_DB_Optimizer::get_autoload_size()/1024/1024, 2);?>Mb
                              </li>
                              <?php if (Swift_Performance_DB_Optimizer::get_autoload_size() > 5242880):?>
                              <li class="text-right text-red">
                                    <?php esc_html_e('SHOULD FIX', 'swift-performance');?>
			            </li>
				<?php elseif (Swift_Performance_DB_Optimizer::get_autoload_size() > 1048576):?>
                              <li class="text-right text-yellow">
                                    <?php esc_html_e('SHOULD FIX', 'swift-performance');?>
			            </li>
                              <?php else:?>
                              <li class="text-right text-green">
                                    <?php esc_html_e('OK', 'swift-performance');?>
                              </li>
                              <?php endif;?>
				</ul>
                  </li>
            </ul>
      </div>
      <div class="swift-dashboard-item">
            <h3><?php esc_html_e('Posts', 'swift-performance');?></h3>
            <ul>
                  <li>
                        <ul>
                              <li>
                              	<strong><?php esc_html_e('Revisions:', 'swift-performance');?></strong>
                                    <span class="count"><?php echo Swift_Performance_DB_Optimizer::count_revisions();?></span>
                              </li>
                              <li class="text-right">
                                    <a href="#" class="swift-db-optimizer-action" id="clear-revisions"><?php esc_html_e('Clear all', 'swift-performance')?></a> |
						<?php echo Swift_Performance_DB_Optimizer::schedule('clear_revisions');?>
                              </li>
                        </ul>
				<?php echo Swift_Performance_DB_Optimizer::schedule_form('clear_revisions');?>
				<ul>
					<li>
						<strong><?php esc_html_e('Trashed posts:', 'swift-performance');?></strong>
						<span class="count"><?php echo Swift_Performance_DB_Optimizer::count_trashed_posts();?></span>
					</li>
					<li class="text-right">
						<a href="#" class="swift-db-optimizer-action" id="clear-trashed-posts"><?php esc_html_e('Clear all', 'swift-performance')?></a> |
						<?php echo Swift_Performance_DB_Optimizer::schedule('clear_trashed_posts');?>
					</li>
				</ul>
				<?php echo Swift_Performance_DB_Optimizer::schedule_form('clear_trashed_posts');?>
				<ul>
                              <li>
                              	<strong><?php esc_html_e('Orphan postmeta:', 'swift-performance');?></strong>
                                    <span class="count"><?php echo Swift_Performance_DB_Optimizer::count_orphan_postmeta();?></span>
                              </li>
                              <li class="text-right">
                                    <a href="#" class="swift-db-optimizer-action" id="clear-orphan-postmeta"><?php esc_html_e('Clear all', 'swift-performance')?></a> |
						<?php echo Swift_Performance_DB_Optimizer::schedule('clear_orphan_postmeta');?>
                              </li>
                        </ul>
				<?php echo Swift_Performance_DB_Optimizer::schedule_form('clear_orphan_postmeta');?>
				<ul>
					<li>
						<strong><?php esc_html_e('Orphan attachments:', 'swift-performance');?></strong>
						<span class="count"><?php echo Swift_Performance_DB_Optimizer::count_orphan_attachments();?></span>
					</li>
					<li class="text-right">
						<a href="#" class="swift-db-optimizer-action" id="clear-orphan-attachments"><?php esc_html_e('Clear all', 'swift-performance')?></a> |
						<?php echo Swift_Performance_DB_Optimizer::schedule('clear_orphan_attachments');?>
					</li>
				</ul>
				<?php echo Swift_Performance_DB_Optimizer::schedule_form('clear_orphan_attachments');?>
				<ul>
					<li>
						<strong><?php esc_html_e('Duplicated postmeta:', 'swift-performance');?></strong>
						<span class="count"><?php echo Swift_Performance_DB_Optimizer::count_duplicated_postmeta();?></span>
					</li>
					<li class="text-right">
						<a href="#" class="swift-db-optimizer-action" id="clear-duplicated-postmeta"><?php esc_html_e('Clear all', 'swift-performance')?></a> |
						<?php echo Swift_Performance_DB_Optimizer::schedule('clear_duplicated_postmeta');?>
					</li>
				</ul>
				<?php echo Swift_Performance_DB_Optimizer::schedule_form('clear_duplicated_postmeta');?>
                  </li>
            </ul>
      </div>
	<div class="swift-dashboard-item">
		<h3><?php esc_html_e('Comments', 'swift-performance');?></h3>
		<ul>
			<li>
				<ul>
					<li>
						<strong><?php esc_html_e('Spam comments:', 'swift-performance');?></strong>
						<span class="count"><?php echo Swift_Performance_DB_Optimizer::count_spam_comments();?></span>
					</li>
					<li class="text-right">
						<a href="#" class="swift-db-optimizer-action" id="clear-spam-comments"><?php esc_html_e('Clear all', 'swift-performance')?></a> |
						<?php echo Swift_Performance_DB_Optimizer::schedule('clear_spam_comments');?>
					</li>
				</ul>
				<?php echo Swift_Performance_DB_Optimizer::schedule_form('clear_spam_comments');?>
				<ul>
					<li>
						<strong><?php esc_html_e('Trashed comments:', 'swift-performance');?></strong>
						<span class="count"><?php echo Swift_Performance_DB_Optimizer::count_trashed_comments();?></span>
					</li>
					<li class="text-right">
						<a href="#" class="swift-db-optimizer-action" id="clear-trashed-comments"><?php esc_html_e('Clear all', 'swift-performance')?></a> |
						<?php echo Swift_Performance_DB_Optimizer::schedule('clear_trashed_comments');?>
					</li>
				</ul>
				<?php echo Swift_Performance_DB_Optimizer::schedule_form('clear_trashed_comments');?>
				<ul>
					<li>
						<strong><?php esc_html_e('Orphan commentmeta:', 'swift-performance');?></strong>
						<span class="count"><?php echo Swift_Performance_DB_Optimizer::count_orphan_commentmeta();?></span>
					</li>
					<li class="text-right">
						<a href="#" class="swift-db-optimizer-action" id="clear-orphan-commentmeta"><?php esc_html_e('Clear all', 'swift-performance')?></a> |
						<?php echo Swift_Performance_DB_Optimizer::schedule('clear_orphan_commentmeta');?>
					</li>
				</ul>
				<?php echo Swift_Performance_DB_Optimizer::schedule_form('clear_orphan_commentmeta');?>
				<ul>
					<li>
						<strong><?php esc_html_e('Duplicated commentmeta:', 'swift-performance');?></strong>
						<span class="count"><?php echo Swift_Performance_DB_Optimizer::count_duplicated_commentmeta();?></span>
					</li>
					<li class="text-right">
						<a href="#" class="swift-db-optimizer-action" id="clear-duplicated-commentmeta"><?php esc_html_e('Clear all', 'swift-performance')?></a> |
						<?php echo Swift_Performance_DB_Optimizer::schedule('clear_duplicated_commentmeta');?>
					</li>
				</ul>
				<?php echo Swift_Performance_DB_Optimizer::schedule_form('clear_duplicated_commentmeta');?>
			</li>
		</ul>
	</div>
</div>
<br>
<div class="swift-dashboard content-blurred">
	<div class="swift-dashboard-item">
		<h3><?php esc_html_e('Terms & Users', 'swift-performance');?></h3>
		<ul>
			<li>
				<ul>
					<li>
						<strong><?php esc_html_e('Orphan termmeta:', 'swift-performance');?></strong>
						<span class="count"><?php echo Swift_Performance_DB_Optimizer::count_orphan_termmeta();?></span>
					</li>
					<li class="text-right">
						<a href="#" class="swift-db-optimizer-action" id="clear-orphan-termmeta"><?php esc_html_e('Clear all', 'swift-performance')?></a> |
						<?php echo Swift_Performance_DB_Optimizer::schedule('clear_orphan_termmeta');?>
					</li>
				</ul>
				<?php echo Swift_Performance_DB_Optimizer::schedule_form('clear_orphan_termmeta');?>
				<ul>
					<li>
						<strong><?php esc_html_e('Orphan usermeta:', 'swift-performance');?></strong>
						<span class="count"><?php echo Swift_Performance_DB_Optimizer::count_orphan_usermeta();?></span>
					</li>
					<li class="text-right">
						<a href="#" class="swift-db-optimizer-action" id="clear-orphan-usermeta"><?php esc_html_e('Clear all', 'swift-performance')?></a> |
						<?php echo Swift_Performance_DB_Optimizer::schedule('clear_orphan_usermeta');?>
					</li>
				</ul>
				<?php echo Swift_Performance_DB_Optimizer::schedule_form('clear_orphan_usermeta');?>
				<ul>
					<li>
						<strong><?php esc_html_e('Duplicated usermeta:', 'swift-performance');?></strong>
						<span class="count"><?php echo Swift_Performance_DB_Optimizer::count_duplicated_usermeta();?></span>
					</li>
					<li class="text-right">
						<a href="#" class="swift-db-optimizer-action" id="clear-duplicated-usermeta"><?php esc_html_e('Clear all', 'swift-performance')?></a> |
						<?php echo Swift_Performance_DB_Optimizer::schedule('clear_duplicated_usermeta');?>
					</li>
				</ul>
				<?php echo Swift_Performance_DB_Optimizer::schedule_form('clear_duplicated_usermeta');?>
			</li>
		</ul>
	</div>
</div>
