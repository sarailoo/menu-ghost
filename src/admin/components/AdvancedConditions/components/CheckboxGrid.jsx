import { CheckboxControl } from '@wordpress/components';

const CheckboxGrid = ( { label, options = [], value, onChange, disabled } ) => {
	const handleToggle = ( option, checked ) => {
		const next = new Set( value );
		if ( checked ) {
			next.add( option );
		} else {
			next.delete( option );
		}
		onChange( next );
	};

	return (
		<div className="wpmc-advanced__checkbox-group">
			<span className="wpmc-advanced__checkbox-label">{ label }</span>
			<div className="wpmc-advanced__checkbox-grid">
				{ options.map(
					( { value: optionValue, label: optionLabel } ) => (
						<CheckboxControl
							key={ optionValue }
							label={ optionLabel }
							checked={ value.has( optionValue ) }
							onChange={ ( checked ) =>
								handleToggle( optionValue, checked )
							}
							disabled={ disabled }
						/>
					)
				) }
			</div>
		</div>
	);
};

export default CheckboxGrid;
