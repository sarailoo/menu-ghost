/* eslint-env jest */
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import ConditionRow from '../ConditionRow';

jest.mock( '../fields/ConditionTypeSelect', () => ( props ) => (
	<button
		data-testid="type-select"
		onClick={ () => props.onChange( 'exclude' ) }
	>
		Type: { props.value }
	</button>
) );

jest.mock( '../fields/ScopeSelect', () => ( props ) => (
	<button
		data-testid="scope-select"
		onClick={ () => props.onChange( 'archive' ) }
	>
		Scope: { props.value }
	</button>
) );

jest.mock( '../fields/SubScopeSelect', () => ( props ) => (
	<div data-testid="subscope-select">Sub: { props.value }</div>
) );

jest.mock( '../fields/AdditionalField', () => ( props ) => (
	<div data-testid="additional-field">Additional: { props.value }</div>
) );

const baseCondition = {
	type: 'include',
	scope: 'archive',
	subScope: 'archive_date',
	additional: '',
	additionalLabel: '',
};

const scopeIndex = {
	archive: {
		options: [ { value: 'archive_date', label: 'Date' } ],
		additionalData: {
			archive_date: {
				list: [ { value: '1', label: 'One' } ],
			},
		},
	},
};

describe( 'ConditionRow', () => {
	it( 'renders sub-scope and additional fields when provided', async () => {
		const onRemove = jest.fn();
		render(
			<ConditionRow
				condition={ baseCondition }
				conditionTypes={ [] }
				scopes={ [] }
				scopeIndex={ scopeIndex }
				onChange={ jest.fn() }
				onRemove={ onRemove }
			/>
		);

		expect( screen.getByTestId( 'subscope-select' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'additional-field' ) ).toBeInTheDocument();

		const user = userEvent.setup();
		await user.click(
			screen.getByRole( 'button', { name: /remove rule/i } )
		);
		expect( onRemove ).toHaveBeenCalled();
	} );

	it( 'falls back to defaults when no matching scope exists', () => {
		const condition = { ...baseCondition, scope: 'unknown', subScope: '' };
		render(
			<ConditionRow
				condition={ condition }
				conditionTypes={ [] }
				scopes={ [ { value: 'archive', label: 'Archive' } ] }
				scopeIndex={ scopeIndex }
				onChange={ jest.fn() }
				onRemove={ jest.fn() }
			/>
		);

		expect( screen.getByTestId( 'subscope-select' ) ).toBeInTheDocument();
	} );
} );
