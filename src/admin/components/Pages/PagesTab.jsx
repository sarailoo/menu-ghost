import { forwardRef, useImperativeHandle, useMemo } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ConditionRow from './ConditionRow';
import { useConditionRows } from '../../hooks/useConditionRows';
import { stripConditions } from '../../utils/conditions';
import './PagesTab.scss';

const PagesTab = forwardRef(
	(
		{
			initialConditions,
			value,
			pageConditions = { conditionTypes: [], scopes: [] },
			onConditionsChange,
		},
		ref
	) => {
		const source = value ?? initialConditions;
		const scopeIndex = useMemo( () => {
			return Object.fromEntries(
				( pageConditions.scopes || [] ).map( ( scope ) => [
					scope.value,
					scope,
				] )
			);
		}, [ pageConditions.scopes ] );

		const {
			rows,
			handleFieldChange,
			handleAdditionalChange,
			addRow,
			removeRow,
		} = useConditionRows( source, scopeIndex, onConditionsChange );

		useImperativeHandle(
			ref,
			() => ( {
				getValue: () => stripConditions( rows ),
			} ),
			[ rows ]
		);

		return (
			<div className="wpmc-pages-tab">
				<div className="wpmc-pages-tab__list">
					{ rows.map( ( row, index ) => (
						<ConditionRow
							key={ row._key }
							condition={ row }
							conditionTypes={
								pageConditions.conditionTypes || []
							}
							scopes={ pageConditions.scopes || [] }
							scopeIndex={ scopeIndex }
							onChange={ ( field, nextValue ) => {
								if ( field === 'additional' ) {
									handleAdditionalChange( index, nextValue );
									return;
								}
								handleFieldChange( index, field, nextValue );
							} }
							onRemove={ () => removeRow( index ) }
						/>
					) ) }
				</div>

				<Button
					variant="secondary"
					onClick={ addRow }
					className="wpmc-pages-tab__add"
				>
					{ __( 'Add Condition', 'menu-control' ) }
				</Button>
			</div>
		);
	}
);

export default PagesTab;
