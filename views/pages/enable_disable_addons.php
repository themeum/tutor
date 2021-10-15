<?php
	$addons_data = tutor_utils()->prepare_addons_data();
	$all_addons = count( $addons_data );
	$active_addons = 0;
	$inactive_addons = 0;

	foreach ( $addons_data as $addon ) {
		if ( $addon['is_enabled'] ) {
			$active_addons++;
		} else {
			$inactive_addons++;
		}
	}
?>
<main class="tutor-backend-settings-addons-list tutor-dashboard-page">
	<header
		class="
			tutor-addons-list-header
			d-flex
			justify-content-between
			align-items-center
			tutor-px-30 tutor-py-20
		"
	>
		<div class="title text-medium-h5 color-text-primary mb-md-0 mb-3">Addons List</div>
		<div class="filter-btns text-regular-body color-text-subsued">
			<!-- <button class="tutor-btn tutor-is-sm">Save Changes</button> -->
			<button type="button" class="filter-btn is-active" data-tab-filter-target="all">
				All <span class="item-count">(<?php echo $all_addons; ?>)</span>
			</button>
			<button type="button" class="filter-btn" data-tab-filter-target="active">
				Active<span class="item-count">(<?php echo $active_addons; ?>)</span>
			</button>
			<button type="button" class="filter-btn" data-tab-filter-target="deactive">
				Deactive <span class="item-count">(<?php echo $inactive_addons; ?>)</span>
			</button>
		</div>
	</header>
	<div class="tutor-addons-list-body tutor-p-30">
		<div class="tutor-addons-list-select-filter d-flex justify-content-end align-items-center tutor-mt-5">
			<div class="filter-custom-field d-flex">
				<select name="filter-select" class="tutor-form-select">
					<option value="all" selected>All</option>
					<option value="active">Active</option>
					<option value="deactive">Deactive</option>
				</select>
				<input
					type="search"
					class="filter-search tutor-form-control"
					placeholder="Search by name"
				/>
			</div>
			<button type="button" class="search-btn tutor-btn tutor-is-sm tutor-is-outline">Filter</button>
		</div>
		<div class="tutor-addons-list-items tutor-mt-40"/>
	</div>
</main>
