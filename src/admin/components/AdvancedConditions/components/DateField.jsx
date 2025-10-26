import { BaseControl } from '@wordpress/components';
import {
	openNativePicker,
	preventManualEntry,
	preventPaste,
} from './inputGuards';

const DateField = ( { id, label, value, onChange, disabled } ) => (
	<BaseControl id={ id } label={ label }>
		<input
			type="date"
			className="components-text-control__input"
			id={ id }
			value={ value || '' }
			onChange={ ( event ) => onChange( event.target.value ) }
			disabled={ disabled }
			onKeyDown={ preventManualEntry }
			onPaste={ preventPaste }
			onDrop={ preventPaste }
			onMouseDown={ ( event ) => {
				event.preventDefault();
				openNativePicker( event, disabled );
			} }
			inputMode="none"
		/>
	</BaseControl>
);

export default DateField;
