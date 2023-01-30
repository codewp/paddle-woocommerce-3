import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { getSubscriptions } from '@paddle/api/subscriptions';
import Pagination from './Pagination';

const columns = [
	{ key: 'id', value: __( 'ID', 'paddle' ) },
	{ key: 'order-number', value: __( 'Order', 'paddle' ) },
	{ key: 'user_email', value: __( 'Email', 'paddle' ) },
	{ key: 'date', value: __( 'Date', 'paddle' ) },
	{ key: 'next_bill_date', value: __( 'Next Date', 'paddle' ) },
	{ key: 'status', value: __( 'Status', 'paddle' ) },
	{
		key: 'next_payment_amount',
		value: __( 'Next Amount', 'paddle' ),
	},
	{ key: 'actions', value: __( 'Actions', 'paddle' ) },
];

export default function Subscriptions() {
	const [ subscriptions, setSubscriptions ] = useState( [] );
	const [ pages, setPages ] = useState( 1 );
	const [ page, setPage ] = useState( 1 );
	const [ search, setSearch ] = useState( '' );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		getItems( { page } );
	}, [ page ] );

	const getItems = async ( args ) => {
		try {
			setLoading( true );
			let response = await getSubscriptions( args );
			setSubscriptions(
				response.items && response.items.length ? response.items : []
			);
			setPages( null != response.pages ? response.pages * 1 : 1 );
		} catch ( error ) {
			console.error( error );
		}
		setLoading( false );
	};

	const searchItems = () => {
		setPage( 1 );
		getItems( { page, search } );
	};

	const getStatusText = ( status ) => {
		switch ( status.toLowerCase() ) {
			case 'active':
				return __( 'Active', 'paddle' );

			case 'cancelled':
			case 'deleted':
				return __( 'Cancelled', 'paddle' );

			case 'paused':
				return __( 'Paused', 'paddle' );

			case 'trialing':
				return __( 'Trialing', 'paddle' );

			case 'past_due':
				return __( 'Past Due', 'paddle' );
		}

		return status;
	};

	const getDateText = ( subscription, key ) => {
		if ( 'next_bill_date' !== key ) {
			return subscription[ key ];
		}

		if (
			'deleted' === subscription[ 'status' ] ||
			'cancelled' === subscription[ 'status' ]
		) {
			return __( 'N/A', 'paddle' );
		}

		return subscription[ key ];
	};

	const getIdText = ( subscription ) => {
		if (
			null != subscription[ 'id' ] &&
			null != subscription[ 'subscription_id' ] &&
			0 < subscription[ 'id' ] * 1 &&
			'' !== subscription[ 'subscription_id' ].trim()
		) {
			return (
				subscription[ 'id' ] * 1 +
				'-' +
				subscription[ 'subscription_id' ].trim()
			);
		}

		if ( null != subscription[ 'id' ] && 0 < subscription[ 'id' ] * 1 ) {
			return subscription[ 'id' ] * 1;
		}

		return 'undefined' !== typeof subscription[ 'subscription_id' ] &&
			'' !== subscription[ 'subscription_id' ].trim()
			? subscription[ 'subscription_id' ].trim()
			: '';
	};

	const next = ( e ) => {
		e.preventDefault();
		setPage( page + 1 );
	};

	const previous = ( e ) => {
		e.preventDefault();
		setPage( page - 1 );
	};

	let blur = loading ? ' asnp-paddle-blur' : '';

	return (
		<>
			<div className={ `asnp-subscriptions${ blur }` }>
				<p className="asnp-search-box">
					<label
						className="screen-reader-text"
						for="user-search-input"
					>
						{ __( 'Search Subscriptions:', 'paddle' ) }
					</label>
					<input
						type="search"
						id="subscription-search-input"
						name="search"
						onChange={ ( e ) => setSearch( e.target.value ) }
					/>
					<input
						type="submit"
						id="search-submit"
						class="button"
						value={ __( 'Search', 'paddle' ) }
						onClick={ searchItems }
					/>
				</p>
				{ 0 < subscriptions.length && (
					<table className="wp-list-table widefat fixed striped table-view-list posts">
						<thead>
							<tr>
								{ columns.map( ( { key, value } ) => (
									<th
										className={ `manage-column column-${ key }` }
										key={ key }
									>
										{ value }
									</th>
								) ) }
							</tr>
						</thead>

						<tbody>
							{ subscriptions.map( ( subscription ) => (
								<tr
									className={ `iedit author-self level-0 type-paddle-subscriptions hentry status-${ subscription.status }` }
									key={ subscription.order_id }
								>
									{ columns.map( ( { key, value } ) => {
										if ( 'next_payment_amount' === key ) {
											return (
												<td
													className={ `column-${ key }` }
													data-title={ value }
													key={
														subscription.order_id +
														'_' +
														key
													}
													dangerouslySetInnerHTML={ {
														__html:
															subscription[ key ],
													} }
												></td>
											);
										}

										return (
											<td
												className={
													`column-${ key }` +
													( 'status' === key
														? ` asnp-paddle-status-${ subscription[ key ] }`
														: '' )
												}
												data-title={ value }
												key={
													subscription.order_id +
													'_' +
													key
												}
											>
												{ 'id' === key &&
													getIdText( subscription ) }
												{ 'order-number' === key && (
													<a
														href={
															subscription.order_url
																? subscription.order_url
																: '#'
														}
														target="_blank"
													>
														#
														{ subscription.order_id +
															( subscription.user_name
																? ' ' +
																  subscription.user_name
																: '' ) }
													</a>
												) }
												{ ( 'date' === key ||
													'next_bill_date' ===
														key ) && (
													<time
														dateTime={
															subscription[
																key + '_time'
															]
														}
													>
														{ getDateText(
															subscription,
															key
														) }
													</time>
												) }
												{ 'actions' === key && (
													<>
														{ null !=
															subscription.cancel_url &&
															( null ==
																subscription.status ||
																'deleted' !==
																	subscription.status ) && (
																<a
																	href={
																		subscription.cancel_url
																			? subscription.cancel_url
																			: '#'
																	}
																	className="woocommerce-button button"
																	target="_blank"
																>
																	{ __(
																		'Cancel',
																		'paddle'
																	) }
																</a>
															) }
														{ null !=
															subscription.update_url &&
															null !=
																subscription.status &&
															'deleted' !==
																subscription.status && (
																<a
																	href={
																		null !=
																		subscription.update_url
																			? subscription.update_url
																			: '#'
																	}
																	className="woocommerce-button button"
																	target="_blank"
																>
																	{ __(
																		'Update',
																		'paddle'
																	) }
																</a>
															) }
													</>
												) }
												{ 'status' === key &&
													getStatusText(
														subscription[ key ]
													) }
												{ -1 ===
													[
														'id',
														'order-number',
														'date',
														'next_bill_date',
														'actions',
														'status',
													].indexOf( key ) &&
													null !=
														subscription[ key ] &&
													subscription[ key ] }
											</td>
										);
									} ) }
								</tr>
							) ) }
						</tbody>
					</table>
				) }
				{ 0 < subscriptions.length && (
					<Pagination
						pages={ pages }
						page={ page }
						next={ next }
						previous={ previous }
						disabled={ loading }
					/>
				) }
				{ 0 >= subscriptions.length && (
					<div className="asnp-message update-nag notice notice-info">
						{ loading
							? __( 'Loading subscriptions...', 'paddle' )
							: __(
									'There is not any subscriptions.',
									'paddle'
							  ) }
					</div>
				) }
			</div>
			<div
				className="asnp-paddle-loading"
				style={ { display: loading ? 'block' : 'none' } }
			>
				{ __( 'Loading...', 'paddle' ) }
			</div>
		</>
	);
}
