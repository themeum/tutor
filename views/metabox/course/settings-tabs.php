<?php
$args = $this->args;

echo '<pre>';
print_r($args);
echo '</pre>';

?>

<div id="tutor-metabox-course-settings-tabs" class="tutor-course-settings-tabs">
	<div class="settings-tabs-heading">
		<h3><?php _e('Course Settings', 'tutor'); ?></h3>
	</div>


	<div class="course-settings-tabs-container">

		<div class="settings-tabs-navs-wrap">

			<ul class="settings-tabs-navs">

				<?php foreach ($args as $arg){
					$label = tutils()->array_get('label', $arg);
					echo "<li><a href=''>{$label}</a> </li>";
				} ?>

			</ul>

		</div>

		<div class="settings-tabs-container">

		</div>

	</div>


</div>
