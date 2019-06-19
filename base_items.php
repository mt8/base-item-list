<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
	global $base_items;
	/*
	 * item
	 *	item_id int
	 *	title string
	 *	detail string
	 *	price int
	 *	stock int
	 *	**img(n) is 1 to 5**
	 *	img(n)_origin string
	 *	img(n)_76 string
	 *	img(n)_146 string
	 *	img(n)_300 string
	 *	img(n)_500 string
	 *	img(n)_640 string
	 *	img(n)_sp_480 string
	 *	img(n)_sp_640 string
	 *	modified int
	 *	shop_id string
	 *  shop_name string
	 *	shop_url string
	 *	categories array (string,string...)
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