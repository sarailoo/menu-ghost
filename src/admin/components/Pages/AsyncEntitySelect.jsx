import { useCallback, useEffect, useMemo, useState } from '@wordpress/element';
import { ComboboxControl } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';

const normalizeItems = ( items = [] ) =>
	[ ...items ].reduce( ( acc, item ) => {
		if ( ! item || ! item.value ) {
			return acc;
		}
		if ( acc.some( ( existing ) => existing.value === item.value ) ) {
			return acc;
		}
		acc.push( {
			value: String( item.value ),
			label: item.label || item.value,
		} );
		return acc;
	}, [] );

const fetchPath = ( type, params = {} ) => {
	const query = { type };
	Object.entries( params ).forEach( ( [ key, value ] ) => {
		if ( value !== undefined && value !== null && value !== '' ) {
			query[ key ] = value;
		}
	} );
	return addQueryArgs( '/menu-control/v1/search', query );
};

const AsyncEntitySelect = ( {
	value,
	config = {},
	initialLabel = '',
	onChange,
} ) => {
	const { type, params = {} } = config;
	const [ options, setOptions ] = useState( [] );
	const [ resolvedOption, setResolvedOption ] = useState( () =>
		value && initialLabel
			? { value: String( value ), label: initialLabel }
			: null
	);
	const [ isLoading, setIsLoading ] = useState( false );

	const mergedOptions = useMemo( () => {
		const list = normalizeItems( options );
		if (
			resolvedOption &&
			resolvedOption.value &&
			! list.some( ( item ) => item.value === resolvedOption.value )
		) {
			list.unshift( resolvedOption );
		}
		return [ { value: '', label: __( 'All', 'menu-control' ) }, ...list ];
	}, [ options, resolvedOption ] );

	const queryItems = useCallback(
		( search = '' ) => {
			if ( ! type ) {
				return;
			}
			setIsLoading( true );
			apiFetch( {
				path: addQueryArgs( fetchPath( type, params ), {
					search,
					page: 1,
				} ),
			} )
				.then( ( response ) => {
					if ( response && Array.isArray( response.items ) ) {
						setOptions( response.items );
					}
				} )
				.finally( () => setIsLoading( false ) );
		},
		[ type, params ]
	);

	useEffect( () => {
		queryItems( '' );
	}, [ queryItems ] );

	useEffect( () => {
		if ( ! value ) {
			setResolvedOption( null );
			return;
		}

		if ( initialLabel ) {
			setResolvedOption( {
				value: String( value ),
				label: initialLabel,
			} );
			return;
		}

		let aborted = false;

		apiFetch( {
			path: addQueryArgs( fetchPath( type, params ), {
				id: value,
			} ),
		} )
			.then( ( response ) => {
				if ( aborted ) {
					return;
				}
				const item = response?.items?.[ 0 ] ?? null;
				if ( item ) {
					setResolvedOption( {
						value: String( item.value ),
						label: item.label || item.value,
					} );
				}
			} )
			.catch( () => {
				if ( ! aborted ) {
					setResolvedOption( null );
				}
			} );

		return () => {
			aborted = true;
		};
	}, [ value, type, params, initialLabel ] );

	const handleChange = useCallback(
		( nextValue ) => {
			const match = mergedOptions.find(
				( item ) => item.value === nextValue
			);
			const payload = {
				value: nextValue ?? '',
				label: match?.label || '',
			};
			if ( ! match && nextValue ) {
				setResolvedOption( {
					value: String( nextValue ),
					label: payload.label,
				} );
			} else {
				setResolvedOption( match || null );
			}
			onChange( payload );
		},
		[ mergedOptions, onChange ]
	);

	return (
		<ComboboxControl
			value={ value || '' }
			onChange={ handleChange }
			onInputChange={ ( nextInput ) => queryItems( nextInput ) }
			onFilterValueChange={ ( filterValue ) => queryItems( filterValue ) }
			options={ mergedOptions }
			allowReset
			__nextHasNoMarginBottom
			__experimentalShowSelectedHint
			aria-busy={ isLoading }
		/>
	);
};

export default AsyncEntitySelect;
