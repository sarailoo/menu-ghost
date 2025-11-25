import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import {
	InspectorControls,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { useState, useEffect, useCallback, useRef } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import ConditionsModal from '../components/Modal/ConditionsModal';
import { store as editorStore } from '@wordpress/editor';
const hashValue = ( value = '' ) => {
	try {
		return btoa( unescape( encodeURIComponent( value ) ) )
			.replace( /\+/g, '-' )
			.replace( /\//g, '_' )
			.replace( /=+$/g, '' );
	} catch ( e ) {
		return `h${ value.length }`;
	}
};

const keyFromAttributes = ( attrs = {}, fallback = '' ) => {
	if ( attrs.id ) {
		return `id:${ attrs.id }`;
	}
	if ( attrs.ref ) {
		return `ref:${ attrs.ref }`;
	}
	if ( attrs.url ) {
		return `url:${ hashValue( attrs.url ) }`;
	}
	if ( attrs.label || attrs.title ) {
		return `label:${ hashValue( attrs.label || attrs.title ) }`;
	}
	return `client:${ hashValue( fallback || JSON.stringify( attrs ) ) }`;
};

window.mnghNavigation = {
	...( window.mnghNavigation || {} ),
	keyFromAttributes,
};

const withNavigationPanel = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		const { name, clientId, attributes, context = {} } = props;

		const { navigationId } = useSelect(
			( select ) => {
				const editorSelect = select( blockEditorStore );
				const postId =
					select( editorStore )?.getCurrentPostId?.() || null;
				const parents = editorSelect?.getBlockParents( clientId ) || [];
				const navClient = parents.find( ( parentClientId ) => {
					const parent = editorSelect.getBlock( parentClientId );
					return parent?.name === 'core/navigation';
				} );
				const navBlock = navClient
					? editorSelect.getBlock( navClient )
					: null;
				const navId =
					navBlock?.attributes?.ref ??
					navBlock?.context?.navigationId ??
					context?.navigationId ??
					postId ??
					null;
				return { navigationId: navId };
			},
			[ clientId, context?.navigationId ]
		);

		const { saveEntityRecord } = useDispatch( 'core' );
		const [ isOpen, setIsOpen ] = useState( false );
		const [ loading, setLoading ] = useState( false );
		const [ error, setError ] = useState( null );
		const [ pages, setPages ] = useState( [] );
		const [ advanced, setAdvanced ] = useState( [] );
		const mounted = useRef( true );

		const linkKey = keyFromAttributes( attributes, clientId );

		const fetchSettings = useCallback( async () => {
			if ( ! navigationId || ! linkKey ) {
				return;
			}
			setLoading( true );
			setError( null );
			try {
				const data = await apiFetch( {
					path: `/menu-ghost/v1/navigation/${ navigationId }/${ linkKey }/settings`,
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
							__(
								'Unable to save changes. Please try again.',
								'menu-ghost'
							)
						),
					};
				}
				setLoading( true );
				setError( null );
				try {
					await apiFetch( {
						path: `/menu-ghost/v1/navigation/${ navigationId }/${ linkKey }/settings`,
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

		const isNavigationItem =
			name === 'core/navigation-link' ||
			name === 'core/navigation-submenu';

		if ( ! isNavigationItem || ! navigationId ) {
			return <BlockEdit { ...props } />;
		}

		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'Display Conditions', 'menu-ghost' ) }
					>
						<Button
							variant="secondary"
							onClick={ () => setIsOpen( true ) }
							isBusy={ loading }
							disabled={ loading }
						>
							{ __( 'Edit Conditions', 'menu-ghost' ) }
						</Button>
						{ error && (
							<p className="mngh-navigation-panel__error">
								{ error }
							</p>
						) }
					</PanelBody>
				</InspectorControls>
				{ isOpen && (
					<ConditionsModal
						menuTitle={
							attributes?.label || attributes?.title || ''
						}
						pageConditions={
							window.mnghMenuGhost?.page_conditions || {}
						}
						advancedMeta={
							window.mnghMenuGhost?.advanced_meta || {}
						}
						initialPages={ pages }
						initialAdvanced={ advanced }
						onRequestClose={ () => setIsOpen( false ) }
						onSave={ async ( data ) => {
							const res = await handleSave( {
								pages: data?.pages || [],
								advanced: data?.advanced || [],
							} );
							if ( res.ok ) {
								setIsOpen( false );
							}
							return res;
						} }
					/>
				) }
			</>
		);
	},
	'withNavigationPanel'
);

addFilter( 'editor.BlockEdit', 'mngh/navigation-panel', withNavigationPanel );
