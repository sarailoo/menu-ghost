import {
	Button,
	Card,
	CardBody,
	CardHeader,
	Popover,
	ToggleControl,
} from '@wordpress/components';
import {
	forwardRef,
	useEffect,
	useCallback,
	useImperativeHandle,
	useMemo,
	useRef,
	useState,
} from '@wordpress/element';
import RuleIcon from './components/RuleIcon';
import { GROUPS, buildDefinitions } from './definitions';
import { hydrateRuleMap, serializeRules } from './utils/ruleMap';
import './AdvancedConditions.scss';

const AdvancedConditions = forwardRef(
	( { initialRules, rules, onRulesChange = () => {}, meta = {} }, ref ) => {
		const definitions = useMemo( () => buildDefinitions( meta ), [ meta ] );
		const definitionMap = useMemo( () => {
			const map = new Map();
			definitions.forEach( ( definition ) => {
				map.set( definition.key, definition );
			} );
			return map;
		}, [ definitions ] );
		const effectiveRules = rules ?? initialRules;
		const [ ruleMap, setRuleMap ] = useState( () =>
			hydrateRuleMap( effectiveRules, definitionMap )
		);
		const hydratingRef = useRef( false );
		const [ infoPopover, setInfoPopover ] = useState( {
			key: null,
			anchor: null,
		} );
		const closeTimeout = useRef( null );
		const [ delayedDisabled, setDelayedDisabled ] = useState( {} );
		const disableTimers = useRef( {} );
		const groupedDefinitions = useMemo(
			() =>
				GROUPS.map( ( group ) => ( {
					...group,
					definitions: definitions.filter(
						( definition ) => definition.group === group.key
					),
				} ) ).filter( ( group ) => group.definitions.length > 0 ),
			[ definitions ]
		);
		const [ expandedGroup, setExpandedGroup ] = useState(
			() => groupedDefinitions[ 0 ]?.key ?? null
		);

		useEffect( () => {
			hydratingRef.current = true;
			Object.keys( disableTimers.current ).forEach( ( key ) => {
				clearTimeout( disableTimers.current[ key ] );
				delete disableTimers.current[ key ];
			} );
			setDelayedDisabled( {} );
			setRuleMap( hydrateRuleMap( effectiveRules, definitionMap ) );
		}, [ effectiveRules, definitionMap ] );

		useEffect( () => {
			if ( ! groupedDefinitions.length ) {
				setExpandedGroup( null );
				return;
			}

			if (
				! expandedGroup ||
				! groupedDefinitions.some(
					( group ) => group.key === expandedGroup
				)
			) {
				setExpandedGroup( groupedDefinitions[ 0 ].key );
			}
		}, [ groupedDefinitions, expandedGroup ] );

		const scheduleDisable = useCallback( ( key ) => {
			if ( disableTimers.current[ key ] ) {
				clearTimeout( disableTimers.current[ key ] );
			}
			setDelayedDisabled( ( current ) => ( {
				...current,
				[ key ]: false,
			} ) );
			disableTimers.current[ key ] = setTimeout( () => {
				setDelayedDisabled( ( current ) => ( {
					...current,
					[ key ]: true,
				} ) );
				clearTimeout( disableTimers.current[ key ] );
				delete disableTimers.current[ key ];
			}, 250 );
		}, [] );

		const cancelDisable = useCallback( ( key ) => {
			if ( disableTimers.current[ key ] ) {
				clearTimeout( disableTimers.current[ key ] );
				delete disableTimers.current[ key ];
			}
			setDelayedDisabled( ( current ) => {
				if ( current[ key ] === undefined ) {
					return current;
				}
				const next = { ...current };
				delete next[ key ];
				return next;
			} );
		}, [] );

		const closeInfoPopover = useCallback( () => {
			if ( closeTimeout.current ) {
				clearTimeout( closeTimeout.current );
				closeTimeout.current = null;
			}
			setInfoPopover( { key: null, anchor: null } );
		}, [] );

		const handleGroupToggle = useCallback(
			( key ) => {
				closeInfoPopover();
				setExpandedGroup( ( current ) =>
					current === key ? current : key
				);
			},
			[ closeInfoPopover ]
		);

		useEffect( () => {
			closeInfoPopover();
		}, [ expandedGroup, closeInfoPopover ] );

		useEffect(
			() => () => {
				if ( closeTimeout.current ) {
					clearTimeout( closeTimeout.current );
				}
			},
			[]
		);

		useEffect( () => {
			Object.entries( ruleMap ).forEach( ( [ key, state ] ) => {
				if ( state?.enabled ) {
					cancelDisable( key );
				} else {
					scheduleDisable( key );
				}
			} );
		}, [ ruleMap, scheduleDisable, cancelDisable ] );

		useEffect(
			() => () => {
				Object.keys( disableTimers.current ).forEach( ( key ) => {
					clearTimeout( disableTimers.current[ key ] );
					delete disableTimers.current[ key ];
				} );
			},
			[]
		);

		useEffect( () => {
			if ( ! infoPopover.key ) {
				return;
			}

			if (
				! definitions.some(
					( definition ) => definition.key === infoPopover.key
				)
			) {
				closeInfoPopover();
			}
		}, [ infoPopover.key, definitions, closeInfoPopover ] );

		useImperativeHandle(
			ref,
			() => ( {
				getValue: () => serializeRules( ruleMap ),
			} ),
			[ ruleMap ]
		);

		const updateRuleMap = useCallback(
			( updater ) => {
				setRuleMap( ( current ) => updater( current ) );
			},
			[]
		);
		useEffect( () => {
			if ( hydratingRef.current ) {
				hydratingRef.current = false;
				return;
			}
			onRulesChange( serializeRules( ruleMap ) );
		}, [ ruleMap, onRulesChange ] );

		const toggleRule = useCallback(
			( key, enabled ) => {
				if ( enabled ) {
					cancelDisable( key );
				} else {
					scheduleDisable( key );
				}
				updateRuleMap( ( current ) => ( {
					...current,
					[ key ]: {
						...current[ key ],
						enabled,
					},
				} ) );
			},
			[ cancelDisable, scheduleDisable, updateRuleMap ]
		);

		const setParams = useCallback(
			( key, patch ) => {
				updateRuleMap( ( current ) => ( {
					...current,
					[ key ]: {
						...current[ key ],
						params: {
							...current[ key ].params,
							...patch,
						},
					},
				} ) );
			},
			[ updateRuleMap ]
		);

		const handleInfoEnter = useCallback(
			( key ) => ( event ) => {
				if ( closeTimeout.current ) {
					clearTimeout( closeTimeout.current );
					closeTimeout.current = null;
				}
				const anchor = event.currentTarget;
				setInfoPopover( ( current ) => {
					if ( current.key === key && current.anchor === anchor ) {
						return current;
					}
					return { key, anchor };
				} );
			},
			[]
		);

		const handleInfoLeave = useCallback( () => {
			if ( closeTimeout.current ) {
				clearTimeout( closeTimeout.current );
			}
			closeTimeout.current = setTimeout( () => {
				setInfoPopover( { key: null, anchor: null } );
				closeTimeout.current = null;
			}, 140 );
		}, [] );

		return (
			<div className="wpmc-advanced">
				<div className="wpmc-advanced__accordions">
					{ groupedDefinitions.map( ( group ) => {
						const isOpen = expandedGroup
							? expandedGroup === group.key
							: false;
						const infoKey = `group:${ group.key }`;
						const panelId = `wpmc-advanced-panel-${ group.key }`;

						return (
							<section
								key={ group.key }
								className={ `wpmc-advanced__accordion${
									isOpen ? ' is-open' : ''
								}` }
							>
								<button
									type="button"
									className="wpmc-advanced__accordion-toggle"
									onClick={ () =>
										handleGroupToggle( group.key )
									}
									aria-expanded={ isOpen }
									aria-controls={ panelId }
								>
									<span className="wpmc-advanced__accordion-label">
										<span className="wpmc-advanced__accordion-title">
											{ group.title }
										</span>
									</span>
									<span
										className="wpmc-advanced__accordion-icon"
										aria-hidden="true"
									/>
								</button>
								{ infoPopover.key === infoKey &&
									infoPopover.anchor && (
										<Popover
											anchor={ infoPopover.anchor }
											onClose={ closeInfoPopover }
											className="wpmc-advanced__info-popover"
										>
											<p>{ group.description }</p>
										</Popover>
									) }
								<div
									id={ panelId }
									className="wpmc-advanced__accordion-panel"
									aria-hidden={ ! isOpen }
								>
									<div className="wpmc-advanced__panel-inner">
										{ group.definitions.map(
											( definition ) => {
												const ruleState = ruleMap[
													definition.key
												] || {
													enabled: false,
													params: definition.defaults,
												};
												const isDisabled =
													ruleState.enabled
														? false
														: delayedDisabled[
																definition.key
														  ] === true;

												return (
													<Card
														key={ definition.key }
														className={ `wpmc-advanced__card${
															ruleState.enabled
																? ' is-active'
																: ''
														}` }
													>
														<CardHeader className="wpmc-advanced__card-header">
															<div className="wpmc-advanced__title">
																<span className="wpmc-advanced__rule-icon">
																	<RuleIcon
																		name={
																			definition.icon
																		}
																	/>
																</span>
																<h3>
																	{
																		definition.label
																	}
																</h3>
																{ definition.description && (
																	<Button
																		className="wpmc-advanced__info-button"
																		icon="editor-help"
																		onMouseEnter={ handleInfoEnter(
																			definition.key
																		) }
																		onMouseLeave={
																			handleInfoLeave
																		}
																		onFocus={ handleInfoEnter(
																			definition.key
																		) }
																		onBlur={
																			handleInfoLeave
																		}
																		variant="tertiary"
																	/>
																) }
																{ infoPopover.key ===
																	definition.key &&
																	infoPopover.anchor && (
																		<Popover
																			anchor={
																				infoPopover.anchor
																			}
																			onClose={
																				closeInfoPopover
																			}
																			className="wpmc-advanced__info-popover"
																		>
																			<p>
																				{
																					definition.description
																				}
																			</p>
																		</Popover>
																	) }
															</div>
															<ToggleControl
																className="wpmc-advanced__toggle"
																checked={
																	!! ruleState.enabled
																}
																onChange={ (
																	value
																) =>
																	toggleRule(
																		definition.key,
																		value
																	)
																}
															/>
														</CardHeader>
														<CardBody
															className={ `wpmc-advanced__card-body${
																ruleState.enabled
																	? ' is-open'
																	: ' is-collapsed'
															}` }
															aria-hidden={
																! ruleState.enabled
															}
														>
															{ definition.editor(
																{
																	params: ruleState.params,
																	setParams: (
																		patch
																	) =>
																		setParams(
																			definition.key,
																			patch
																		),
																	disabled:
																		isDisabled,
																}
															) }
														</CardBody>
													</Card>
												);
											}
										) }
									</div>
								</div>
							</section>
						);
					} ) }
				</div>
			</div>
		);
	}
);

export default AdvancedConditions;
