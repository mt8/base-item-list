<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
	global $base_items;
	/*
	Array
	(	
		[n] => stdClass Object
		(
			[item_id] => int
			[title] => string
			[detail] => string

			[price] => int
			[proper_price] => int
			[stock] => int
			[visible] => int
			[list_order] => int
			[identifier] => 
			[img(1-5)_origin] => string
			[img1_76] => string
			[img1_146] => string
			[img1_300] => string
			[img1_500] => string
			[img1_640] => string
			[img1_sp_480] => string
			[img1_sp_640] => string
			[modified] => 1601540864
			[variations] => Array
				(
					[n] => stdClass Object
						(
							[variation_id] => int
							[variation] => string
							[variation_stock] => int
							[variation_identifier] => 
						)
				)

			[shop_id] => string
			[shop_name] => string
			[shop_url] => string
			[categories] => Array
				(
					[n] => string
				)
		)
	)
	 */
?>

<?php if ( isset( $base_items ) ) : ?>

	<div class="base_items">
		<ul class="base_items_list">
		<?php foreach ( $base_items as $item ) : ?>
			<li class="base_item">
				<dt><span class="base_item_title"><?php echo esc_html( $item->title ); ?></span></dt>
				<dd>
					<a href="<?php echo esc_url( $item->shop_url) ?>/items/<?php echo $item->item_id; ?>" target="_blank">
						<img src="<?php echo esc_url( $item->img1_300 ); ?>" alt="<?php echo esc_attr( $item->title ); ?>">
					</a>
				</dd>
			</li>
		<?php endforeach; ?>
		</ul>
	</div><!--/.base_items-->

<?php endif;