import { __ } from '@wordpress/i18n';
import { PanelBody, Button } from '@wordpress/components';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useState, useEffect, useCallback, useRef } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import ConditionsModal from '../components/Modal/ConditionsModal';

const SidebarPanel = ( { block, navigationId } ) => {
	const { saveEntityRecord } = useDispatch( 'core' );
	const [ isOpen, setIsOpen ] = useState( false );
	const [ loading, setLoading ] = useState( false );
	const [ error, setError ] = useState( null );
	const [ pages, setPages ] = useState( [] );
	const [ advanced, setAdvanced ] = useState( [] );
	const mounted = useRef( true );

	const linkKey =
		window.mnghNavigation?.keyFromAttributes?.( block?.attributes ) ||
		`${ block?.clientId || '' }`;

	const fetchSettings = useCallback( async () => {
		if ( ! navigationId || ! linkKey ) {
			return;
		}
		setLoading( true );
		setError( null );
		try {
			const data = await apiFetch( {
				path: `/menu-ghost/v1/navigation/${ navigationId }/${ encodeURIComponent(
					linkKey
				) }/settings`,
			} );
			if ( mounted.current && data ) {
				setPages( data.pages || [] );
				setAdvanced( data.advanced || [] );
			}
		} catch ( e ) {
			if ( mounted.current ) {
				setError(
					e?.message ||
						__( 'Unable to load conditions.', 'menu-ghost' )
				);
			}
		} finally {
			if ( mounted.current ) {
				setLoading( false );
			}
		}
	}, [ navigationId, linkKey ] );

	useEffect( () => {
		mounted.current = true;
		return () => {
			mounted.current = false;
		};
	}, [] );

	useEffect( () => {
		fetchSettings();
	}, [ fetchSettings ] );

	const handleSave = useCallback(
		async ( payload ) => {
			if ( ! navigationId || ! linkKey ) {
				return {
					ok: false,
					error: new Error(
						'Missing navigation or link identifier.'
					),
				};
			}
			setLoading( true );
			setError( null );
			try {
				await apiFetch( {
					path: `/menu-ghost/v1/navigation/${ navigationId }/${ encodeURIComponent(
						linkKey
					) }/settings`,
					method: 'POST',
					body: JSON.stringify( payload ),
				} );
				// Mark navigation entity dirty so user can save.
				await saveEntityRecord(
					'postType',
					'wp_navigation',
					navigationId,
					{}
				);
				if ( mounted.current ) {
					setPages( payload.pages || [] );
					setAdvanced( payload.advanced || [] );
				}
				return { ok: true };
			} catch ( e ) {
				if ( mounted.current ) {
					setError(
						e?.message ||
							__(
								'Unable to save changes. Please try again.',
								'menu-ghost'
							)
					);
				}
				return { ok: false, error: e };
			} finally {
				if ( mounted.current ) {
					setLoading( false );
				}
			}
		},
		[ navigationId, linkKey, saveEntityRecord ]
	);

	const openModal = () => setIsOpen( true );
	const closeModal = () => setIsOpen( false );

	return (
		<PluginDocumentSettingPanel
			name="mngh-navigation-conditions"
			title={ __( 'Display Conditions', 'menu-ghost' ) }
			className="mngh-navigation-panel"
		>
			<Button
				variant="secondary"
				onClick={ openModal }
				isBusy={ loading }
				disabled={ loading }
			>
				{ __( 'Edit Conditions', 'menu-ghost' ) }
			</Button>
			{ isOpen && (
				<ConditionsModal
					menuTitle={
						block?.attributes?.label ||
						block?.attributes?.title ||
						''
					}
					pageConditions={
						window.mnghMenuGhost?.page_conditions || {}
					}
					advancedMeta={ window.mnghMenuGhost?.advanced_meta || {} }
					initialPages={ pages }
					initialAdvanced={ advanced }
					onRequestClose={ closeModal }
					onSave={ async ( data ) => {
						const res = await handleSave( {
							pages: data?.pages || [],
							advanced: data?.advanced || [],
						} );
						if ( res.ok ) {
							closeModal();
						}
						return res;
					} }
				/>
			) }
			{ error && (
				<p className="mngh-navigation-panel__error">{ error }</p>
			) }
		</PluginDocumentSettingPanel>
	);
};

export default SidebarPanel;
