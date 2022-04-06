import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import Subscriptions from '../../components/Subscriptions';

import './style.scss';

export default function App() {
	return (
		<>
			<Subscriptions />
		</>
	);
}
