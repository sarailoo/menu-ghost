import { createRoot, render, StrictMode } from '@wordpress/element';
import './scss/style.scss';

const WPMenuConditions = () => {
	return (
		<>
			<button>Display Condition</button>
		</>
	);
};

window.wp_menu_control.menu_item_ids.forEach( ( itemId ) => {
	const domElementId = `wp-menu-control-${ itemId }`;
	const domElement = document.getElementById( domElementId );

	if (domElement) {
		if (createRoot) {
			createRoot(domElement).render(
				<StrictMode>
					<WPMenuConditions />
				</StrictMode>
			);
		} else {
			render(
				<StrictMode>
					<WPMenuConditions />
				</StrictMode>,
				domElement
			);
		}
	}
} );
