<?php
$withdrawList = new \TUTOR\Withdraw_Requests_List();
$withdrawList->prepare_items();
?>


<div class="wrap">
	<h2><?php _e('Withdraw Requests', 'tutor'); ?></h2>

	<form id="withdrawals-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php
		$withdrawList->search_box(__('Search', 'tutor'), 'withdrawals');
		$withdrawList->display(); ?>
	</form>
</div>