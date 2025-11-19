import { renderHook, act } from '@testing-library/react';
import useSyncedState from '../useSyncedState';

describe( 'useSyncedState', () => {
	it( 'returns state synced with incoming props', () => {
		const { result, rerender } = renderHook( ( { value } ) => useSyncedState( value ), {
			initialProps: { value: 'initial' },
		} );

		const [ state ] = result.current;
		expect( state ).toBe( 'initial' );

		rerender( { value: 'next' } );
		expect( result.current[ 0 ] ).toBe( 'next' );
	} );

	it( 'allows local updates without breaking sync', () => {
		const { result, rerender } = renderHook( ( { value } ) => useSyncedState( value ), {
			initialProps: { value: 'one' },
		} );

		act( () => {
			const [ , setState ] = result.current;
			setState( 'local' );
		} );

		expect( result.current[ 0 ] ).toBe( 'local' );

		rerender( { value: 'two' } );
		expect( result.current[ 0 ] ).toBe( 'two' );
	} );
} );
