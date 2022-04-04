import { render } from 'react-dom';
import domReady from '@wordpress/dom-ready';
import App from './views/App';

domReady( function () {
	render( <App></App>, document.getElementById( 'paddle-subscriptions' ) );
} );
