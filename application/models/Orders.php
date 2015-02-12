<?php

/**
 * Data access wrapper for "orders" table.
 *
 * @author jim
 */
class Orders extends MY_Model {

    // constructor
    function __construct() {
        parent::__construct('orders', 'num');
    }

    // add an item to an order
    function add_item($num, $code) {
		
		$CI = &get_instance();
		$CI->load->model( 'orderitems' );
		
        $new_item = $this->orderitems->get( $num, $code );
		if( $new_item === null )
		{
			$new_item = array( 'order'=>$num, 'item'=>$code, 'quantity'=>1 );
			$this->orderitems->add( $new_item );
		}
		else
		{
			$new_item['quantity'] = $new_item['quantity'] + 1;
			$this->orderitems->update( $new_item );
		}
    }

    // calculate the total for an order
    function total($num) {
		$CI = &get_instance();
		$CI->load->model( 'orderitems' );
		
		$items = $this->orderitems->some('order', $num);
		$result = 0;
		foreach( $items as $item )
		{
			$menuitem = $this->menu->get( $item->item );
			$result += $item->quantity * $menuitem->price;
		}
        return $result;
    }

    // retrieve the details for an order
    function details($num) {
        $CI = &get_instance();
		$CI->load->model( 'orderitems' );
		$result['items'] = $this->orderitems->some( 'order', $num );
		$result['total'] = $this->total( $num );
		return $result;
    }

    // cancel an order
    function flush($num) {
        $CI = &get_instance();
		$CI->load->model( 'orderitems' );
		
		$this->orderitems->delete_some( $num );
    }

    // validate an order
    // it must have at least one item from each category
    function validate($num) {
		$CI = &get_instance();
		$items = $CI->orderitems->group($num);
		$gotem = array();
		
		if( count($items) > 0 )
			foreach( $items as $item )
			{
				$menu = $CI->menu->get($item->item);
				$gotem[$menu->category] = 1;
			}
		
		return isset($gotem['m']) && isset($gotem['d']) && isset($gotem['s']);
    }

}
