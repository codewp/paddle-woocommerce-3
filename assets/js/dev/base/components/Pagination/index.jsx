import React from 'react';
import { __ } from '@wordpress/i18n';
import Page from './page';
import './style.scss';

export default function Pagination( {
	current,
	total,
	endSize = 1,
	midSize = 2,
	nextText,
	prevText,
	onClickPage,
} ) {
	if ( ! total ) {
		return null;
	}
	endSize = endSize < 1 ? 1 : endSize;
	midSize = midSize < 0 ? 2 : midSize;
	let dots = false;
	let pages = [];

	if ( current && current > 1 ) {
		pages.push( {
			isCurrent: false,
			key: 'prev',
			onClick: () => onClickPage( current - 1 ),
			className: 'asnp-pagination-prev',
			text: prevText,
		} );
	}

	for ( let n = 1; n <= total; n++ ) {
		let isCurrent = n === current;

		if ( isCurrent ) {
			dots = true;
			pages.push( {
				isCurrent: true,
				key: n,
				onClick: () => onClickPage( n ),
				className: 'asnp-pages',
				text: n,
			} );
		} else {
			if (
				n <= endSize ||
				( current &&
					n >= current - midSize &&
					n <= current + midSize ) ||
				n > total - endSize
			) {
				pages.push( {
					isLink: true,
					key: n,
					onClick: () => onClickPage( n ),
					className: 'asnp-pages',
					text: n,
				} );
				dots = true;
			} else if ( dots ) {
				pages.push( {
					isDots: true,
					key: n,
					onClick:()=>console.log('dots'),
					className: 'asnp-pages',
					text: '...',
				} );
				dots = false;
			}
		}
	}

	if ( current && current < total ) {
		pages.push( {
			isCurrent: false,
			key: 'next',
			onClick: () => onClickPage( current + 1 ),
			className: 'asnp-pagination-next',
			text: nextText,
		} );
	}

	return (
			<div className="asnp-product-pagination">
				{ pages.map(
					( { isCurrent, key, text, className, onClick } ) => (
						<Page
							key={ key }
							isCurrent={ isCurrent }
							pageKey={ key }
							onClick={ () => onClick() }
							className={ className }
						>
							{ text }
						</Page>
					)
				) }
			</div>
	);
}
