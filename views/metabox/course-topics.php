
<?php
$topics = range(1,10);

foreach ($topics as $topic){
	?>

	<div id="lms-topics-<?php echo $topic; ?>" class="lms-topics">


		<h3>
			<i class="dashicons dashicons-move"></i> <a href="">Topics <?php echo $topic; ?></a>
		</h3>


	</div>


	<?php
}
?>