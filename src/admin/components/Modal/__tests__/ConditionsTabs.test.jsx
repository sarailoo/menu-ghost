import { render, screen, fireEvent } from '@testing-library/react';
import ConditionsTabs from '../ConditionsTabs';

jest.mock( '@wordpress/i18n' );

const makeTabs = () => [
	{ name: 'pages', title: 'Pages', content: <div>Pages content</div> },
	{ name: 'advanced', title: 'Advanced', render: () => <div>Advanced content</div> },
];

describe( 'ConditionsTabs', () => {
	it( 'renders content for the active tab', () => {
		render( <ConditionsTabs tabs={ makeTabs() } /> );

		expect( screen.getByText( 'Pages content' ) ).toBeInTheDocument();
		expect( screen.queryByText( 'Advanced content' ) ).not.toBeInTheDocument();
	} );

	it( 'switches tabs when the user clicks a tab control', () => {
		render( <ConditionsTabs tabs={ makeTabs() } /> );

		const advancedToggle = screen.getByRole( 'button', { name: 'Advanced' } );
		fireEvent.click( advancedToggle );

		expect( screen.getByText( 'Advanced content' ) ).toBeInTheDocument();
		expect( screen.queryByText( 'Pages content' ) ).not.toBeInTheDocument();
	} );
} );
