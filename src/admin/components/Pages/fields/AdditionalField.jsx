import { useCallback, useMemo } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import AsyncEntitySelect from '../AsyncEntitySelect';

const formatStaticOptions = ( options = [] ) =>
	options.map( ( { value, label } ) => ( {
		value,
		label,
	} ) );

const AdditionalField = ( { value, initialLabel, config = {}, onChange } ) => {
	const asyncConfig = config.async ?? null;

	const staticOptions = useMemo( () => {
		const listItems = Array.isArray( config.list ) ? config.list : [];

		if ( asyncConfig || listItems.length === 0 ) {
			return [];
		}

		return [
			{ value: '', label: __( 'All', 'wp-menu-control' ) },
			...formatStaticOptions( listItems ),
		];
	}, [ asyncConfig, config.list ] );

	const handleStaticChange = useCallback(
		( nextValue ) => {
			const match = staticOptions.find(
				( option ) => option.value === nextValue
			);

			onChange?.( {
				value: nextValue,
				label: match?.label || '',
			} );
		},
		[ onChange, staticOptions ]
	);

	if ( asyncConfig ) {
		return (
			<AsyncEntitySelect
				value={ value || '' }
				config={ asyncConfig }
				initialLabel={ initialLabel || '' }
				onChange={ onChange }
			/>
		);
	}

	if ( staticOptions.length === 0 ) {
		return null;
	}

	return (
		<SelectControl
			value={ value || '' }
			options={ staticOptions }
			onChange={ handleStaticChange }
		/>
	);
};

export default AdditionalField;
