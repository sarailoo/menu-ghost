import { useCallback, useEffect, useState } from '@wordpress/element';
import DisplayConditionsButton from './components/common/DisplayConditionsButton';
import ConditionsModal from './components/Modal/ConditionsModal';

const App = ( {
	itemId,
	menuTitle,
	pageConditions,
	advancedMeta,
	initialPages,
	initialAdvanced,
	ajaxUrl,
	nonce,
} ) => {
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	const [ savedPages, setSavedPages ] = useState( initialPages );
	const [ savedAdvanced, setSavedAdvanced ] = useState( initialAdvanced );
	useEffect( () => setSavedPages( initialPages ), [ initialPages ] );
	useEffect( () => setSavedAdvanced( initialAdvanced ), [ initialAdvanced ] );

	const handleSave = useCallback(
		async ( { pages, advanced } ) => {
			if ( ! ajaxUrl ) {
				setSavedPages( pages );
				setSavedAdvanced( advanced );
				setIsModalOpen( false );
				return { ok: true };
			}

			const payload = new URLSearchParams();
			payload.append( 'action', 'mghost_save_menu_settings' );
			if ( nonce ) {
				payload.append( 'nonce', nonce );
			}
			payload.append( 'itemId', String( itemId ) );
			payload.append( 'pages', JSON.stringify( pages ) );
			payload.append( 'advanced', JSON.stringify( advanced ) );

			try {
				const response = await fetch( ajaxUrl, {
					method: 'POST',
					headers: {
						'Content-Type':
							'application/x-www-form-urlencoded; charset=utf-8',
					},
					body: payload.toString(),
				} );

				if ( ! response.ok ) {
					throw new Error(
						`Request failed with status ${ response.status }`
					);
				}
				const result = await response.json();
				if ( ! result?.success ) {
					throw new Error( result?.data?.message || 'Unknown error' );
				}

				setSavedPages( pages );
				setSavedAdvanced( advanced );
				setIsModalOpen( false );
				return { ok: true };
			} catch ( error ) {
				return { ok: false, error };
			}
		},
		[ ajaxUrl, itemId, nonce ]
	);

	return (
		<>
			<DisplayConditionsButton onClick={ () => setIsModalOpen( true ) } />
			{ isModalOpen && (
				<ConditionsModal
					menuTitle={ menuTitle }
					pageConditions={ pageConditions }
					advancedMeta={ advancedMeta }
					initialPages={ savedPages }
					initialAdvanced={ savedAdvanced }
					onRequestClose={ () => setIsModalOpen( false ) }
					onSave={ handleSave }
				/>
			) }
		</>
	);
};

export default App;
