import { Notice } from '@wordpress/components';

const ConditionsErrorNotice = ( { message, onRemove } ) => {
	if ( ! message ) {
		return null;
	}

	return (
		<Notice
			className="wpmc-conditions-modal__notice"
			status="error"
			onRemove={ onRemove }
			isDismissible
		>
			{ message }
		</Notice>
	);
};

export default ConditionsErrorNotice;
