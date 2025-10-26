import { SelectControl } from '@wordpress/components';

const mapOptions = ( options = [] ) =>
	options.map( ( { value, label } ) => ( {
		value,
		label,
	} ) );

const ConditionTypeSelect = ( { value, options, onChange } ) => (
	<SelectControl
		value={ value }
		options={ mapOptions( options ) }
		onChange={ ( nextValue ) => onChange?.( nextValue ) }
	/>
);

export default ConditionTypeSelect;
