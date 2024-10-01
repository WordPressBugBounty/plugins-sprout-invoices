<?php
	$fields['name']['default'] = ( get_the_title( $id ) != 'Auto Draft' ) ? get_the_title( $id ) : '';
	if ( ! empty( $associated_users ) ) {
		unset( $fields['email'] );
		unset( $fields['first_name'] );
		unset( $fields['last_name'] );
	}
	$fields['street']['default']      = ( isset( $address['street'] ) && ! empty( $address['street'] ) ) ? $address['street'] : $fields['street']['default'];
	$fields['street_2']['default']    = ( isset( $address['street_2'] ) && ! empty( $address['street_2'] ) ) ? $address['street_2'] : $fields['street_2']['default'];
	$fields['city']['default']        = ( isset( $address['city'] ) && ! empty( $address['city'] ) ) ? $address['city'] : $fields['city']['default'];
	$fields['zone']['default']        = ( isset( $address['zone'] ) && ! empty( $address['zone'] ) ) ? $address['zone'] : $fields['zone']['default'];
	$fields['postal_code']['default'] = ( isset( $address['postal_code'] ) && ! empty( $address['postal_code'] ) ) ? $address['postal_code'] : $fields['postal_code']['default'];
	$fields['country']['default']     = ( isset( $address['country'] ) && ! empty( $address['country'] ) ) ? $address['country'] : $fields['country']['default'];
?>
<div id="client_fields" class="admin_fields clearfix">
	<?php sa_admin_fields( $fields ); ?>
</div>
