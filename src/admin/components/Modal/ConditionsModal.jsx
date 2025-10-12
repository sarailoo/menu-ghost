import { useCallback, useMemo, useState } from '@wordpress/element';
import { Modal } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import PagesTab from '../Pages/PagesTab';
import AdvancedConditions from '../AdvancedConditions/AdvancedConditions';
import useSyncedState from '../../hooks/useSyncedState';
import { stripConditions } from '../../utils/conditions';
import ConditionsTabs from './ConditionsTabs';
import ConditionsFooter from './ConditionsFooter';
import ConditionsErrorNotice from './ConditionsErrorNotice';
import './ConditionsModal.scss';

const TAB_CONFIG = {
	pages: {
		name: 'pages',
		title: __( 'Pages', 'menu-control' ),
	},
	advanced: {
		name: 'advanced',
		title: __( 'Advanced Rules', 'menu-control' ),
	},
};

const ConditionsModal = ( {
	menuTitle,
	pageConditions,
	advancedMeta,
	initialPages,
	initialAdvanced,
	onRequestClose,
	onSave,
} ) => {
	const [ pagesDraft, setPagesDraft ] = useSyncedState( initialPages );
	const [ advancedDraft, setAdvancedDraft ] =
		useSyncedState( initialAdvanced );
	const [ saving, setSaving ] = useState( false );
	const [ error, setError ] = useState( null );

	const modalTitle = useMemo( () => {
		if ( menuTitle ) {
			return sprintf(
				/* translators: %s: menu item title. */
				__( '%s - Display Conditions', 'menu-control' ),
				menuTitle
			);
		}

		return __( 'Display Conditions', 'menu-control' );
	}, [ menuTitle ] );

	const handleSave = useCallback( async () => {
		const payload = {
			pages: stripConditions( pagesDraft ) ?? [],
			advanced: advancedDraft ?? [],
		};

		setSaving( true );
		setError( null );

		const result = ( await onSave( payload ) ) || {};
		setSaving( false );

		if ( result.ok ) {
			return;
		}

		setError(
			result.error?.message ||
				__(
					'Unable to save changes. Please try again.',
					'menu-control'
				)
		);
	}, [ advancedDraft, onSave, pagesDraft ] );

	const tabs = useMemo(
		() => [
			{
				...TAB_CONFIG.pages,
				render: () => (
					<PagesTab
						initialConditions={ initialPages }
						value={ pagesDraft }
						pageConditions={ pageConditions }
						onConditionsChange={ setPagesDraft }
					/>
				),
			},
			{
				...TAB_CONFIG.advanced,
				render: () => (
					<AdvancedConditions
						initialRules={ initialAdvanced }
						rules={ advancedDraft }
						meta={ advancedMeta }
						onRulesChange={ setAdvancedDraft }
					/>
				),
			},
		],
		[
			advancedDraft,
			advancedMeta,
			initialAdvanced,
			initialPages,
			pageConditions,
			pagesDraft,
			setAdvancedDraft,
			setPagesDraft,
		]
	);

	return (
		<Modal
			title={ modalTitle }
			className="wpmc-conditions-modal"
			onRequestClose={ saving ? undefined : onRequestClose }
			shouldCloseOnClickOutside={ ! saving }
			shouldCloseOnEsc={ ! saving }
		>
			<div className="wpmc-conditions-modal__inner">
				<ConditionsTabs tabs={ tabs } />

				<ConditionsErrorNotice
					message={ error }
					onRemove={ () => setError( null ) }
				/>

				<ConditionsFooter
					onCancel={ onRequestClose }
					onSave={ handleSave }
					saving={ saving }
				/>
			</div>
		</Modal>
	);
};

export default ConditionsModal;
