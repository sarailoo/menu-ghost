import { SelectControl } from '@wordpress/components';

const mapOptions = ( options = [] ) =>
	options.map( ( { value, label } ) => ( {
		value,
		label,
	} ) );

const SubScopeSelect = ( { value, options = [], onChange } ) => {
	if ( ! options.length ) {
		return null;
	}

	return (
		<SelectControl
			value={ value }
			options={ mapOptions( options ) }
			onChange={ ( nextValue ) => onChange?.( nextValue ) }
		/>
	);
};

export default SubScopeSelect;
