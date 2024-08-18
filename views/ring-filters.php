<?php
$gcpb_filters = get_option( 'gcpb_filter_attributes' );

$required_attributes = array(
	'eo_metal_attr',
	'ring-style',
	'shape',
);
?>

<div class="gcpb-filters-container">
	<div class="gcpb-custom-filters-wrapper gcpb-setting-filters">
		<button class="gcpb-filter-reset-btn gcpb-filter-reset-btn-setting gcpb-mobile-hidden">Clear Filters</button>

		<div class="gcpb-custom-filters">
			<div class="gcpb-mobile-active-filters__wrapper">
				<div class="gcpb-mobile-active-filters gcpb-mobile-only">
					Shown with: <span class="gcpb-active-shape"></span><span class="gcpb-active-metal">| </span><span class="gcpb-active-style">| </span>
				</div>
				<button class="gcpb-filter-reset-btn gcpb-filter-reset-btn-setting gcpb-mobile-only">Clear Filters</button>
			</div>
			<div class="gcpb-filters-wrapper">
				<?php if ( ! empty( $gcpb_filters ) ) : ?>
					<?php foreach ( $gcpb_filters as $gcpb_filter ) : ?>
						<?php if ( isset( $gcpb_filter['main-attribute'] ) && ! empty( $gcpb_filter['main-attribute'] ) ) : ?>
							<?php
							$attribute_slug = $gcpb_filter['main-attribute'];
							$attribute_taxonomy = 'pa_' . $attribute_slug;
							$is_filter = ( isset( $gcpb_filter['is_filter'] ) && $gcpb_filter['is_filter'] == 'yes' ) ? 'yes' : 'no';
							?>

							<div class="gcpb-filter-container gcpb-<?php echo $attribute_slug; ?>-filter-container" tabindex="0">
								<div class="gcpb-filter-wrapper">
									<div class="gcpb-filter-title">
										<?php echo wc_attribute_label( $attribute_taxonomy ); ?>:
										<span class="gcpb-selected-item"></span>
									</div>

									<?php if ( isset( $gcpb_filter['gcpb-sub-attributes'] ) && ! empty( $gcpb_filter['gcpb-sub-attributes'] ) ) : ?>
										<div class="prb-filter gcpb-filter gcpb-scrollbar gcpb-horizontal">

											<?php foreach ( $gcpb_filter['gcpb-sub-attributes'] as $term ) : ?>
												<?php $metal_filter = get_term_by( 'id', $term['sub-attribute'], $attribute_taxonomy ); ?>

												<button type="button" class="gcpb-custom-filter-button gcpb-active" data-filter-items="<?php echo $is_filter; ?>" data-filter-name="<?php echo $attribute_slug; ?>" data-filter-value="<?php echo $metal_filter->slug; ?>" data-filter-display-value="<?php echo $metal_filter->name; ?>">
													<?php $textureImg = wp_get_attachment_image_src( $term['gcpb_attribute_icon'] ); ?>

													<div class="gcpb-custom-filter-button-popup-text" ><?php echo $metal_filter->name; ?></div>

													<img class="gcpb-custom-filters-button-icon" src="<?php echo $textureImg[0]; ?>" alt="<?php echo $metal_filter->name; ?>">
												</button>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
