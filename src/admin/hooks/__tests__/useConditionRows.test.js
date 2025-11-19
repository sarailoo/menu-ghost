import { renderHook, act } from '@testing-library/react';
import { useConditionRows } from '../useConditionRows';
import { DEFAULT_CONDITION } from '../../utils/conditions';

const scopeIndex = {
	archive: {
		options: [
			{ value: 'archive_date', label: 'Date' },
			{ value: 'archive_author', label: 'Author' },
		],
	},
};

const renderRowsHook = ( rows = [ DEFAULT_CONDITION ], onChange ) =>
	renderHook( () => useConditionRows( rows, scopeIndex, onChange ) );

describe( 'useConditionRows', () => {
	it( 'hydrates incoming rows and exposes handlers', () => {
		const initialRows = [ { ...DEFAULT_CONDITION, scope: 'archive' } ];
		const { result } = renderRowsHook( initialRows );

		expect( result.current.rows[ 0 ].scope ).toBe( 'archive' );

		act( () => result.current.addRow() );
		expect( result.current.rows ).toHaveLength( 2 );
	} );

	it( 'resets dependent fields when scope or subscope change', () => {
		const initialRows = [ { ...DEFAULT_CONDITION, scope: 'entire_site', additional: 'foo' } ];
		const { result } = renderRowsHook( initialRows );

		act( () => result.current.handleFieldChange( 0, 'scope', 'archive' ) );
		expect( result.current.rows[ 0 ].subScope ).toBe( 'archive_date' );
		expect( result.current.rows[ 0 ].additional ).toBe( '' );

		act( () => result.current.handleFieldChange( 0, 'subScope', 'archive_author' ) );
		expect( result.current.rows[ 0 ].additional ).toBe( '' );
	} );

	it( 'normalizes additional selectors and emits onChange callbacks', () => {
		const onChange = jest.fn();
		const { result } = renderRowsHook( [ DEFAULT_CONDITION ], onChange );

		act( () =>
			result.current.handleAdditionalChange( 0, {
				value: 123,
				label: 'My term',
			} )
		);

		expect( result.current.rows[ 0 ].additional ).toBe( '123' );
		expect( result.current.rows[ 0 ].additionalLabel ).toBe( 'My term' );
		expect( onChange ).toHaveBeenCalled();

		act( () => result.current.removeRow( 0 ) );
		expect( result.current.rows ).toHaveLength( 1 );
	} );
} );
