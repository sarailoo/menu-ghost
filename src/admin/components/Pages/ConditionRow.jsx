import { useMemo } from '@wordpress/element';
import { Card, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import './ConditionRow.scss';
import ConditionTypeSelect from './fields/ConditionTypeSelect';
import ScopeSelect from './fields/ScopeSelect';
import SubScopeSelect from './fields/SubScopeSelect';
import AdditionalField from './fields/AdditionalField';

const fallbackTypeOptions = [
	{
		value: 'include',
		label: __( 'Include', 'menu-control' ),
	},
	{
		value: 'exclude',
		label: __( 'Exclude', 'menu-control' ),
	},
];

const fallbackScopeOptions = [
	{
		value: 'entire_site',
		label: __( 'Entire Site', 'menu-control' ),
	},
];

const ConditionRow = ( {
	condition,
	conditionTypes,
	scopes,
	scopeIndex,
	onChange,
	onRemove,
} ) => {
	const typeOptions = conditionTypes?.length
		? conditionTypes
		: fallbackTypeOptions;
	const scopeOptions = scopes?.length ? scopes : fallbackScopeOptions;

	const scopeConfig = useMemo( () => {
		if ( scopeIndex[ condition.scope ] ) {
			return scopeIndex[ condition.scope ];
		}

		const fallbackScope = scopeOptions[ 0 ]?.value;

		return fallbackScope ? scopeIndex[ fallbackScope ] || {} : {};
	}, [ condition.scope, scopeIndex, scopeOptions ] );

	const subScopeOptions = scopeConfig.options || [];
	const additionalConfig =
		scopeConfig.additionalData?.[ condition.subScope ] || {};
	const hasSubScope = subScopeOptions.length > 0;
	const hasAdditionalField = Boolean(
		additionalConfig.async || ( additionalConfig.list?.length ?? 0 )
	);

	return (
		<Card className="wpmc-condition-row">
			<div className="wpmc-condition-row__controls">
				<div className="wpmc-condition-row__field">
					<ConditionTypeSelect
						value={ condition.type }
						options={ typeOptions }
						onChange={ ( value ) => onChange( 'type', value ) }
					/>
				</div>

				<div className="wpmc-condition-row__field">
					<ScopeSelect
						value={ condition.scope }
						options={ scopeOptions }
						onChange={ ( value ) => onChange( 'scope', value ) }
					/>
				</div>

				{ hasSubScope && (
					<div className="wpmc-condition-row__field">
						<SubScopeSelect
							value={ condition.subScope }
							options={ subScopeOptions }
							onChange={ ( value ) =>
								onChange( 'subScope', value )
							}
						/>
					</div>
				) }

				{ hasAdditionalField && (
					<div className="wpmc-condition-row__field">
						<AdditionalField
							value={ condition.additional }
							initialLabel={ condition.additionalLabel }
							config={ additionalConfig }
							onChange={ ( selection ) =>
								onChange( 'additional', selection )
							}
						/>
					</div>
				) }

				<Button
					icon="no-alt"
					label={ __( 'Remove rule', 'menu-control' ) }
					onClick={ onRemove }
					variant="tertiary"
					className="wpmc-condition-row__remove"
				/>
			</div>
		</Card>
	);
};

export default ConditionRow;
