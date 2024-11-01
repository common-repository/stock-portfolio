<?php
/*
Plugin Name: Stock Portfolio
Plugin URI: http://diyinvestor.me/wordpress-stock-portfolio-plugin/
Description: Track the performance of up to 12 stocks in your portfolio. 
Version: 1.2.3
Author: David White
Author URI: https://www.upwork.com/freelancers/~01f6e487fbcf140489
License: GPL2
*/

function diy_investor_register_stock_portfolio(){
     register_widget( 'diy_Stock_Portfolio' );
}
add_action( 'widgets_init', 'diy_investor_register_stock_portfolio');

$plugin = plugin_basename(__FILE__);
add_filter( "plugin_action_links_$plugin", array( 'diy_Stock_Portfolio', 'plugin_links' ) );
add_action( 'wp_enqueue_scripts', array( 'diy_Stock_Portfolio', 'frontend_scripts' ) );

class diy_Stock_Portfolio extends WP_Widget {

	/**
	 * Hooks to 'plugin_action_links_' filter
	 *
	 * @since 1.0.0
	 */
	static function plugin_links($links) {
		$widget_link = '<a href="widgets.php">Widget</a>';
		array_unshift($links, $widget_link);
		return $links;
	}
	
	static function frontend_scripts() {

		wp_enqueue_style( 'stock_portfolio_css_src', plugins_url( 'include/css/stock_portfolio.css', __FILE__ ) );
		wp_enqueue_script( 'portfolio_quotes_js_src', plugins_url( 'include/js/portfolio_quotes.js', __FILE__ ), array( 'jquery', 'jquery-ui-core' ) );
	}
		
	public function __construct() {
		$widget_ops = array( 'classname' => 'diy_Stock_Portfolio', 'description' => 'Track your stock portfolio performance in percentages.' );

		$this->options[] = array(
			'name'  => 'title', 
			'label' => 'Title',
			'type'	=> 'text', 	
			'default' => 'Stocks',
			'cost'  => 'Costs', 
			'month' => 'Months',
			'url' => 'URLs'
		);

		for ($i = 1; $i < 13; $i++) {
			$this->options[] = array(
				'name'	=> 'stock_' . $i, 
				'label'	=> 'Stock Tickers',
				'type'	=> 'text',	
				'default' => '',
				'cost'  => 'cost_' . $i,
				'month' => 'month_' . $i,
				'url' => 'url_' . $i,
			);
		}

		parent::__construct( false, 'Stock Portfolio', $widget_ops );
		// parent::WP_Widget(false, 'Show Stock Data', $widget_ops);
	}

