import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import './DisplayConditionsButton.scss';

const DisplayConditionsButton = ( { onClick } ) => (
	<Button
		className="wpmc-display-conditions-button"
		variant="secondary"
		onClick={ onClick }
	>
		{ __( 'Display Conditions', 'menu-ghost' ) }
	</Button>
);

export default DisplayConditionsButton;
