import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { API_ROOT } from './constants';

export const getSubscriptions = async ( args = {} ) => {
	let query = '';
	if ( null != args.page && ! isNaN( args.page * 1 ) && 0 < args.page * 1 ) {
		query += '?page=' + args.page * 1;
	}

	try {
		const response = await apiFetch( {
			path: `${ API_ROOT }/subscriptions${ query }`,
			method: 'GET',
		} );

		if ( response ) {
			return response;
		}

		throw new Error(
			__( 'There was an error on getting subscriptions.', 'paddle' )
		);
	} catch ( error ) {
		throw error;
	}
};
