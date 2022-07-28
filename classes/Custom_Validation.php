<?php
namespace TUTOR;
/**
 * Custom Valaidation Trait
 */
trait Custom_Validation
{
	/**
	 * Check whether order value is asc or desc
	 *
	 * @param string $order
	 * @return bool
	 */
	public function validate_order( $order )
	{
		return in_array( strtolower( $order ), array( 'asc', 'desc' ) );
	}
}
?>