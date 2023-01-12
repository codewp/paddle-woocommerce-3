import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { getSubscriptions } from '@paddle/api/subscriptions';
import Pagination from './Pagination';

const columns = [
	{ key: 'order-number', value: __( 'Order', 'paddle' ) },
	{ key: 'user_email', value: __( 'Email', 'paddle' ) },
	{ key: 'date', value: __( 'Date', 'paddle' ) },
	{ key: 'next_bill_date', value: __( 'Next Date', 'paddle' ) },
	{ key: 'status', value: __( 'Status', 'paddle' ) },
	{ key: 'total', value: __( 'Total', 'paddle' ) },
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
			<p className="asnp-search-box">
				<label className="screen-reader-text" for="user-search-input">
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
				<table
					className={ `wp-list-table widefat fixed striped table-view-list posts${ blur }` }
				>
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
									if ( 'total' === key ) {
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
													__html: subscription[ key ],
												} }
											></td>
										);
									}

									return (
										<td
											className={ `column-${ key }` }
											data-title={ value }
											key={
												subscription.order_id +
												'_' +
												key
											}
										>
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
												'next_bill_date' === key ) && (
												<time
													dateTime={
														subscription[
															key + '_time'
														]
													}
												>
													{ subscription[ key ] }
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
													target="_blank"
												>
													{ __( 'Cancel', 'paddle' ) }
												</a>
											) }
											{ -1 ===
												[
													'order-number',
													'date',
													'next_bill_date',
													'actions',
												].indexOf( key ) &&
												null != subscription[ key ] &&
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
			{ ! loading && 0 >= subscriptions.length && (
				<div className="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
					{ __( 'There is not any subscriptions.', 'paddle' ) }
				</div>
			) }
			<div
				className="asnp-paddle-loading"
				style={ { display: loading ? 'block' : 'none' } }
			>
				{ __( 'Loading...', 'paddle' ) }
			</div>
		</>
	);
}
