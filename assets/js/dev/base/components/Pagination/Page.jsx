import React from 'react';

export default function Page( {
	isCurrent = false,
	pageKey,
	onClick,
	children,
	className='',
} ) {
	if ( isCurrent ) {
		className += ' current';
	}

	return (
		<button className={ className } onClick={ () => onClick() }>
			{ pageKey === 'prev' && <span className="fas fa-angle-left" /> }
			{ children }
			{ pageKey === 'next' && <span className="fas fa-angle-right" /> }
		</button>
	);
}
