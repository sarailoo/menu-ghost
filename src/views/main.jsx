import { createRoot, render, StrictMode } from '@wordpress/element';

import './scss/style.scss';

const domElement = document.getElementById(
	window.wp_menu_control.dom_element_id
);

const WPMenuConditions = () => {
	return (
		<>
		</>
	);
};

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
