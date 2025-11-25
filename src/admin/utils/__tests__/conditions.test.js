import {
	DEFAULT_CONDITION,
	hydrateConditions,
	stripConditionExtras,
	stripConditions,
	createEmptyCondition,
} from '../conditions';

describe( 'conditions utils', () => {
	it( 'hydrates empty inputs with a default condition', () => {
		const rows = hydrateConditions();
		expect( rows ).toHaveLength( 1 );
		expect( rows[ 0 ] ).toMatchObject( DEFAULT_CONDITION );
		expect( rows[ 0 ]._key ).toBeTruthy();
	} );

	it( 'preserves existing keys when hydrating arrays', () => {
		const rows = hydrateConditions( [ { _key: 'test', type: 'exclude' } ] );
		expect( rows[ 0 ]._key ).toBe( 'test' );
		expect( rows[ 0 ].type ).toBe( 'exclude' );
	} );

	it( 'strips internal keys from conditions', () => {
		const normalized = stripConditionExtras( {
			...DEFAULT_CONDITION,
			_key: 'row-1',
		} );
		expect( normalized._key ).toBeUndefined();
		const list = stripConditions( [ { _key: 'row-2', type: 'include' } ] );
		expect( list[ 0 ]._key ).toBeUndefined();
	} );

	it( 'creates empty conditions with unique keys', () => {
		const first = createEmptyCondition();
		const second = createEmptyCondition();
		expect( first._key ).not.toBe( second._key );
	} );
} );