	/** @see WP_Widget::widget */
    function widget($args, $instance) {

		extract( $args );

		$title = $instance['title'];

		echo $before_widget;

		if ( $title != '') {
			echo $before_title . $title . $after_title;
		}else {
			echo 'Make sure settings are saved.';
		}
		
		$months = array();
		for ($i = 1; $i < 13; $i++) {
			$month = $instance['month_' . $i];
			if ($month != '') {
				$months[] = $month;
			}
		}		

		$tickers = array();
		for ($i = 1; $i < 13; $i++) {
			$ticker = strtoupper($instance['stock_' . $i]);
			if ($ticker != '') {
				$tickers[] = $ticker;
			}
		}
		
		$costs = array();
		for ($i = 1; $i < 13; $i++) {
			$cost = $instance['cost_' . $i];
			if ($cost != '') {
				$costs[] = $cost;
			}
		}

		$urls = array();
		for ($i = 1; $i < 13; $i++) {
			$url = $instance['url_' . $i];
			if ($url != '') {
				$urls[] = $url;
			}
		}
    
		?>
		<table class="diy_investor_stock_portfolio_table" id="<?php echo $this->id; ?>">
			<thead>
			  <tr>
			<?php if($instance['quote_display_column'] == 'month') : ?>
			    <th id="col0">month</th>
			<?php endif; ?>	
				<th id="col1">stock</th>
			<?php if($instance['quote_display_column'] != 'month') : ?>
			    <th class="diy_investor_right">cost</th>
			<?php endif; ?>	
				<th class="diy_investor_right">quote</th>
				<th class="diy_investor_right">change</th>
			</tr>
			</thead>
			<tbody>
				<?php
				$index=0;
                foreach($tickers as $ticker) {
                
					$new_ticker = str_replace('^', '-', $ticker);
					$new_ticker = str_replace('.', '_', $new_ticker);

				?>
					<tr>
					<?php if($instance['quote_display_column'] == 'month') : ?>
						<td><a href=" <?php echo $urls[$index]; ?>"><?php echo $months[$index]; ?></a></td>					
					<?php endif; ?>
						<td class="diy_investor_stock_portfolio_ticker"><a href=" <?php echo $urls[$index]; ?>"><?php echo $ticker; ?></a></td>
					<?php if($instance['quote_display_column'] != 'month') : ?>
						<td class="diy_investor_right">$<?php echo $costs[$index]; ?></td>					
					<?php endif; ?>
						<td class="diy_investor_stock_portfolio_quote_<?php echo $this->id . $new_ticker; ?> diy_investor_stock_portfolio_error diy_investor_right"></td>
						<td class="diy_investor_stock_portfolio_change_pnl_<?php echo $new_ticker; ?> diy_investor_stock_portfolio_error diy_investor_right"></td>
 					</tr>

					<tr style="display: none;">
						<td>
							<input style="display:none;" id="diy_investor_stock_portfolio_widget_<?php echo $this->id; ?>" value="<?php echo implode(',', $tickers); ?>"/>
						</td>
					</tr>
					<tr style="display: none;">
						<td>
							<input style="display:none;" id="diy_investor_stock_portfolio_id_color_<?php echo $this->id; ?>" value="<?php echo isset($instance['quote_display_color']) ? $instance['quote_display_color'] : 'change'; ?>"/>
						</td>
					</tr>
					<tr style="display: none;">
						<td class="diy_investor_stock_portfolio_costs<?php echo $index; ?>"> <?php echo $costs[$index]; ?></a></td>
					</tr>
					<tr style="display: none;">
						<td>
							<input style="display:none;" id="diy_investor_stock_portfolio_id_apikey_<?php echo $this->id; ?>" value="<?php echo isset($instance['apikey']) ? $instance['apikey'] : 'apikey'; ?>"/>
						</td>
					</tr>					
					
				<?php 
      				  $index = $index + 1; 
					}
				?>

			</tbody>
		</table>

		<?php
		echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
		$instance = array();

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['quote_display_color'] = ( ! empty( $new_instance['quote_display_color'] ) ) ? strip_tags( $new_instance['quote_display_color'] ) : '';
		$instance['quote_display_column'] = ( ! empty( $new_instance['quote_display_column'] ) ) ? strip_tags( $new_instance['quote_display_column'] ) : '';
		$instance['apikey'] = ( ! empty( $new_instance['apikey'] ) ) ? strip_tags( $new_instance['apikey'] ) : '';

		foreach ($this->options as $val) {
			$instance[$val['month']] = strip_tags(isset($new_instance[$val['month']]) ? $new_instance[$val['month']] : '');
			$instance[$val['name']] = strip_tags(isset($new_instance[$val['name']]) ? $new_instance[$val['name']] : '');
			$instance[$val['cost']] = strip_tags(isset($new_instance[$val['cost']]) ? $new_instance[$val['cost']] : '77');
			$instance[$val['url']] = strip_tags(isset($new_instance[$val['url']]) ? $new_instance[$val['url']] : '');
			}
        return $instance;
    }

