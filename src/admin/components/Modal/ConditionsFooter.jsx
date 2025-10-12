import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const ConditionsFooter = ( { onCancel, onSave, saving } ) => (
	<footer className="wpmc-conditions-modal__footer">
		<Button variant="secondary" onClick={ onCancel } disabled={ saving }>
			{ __( 'Cancel', 'wp-menu-control' ) }
		</Button>
		<Button
			variant="primary"
			className="wpmc-conditions-modal__save"
			onClick={ onSave }
			disabled={ saving }
		>
			{ saving
				? __( 'Saving…', 'wp-menu-control' )
				: __( 'Save & Close', 'wp-menu-control' ) }
		</Button>
	</footer>
);

export default ConditionsFooter;
