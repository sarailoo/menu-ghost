import { createRoot, render } from '@wordpress/element';
import App from './App';
import './styles/admin.scss';

const globalSettings = window.menu_control || {};
const {
	menu_items: menuItems = [],
	page_conditions: pageConditionsRaw,
	advanced_meta: advancedMeta,
	saved_settings: savedSettings = {},
	ajax_url: ajaxUrl,
	nonce,
} = globalSettings;

const pageConditions = pageConditionsRaw || { conditionTypes: [], scopes: [] };

menuItems.forEach( ( { id, title } ) => {
	const containerId = `menu-control-${ id }`;
	const container = document.getElementById( containerId );
	if ( ! container ) {
		return;
	}

	const initial = savedSettings[ id ] || { pages: [], advanced: [] };

	const app = (
		<App
			itemId={ id }
			menuTitle={ title }
			pageConditions={ pageConditions }
			advancedMeta={ advancedMeta }
			initialPages={ initial.pages || [] }
			initialAdvanced={ initial.advanced || [] }
			ajaxUrl={ ajaxUrl }
			nonce={ nonce }
		/>
	);

	if ( createRoot ) {
		createRoot( container ).render( app );
	} else {
		render( app, container );
	}
} );
