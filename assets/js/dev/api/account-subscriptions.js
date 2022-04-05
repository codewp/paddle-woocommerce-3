import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { API_ROOT } from './constants';

export const getAccountSubscriptions = async ( args = {} ) => {
	if ( args.page && 1 == args.page ) {
		return {
			items: [
				{
					order_id: '12345',
					subscription_id: '12345',
					user_id: '12345',
					status: 'active',
					date: 'March 17, 2022',
					total: '150$',
				},
				{
					order_id: '123456',
					subscription_id: '123456',
					user_id: '123456',
					status: 'closed',
					date: 'March 18, 2022',
					total: '30$',
				},
			],
			pages: 2,
			page: 1,
		};
	}
	return {
		items: [
			{
				order_id: '987654',
				subscription_id: '987654',
				user_id: '987654',
				status: 'closed',
				date: 'March 17, 2022',
				total: '20$',
			},
			{
				order_id: '9876543',
				subscription_id: '9876543',
				user_id: '9876543',
				status: 'closed',
				date: 'March 18, 2022',
				total: '350$',
			},
		],
		pages: 2,
		page: 2,
	};

	let query = '';
	if ( null != args.page && ! isNaN( args.page * 1 ) && 0 < args.page * 1 ) {
		query += '?page=' + args.page * 1;
	}

	try {
		const response = await apiFetch( {
			path: `${ API_ROOT }/account-subscriptions${ query }`,
			method: 'GET',
		} );

		if ( response && 1 == response.success ) {
			return response;
		}

		throw new Error(
			__( 'There was an error on getting subscriptions.', 'paddle' )
		);
	} catch ( error ) {
		throw error;
	}
};
