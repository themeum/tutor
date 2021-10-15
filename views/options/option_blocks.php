<?php if ( $blocks['block_type'] == 'uniform' ) : ?>

	<div class="tutor-option-single-item <?php echo ! isset( $blocks['class'] ) ? ( $blocks['slug'] ?? null ) : $blocks['class']; ?>">
		<?php echo $blocks['label'] ? '<h4>' . $blocks['label'] . '</h4>' : ''; ?>
		<div class="item-wrapper">
			<?php
			foreach ( $blocks['fields'] as $field ) :
				echo $this->generate_field( $field );
			endforeach;
			?>
		</div>
	</div>

<?php elseif ( $blocks['block_type'] == 'isolate' ) : ?>

	<div class="tutor-option-single-item <?php echo $blocks['slug']; ?>">
		<?php echo $blocks['label'] ? '<h4>' . $blocks['label'] . '</h4>' : ''; ?>
		<?php foreach ( $blocks['fields'] as $field ) : ?>
			<div class="item-wrapper">
				<?php echo $this->generate_field( $field ); ?>
			</div>
		<?php endforeach; ?>
	</div>

<?php elseif ( $blocks['block_type'] == 'notification' ) : ?>

	<div class="tutor-option-single-item">
		<div class="item-title">
			<div class="d-flex">
				<h4><?php echo $blocks['label']; ?></h4>
				<div class="tooltip-wrap tooltip-icon">
					<span class="tooltip-txt tooltip-right"><?php echo $blocks['tooltip']; ?></span>
				</div>
			</div>
			<h4><?php echo $blocks['status_label']; ?></h4>
		</div>
		<div class="item-wrapper">
			<?php foreach ( $blocks['fields'] as $field ) :
				echo $this->generate_field( $field );
				endforeach;
			?>
		</div>
	</div>

<?php elseif ( $blocks['block_type'] == 'column' ) : ?>

	<div class="tutor-option-single-item item-variation-grid <?php echo $blocks['slug']; ?>">
		<?php echo $blocks['label'] ? '<h4>' . $blocks['label'] . '</h4>' : ''; ?>
		<div class="item-grid">
			<?php foreach ( $blocks['fieldset'] as $fieldset ) : ?>
				<div class="item-wrapper">
					<?php foreach ( $fieldset as $field ) : ?>
						<?php echo $this->generate_field( $field ); ?>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

<?php endif; ?>
