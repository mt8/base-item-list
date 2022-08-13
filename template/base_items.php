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
			[item_tax_type] => int
			[stock] => int
			[visible] => int
			[list_order] => int
			[identifier] => 
			[modified] => 1601540864

			[img(1-5)_origin] => string
			[img(1-5)_76] => string
			[img(1-5)_146] => string
			[img(1-5)_300] => string
			[img(1-5)_500] => string
			[img(1-5)_640] => string
			[img(1-5)_sp_480] => string
			[img(1-5)_sp_640] => string
			[variations] => Array
				(
					[n] => stdClass Object
						(
							[variation_id] => int
							[variation] => string
							[variation_stock] => int
							[variation_identifier] =>
							[barcode] =>
						)
				)
			[options] => Array
				(
					[n] => stdClass Object
						(
							[option_id] => int
							[option_name] => string
							[option_type] => string
							[required] => bool
							[list_order] => int
						)
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