import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { getAccountSubscriptions } from '@paddle/api/account-subscriptions';
import Pagination from './Pagination';

const columns = [
	{
		key: 'order-number',
		value: __( 'Order', 'paddle' ),
	},
	{
		key: 'date',
		value: __( 'Date', 'paddle' ),
	},
	{
		key: 'status',
		value: __( 'Status', 'paddle' ),
	},
	{
		key: 'total',
		value: __( 'Total', 'paddle' ),
	},
	{
		key: 'actions',
		value: __( 'Actions', 'paddle' ),
	},
];

export default function Subscriptions() {
	const [ subscriptions, setSubscriptions ] = useState( [] );
	const [ pages, setPages ] = useState( 1 );
	const [ page, setPage ] = useState( 1 );
	const [ loading, setLoading ] = useState( false );

	useEffect( async () => {
		try {
			setLoading( true );
			console.log( page );
			let response = await getAccountSubscriptions( { page } );
			setSubscriptions(
				response.items && response.items.length ? response.items : []
			);
			setPages( null != response.pages ? response.pages * 1 : 1 );
			setPage( null != response.page ? response.page * 1 : 1 );
		} catch ( error ) {
			console.error( error );
		}

		setLoading( false );
	}, [ page ] );

	const next = ( e ) => {
		e.preventDefault();
		setPage( page + 1 );
	};

	const previous = ( e ) => {
		e.preventDefault();
		setPage( page - 1 );
	};

	return (
		<>
			<table className="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
				<thead>
					<tr>
						{ columns.map( ( { key, value } ) => (
							<th
								className={ `woocommerce-orders-table__header woocommerce-orders-table__header-${ key }` }
								key={ key }
							>
								<span>{ value }</span>
							</th>
						) ) }
					</tr>
				</thead>

				<tbody>
					{ subscriptions.map( ( subscription ) => (
						<tr
							className={ `woocommerce-orders-table__row woocommerce-orders-table__row--status-${ subscription.status } order` }
							key={ subscription.order_id }
						>
							{ columns.map( ( { key, value } ) => (
								<td
									className={ `woocommerce-orders-table__cell woocommerce-orders-table__cell-${ key }` }
									data-title={ value }
									key={ subscription.order_id + '_' + key }
								>
									{ 'order-number' === key && (
										<a
											href={
												subscription.order_url
													? subscription.order_url
													: '#'
											}
										>
											#{ subscription.order_id }
										</a>
									) }
									{ 'date' === key && (
										<time dateTime={ subscription.date }>
											{ subscription.date }
										</time>
									) }
									{ 'actions' === key && (
										<a
											href={
												subscription.cancel_url
													? subscription.cancel_url
													: '#'
											}
											className="woocommerce-button button"
										>
											{ __( 'Cancel', 'paddle' ) }
										</a>
									) }
									{ 'order-number' !== key &&
										'date' !== key &&
										'actions' !== key &&
										null != subscription[ key ] &&
										subscription[ key ] }
								</td>
							) ) }
						</tr>
					) ) }
				</tbody>
			</table>
			<Pagination
				pages={ pages }
				page={ page }
				next={ next }
				previous={ previous }
			/>
		</>
	);
}
