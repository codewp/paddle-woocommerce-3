import React from 'react';
import { __ } from '@wordpress/i18n';

export default function Pagination( { pages, page, previous, next } ) {
	if ( 1 >= pages ) {
		return null;
	}

	return (
		<div className="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			{ 1 !== page && (
				<a
					className="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button"
					onClick={ ( e ) => previous( e ) }
				>
					{ __( 'Previous', 'paddle' ) }
				</a>
			) }
			{ page !== pages && (
				<a
					className="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button"
					onClick={ ( e ) => next( e ) }
				>
					{ __( 'Next', 'paddle' ) }
				</a>
			) }
		</div>
	);
}
