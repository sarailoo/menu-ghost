import { useCallback, useEffect, useState } from '@wordpress/element';
import { createEmptyCondition, hydrateConditions } from '../utils/conditions';

const ensureAdditionalShape = ( additional ) => {
	if (
		additional &&
		typeof additional === 'object' &&
		additional.value !== undefined
	) {
		return {
			value: additional.value !== null ? String( additional.value ) : '',
			label: additional.label || '',
		};
	}

	return {
		value: additional ? String( additional ) : '',
		label: '',
	};
};

export const useConditionRows = (
	sourceRows,
	scopeIndex,
	onChange = undefined
) => {
	const [ rows, setRows ] = useState( () => hydrateConditions( sourceRows ) );

	const emitChange = useCallback(
		( nextRows ) => {
			if ( typeof onChange === 'function' ) {
				onChange( nextRows );
			}
		},
		[ onChange ]
	);

	useEffect( () => {
		setRows( hydrateConditions( sourceRows ) );
	}, [ sourceRows ] );

	const handleFieldChange = useCallback(
		( index, field, value ) => {
			setRows( ( current ) => {
				const next = current.map( ( row, idx ) => {
					if ( idx !== index ) {
						return row;
					}

					let nextRow = {
						...row,
						[ field ]: value,
					};

					if ( field === 'scope' ) {
						const scopeConfig = scopeIndex[ value ] || {};
						const firstOption =
							scopeConfig.options?.[ 0 ]?.value || '';

						nextRow = {
							...nextRow,
							subScope: firstOption,
							additional: '',
							additionalLabel: '',
						};
					}

					if ( field === 'subScope' ) {
						nextRow = {
							...nextRow,
							additional: '',
							additionalLabel: '',
						};
					}

					return nextRow;
				} );

				emitChange( next );
				return next;
			} );
		},
		[ emitChange, scopeIndex ]
	);

	const handleAdditionalChange = useCallback(
		( index, additional ) => {
			const { value, label } = ensureAdditionalShape( additional );

			setRows( ( current ) => {
				const next = current.map( ( row, idx ) =>
					idx === index
						? {
								...row,
								additional: value,
								additionalLabel: label,
						  }
						: row
				);

				emitChange( next );
				return next;
			} );
		},
		[ emitChange ]
	);

	const addRow = useCallback( () => {
		setRows( ( current ) => {
			const next = [ ...current, createEmptyCondition() ];
			emitChange( next );
			return next;
		} );
	}, [ emitChange ] );

	const removeRow = useCallback(
		( index ) => {
			setRows( ( current ) => {
				if ( current.length <= 1 ) {
					return current;
				}

				const next = current.filter( ( _, idx ) => idx !== index );
				emitChange( next );
				return next;
			} );
		},
		[ emitChange ]
	);

	return {
		rows,
		handleFieldChange,
		handleAdditionalChange,
		addRow,
		removeRow,
	};
};