	/** @see WP_Widget::form */
    function form($instance) {

    	if (isset($instance['title'])){
	    	$title = $instance['title'];
    	}else{
	    	$title = __('New title');
	    }

	    if (isset($instance['quote_display_color'])){
	    	$quote_display_color = $instance['quote_display_color'];
    	}else{
	    	$quote_display_color = 'change';
	    }

	    if (isset($instance['quote_display_column'])){
	    	$quote_display_column = $instance['quote_display_column'];
    	}else{
	    	$quote_display_column = 'cost';
	    }

    	if (isset($instance['apikey'])){
	    	$apikey = $instance['apikey'];
    	}else{
	    	$apikey = __('New apikey');
	    }		
    	?>

    	<!-- Title -->
    	<p>
			<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
    	</p>

    	<!-- Display Cost or Month -->
    	<p>
    		<label><?php _e( 'Display Cost or Month Column' ); ?></label><br/>
    		<input type="radio" id="<?php echo $this->get_field_id( 'quote_display_column' ); ?>" name="<?php echo $this->get_field_name( 'quote_display_column' ); ?>" value="cost" <?php echo isset($quote_display_column) && $quote_display_column == 'cost' ? "checked" : ""; ?>/><label><?php _e('Cost'); ?></label>&nbsp;&nbsp;
    		<input type="radio" id="<?php echo $this->get_field_id( 'quote_display_column' ); ?>" name="<?php echo $this->get_field_name( 'quote_display_column' ); ?>" value="month" <?php echo isset($quote_display_column) && $quote_display_column == 'month' ? "checked" : ""; ?>/><label><?php _e('Month'); ?></label>
    	</p>

    	<!-- Quote Display Color -->
    	<p>
    		<label><?php _e( 'Quote Display Color' ); ?></label><br/>
    		<input type="radio" id="<?php echo $this->get_field_id( 'quote_display_color' ); ?>" name="<?php echo $this->get_field_name( 'quote_display_color' ); ?>" value="black" <?php echo isset($quote_display_color) && $quote_display_color == 'black' ? "checked" : ""; ?>/><label><?php _e('Black'); ?></label>&nbsp;&nbsp;
    		<input type="radio" id="<?php echo $this->get_field_id( 'quote_display_color' ); ?>" name="<?php echo $this->get_field_name( 'quote_display_color' ); ?>" value="change" <?php echo isset($quote_display_color) && $quote_display_color == 'change' ? "checked" : ""; ?>/><label><?php _e('Color of "Change" column'); ?></label>
    	</p>

    	<!-- Alpha Vantage API Key -->
    	<p>
			<label for="<?php echo $this->get_field_name( 'apikey' ); ?>"><a href="https://www.alphavantage.co" target="_blank"><?php _e( 'Alpha Vantage API Key' ); ?></a></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'apikey' ); ?>" name="<?php echo $this->get_field_name( 'apikey' ); ?>" type="text" value="<?php echo esc_attr( $apikey ); ?>" />
    	</p>

		
    	<!-- Stock Tickers -->
    	<p>
			<label><?php _e( 'Months, Tickers, Cost Basis & URLs' ); ?></label>
			<ol>

			<?php
			for ($i = 1; $i < 13; $i++) {
				$month = isset($instance['month_'.$i]) ? $instance['month_'.$i] : '';
				$stock = isset($instance['stock_'.$i]) ? $instance['stock_'.$i] : '';
				$cost = isset($instance['cost_'.$i]) ? $instance['cost_'.$i] : '';
				$url = isset($instance['url_'.$i]) ? $instance['url_'.$i] : '';
				
				?>
				<li>
				  <input style="width:32%"; id="<?php echo $this->get_field_id( 'month_'.$i ); ?>" name="<?php echo $this->get_field_name('month_' . $i); ?>" type="text" value="<?php echo esc_attr( $month ); ?>" />
				  <input style="width:31%;" id="<?php echo $this->get_field_id( 'stock_'.$i ); ?>" name="<?php echo $this->get_field_name('stock_' . $i); ?>" type="text" value="<?php echo esc_attr( $stock ); ?>" />
				  <input style="width:31%"; id="<?php echo $this->get_field_id( 'cost_'.$i ); ?>" name="<?php echo $this->get_field_name('cost_' . $i); ?>" type="text" value="<?php echo esc_attr( $cost ); ?>" />
				  <input style="width:100%"; id="<?php echo $this->get_field_id( 'url_'.$i ); ?>" name="<?php echo $this->get_field_name('url_' . $i); ?>" type="text" value="<?php echo esc_attr( $url ); ?>" />
				</li>
				<?php
			}
			?>
			</ol>
		</p>
		<?php
	}
}