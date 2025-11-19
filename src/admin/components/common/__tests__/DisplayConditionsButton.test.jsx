import { render, screen, fireEvent } from '@testing-library/react';
import DisplayConditionsButton from '../DisplayConditionsButton';

jest.mock( '@wordpress/i18n' );

describe( 'DisplayConditionsButton', () => {
	it( 'renders translated label and handles clicks', () => {
		const handleClick = jest.fn();

		render( <DisplayConditionsButton onClick={ handleClick } /> );

		const button = screen.getByRole( 'button', {
			name: /display conditions/i,
		} );

		fireEvent.click( button );

		expect( button ).toHaveClass( 'wpmc-display-conditions-button' );
		expect( handleClick ).toHaveBeenCalledTimes( 1 );
	} );
} );
