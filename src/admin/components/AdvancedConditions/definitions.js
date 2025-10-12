import {
	ComboboxControl,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import CheckboxGrid from './components/CheckboxGrid';
import DateField from './components/DateField';
import TimeField from './components/TimeField';

export const UTM_KEYS = [
	'utm_campaign',
	'utm_content',
	'utm_medium',
	'utm_source',
	'utm_term',
];

export const GROUPS = [
	{
		key: 'audience',
		title: __( 'User', 'menu-control' ),
		description: __(
			'Control menu visibility based on who is visiting.',
			'menu-control'
		),
	},
	{
		key: 'schedule',
		title: __( 'Date & Time', 'menu-control' ),
		description: __(
			'Schedule when links should appear.',
			'menu-control'
		),
	},
	{
		key: 'campaign',
		title: __( 'URL & Campaign', 'menu-control' ),
		description: __(
			'Leverage query strings and campaign parameters.',
			'menu-control'
		),
	},
];

export const buildDefinitions = ( meta ) => [
	{
		key: 'user_role',
		group: 'audience',
		label: __( 'User Role', 'menu-control' ),
		description: __(
			'Only show for selected WordPress roles.',
			'menu-control'
		),
		icon: 'user_role',
		defaults: { roles: [] },
		editor: ( { params, setParams, disabled } ) => (
			<CheckboxGrid
				options={ meta.roles || [] }
				value={ new Set( params.roles || [] ) }
				onChange={ ( values ) =>
					setParams( { roles: Array.from( values ) } )
				}
				disabled={ disabled }
			/>
		),
	},
	{
		key: 'device',
		group: 'audience',
		label: __( 'User Device', 'menu-control' ),
		description: __(
			'Restrict to visitors on mobile, tablet, or desktop.',
			'menu-control'
		),
		icon: 'device',
		defaults: { devices: [] },
		editor: ( { params, setParams, disabled } ) => (
			<CheckboxGrid
				options={ meta.devices || [] }
				value={ new Set( params.devices || [] ) }
				onChange={ ( values ) =>
					setParams( { devices: Array.from( values ) } )
				}
				disabled={ disabled }
			/>
		),
	},
	{
		key: 'login_status',
		group: 'audience',
		label: __( 'Login Status', 'menu-control' ),
		description: __(
			'Target visitors who are logged in or logged out.',
			'menu-control'
		),
		icon: 'login_status',
		defaults: { state: 'any' },
		editor: ( { params, setParams, disabled } ) => (
			<SelectControl
				value={ params.state || 'any' }
				options={ [
					{ value: 'any', label: __( 'Any', 'menu-control' ) },
					{
						value: 'logged_in',
						label: __( 'Logged in', 'menu-control' ),
					},
					{
						value: 'logged_out',
						label: __( 'Logged out', 'menu-control' ),
					},
				] }
				onChange={ ( value ) => setParams( { state: value } ) }
				disabled={ disabled }
			/>
		),
	},
	{
		key: 'signup_date',
		group: 'audience',
		label: __( 'Signup Date', 'menu-control' ),
		description: __(
			'Compare against the user registration date.',
			'menu-control'
		),
		icon: 'signup_date',
		defaults: { operator: 'after', date: '' },
		editor: ( { params, setParams, disabled } ) => (
			<div className="wpmc-advanced__input-row">
				<SelectControl
					value={ params.operator || 'after' }
					options={ [
						{
							value: 'after',
							label: __( 'After', 'menu-control' ),
						},
						{
							value: 'before',
							label: __( 'Before', 'menu-control' ),
						},
					] }
					onChange={ ( value ) => setParams( { operator: value } ) }
					disabled={ disabled }
				/>
				<DateField
					id="signup-date"
					value={ params.date || '' }
					onChange={ ( value ) => setParams( { date: value } ) }
					disabled={ disabled }
				/>
			</div>
		),
	},
	{
		key: 'browser_language',
		group: 'audience',
		label: __( 'Browser Language', 'menu-control' ),
		description: __(
			'Match the visitor language preference sent by the browser.',
			'menu-control'
		),
		icon: 'browser_language',
		defaults: { langs: [] },
		editor: ( { params, setParams, disabled } ) => (
			<ComboboxControl
				value={ params.langs?.[ 0 ] || '' }
				options={ ( meta.languages || [] ).map(
					( { value, label } ) => ( {
						value,
						label,
					} )
				) }
				onChange={ ( value ) =>
					setParams( { langs: value ? [ value ] : [] } )
				}
				allowReset
				disabled={ disabled }
			/>
		),
	},
	{
		key: 'days_of_week',
		group: 'schedule',
		label: __( 'Days of the Week', 'menu-control' ),
		description: __(
			'Display only on the selected weekdays.',
			'menu-control'
		),
		icon: 'days_of_week',
		defaults: { days: [] },
		editor: ( { params, setParams, disabled } ) => (
			<CheckboxGrid
				options={ meta.weekdays || [] }
				value={ new Set( params.days || [] ) }
				onChange={ ( values ) =>
					setParams( { days: Array.from( values ) } )
				}
				disabled={ disabled }
			/>
		),
	},
	{
		key: 'within_date_range',
		group: 'schedule',
		label: __( 'Within Date Range', 'menu-control' ),
		description: __(
			'Activate only between two calendar dates.',
			'menu-control'
		),
		icon: 'within_date_range',
		defaults: { start: '', end: '' },
		editor: ( { params, setParams, disabled } ) => (
			<div className="wpmc-advanced__input-row">
				<DateField
					id="within-date-start"
					label={ __( 'Start date', 'menu-control' ) }
					value={ params.start || '' }
					onChange={ ( value ) => setParams( { start: value } ) }
					disabled={ disabled }
				/>
				<DateField
					id="within-date-end"
					label={ __( 'End date', 'menu-control' ) }
					value={ params.end || '' }
					onChange={ ( value ) => setParams( { end: value } ) }
					disabled={ disabled }
				/>
			</div>
		),
	},
	{
		key: 'within_time',
		group: 'schedule',
		label: __( 'Within Time Window', 'menu-control' ),
		description: __(
			'Activate during the selected time of day.',
			'menu-control'
		),
		icon: 'within_time',
		defaults: { start: '', end: '' },
		editor: ( { params, setParams, disabled } ) => (
			<div className="wpmc-advanced__input-row">
				<TimeField
					id="within-time-start"
					label={ __( 'Start time', 'menu-control' ) }
					value={ params.start || '' }
					onChange={ ( value ) => setParams( { start: value } ) }
					disabled={ disabled }
				/>
				<TimeField
					id="within-time-end"
					label={ __( 'End time', 'menu-control' ) }
					value={ params.end || '' }
					onChange={ ( value ) => setParams( { end: value } ) }
					disabled={ disabled }
				/>
			</div>
		),
	},
	{
		key: 'url_query_key',
		group: 'campaign',
		label: __( 'URL Query Parameter', 'menu-control' ),
		description: __(
			'Check for a specific query-string key/value.',
			'menu-control'
		),
		icon: 'url_query_key',
		defaults: { mode: 'exists', key: '', value: '' },
		editor: ( { params, setParams, disabled } ) => (
			<div className="wpmc-advanced__input-row">
				<SelectControl
					label={ __( 'Mode', 'menu-control' ) }
					value={ params.mode || 'exists' }
					options={ [
						{
							value: 'exists',
							label: __( 'Exists', 'menu-control' ),
						},
						{
							value: 'equals',
							label: __( 'Equals', 'menu-control' ),
						},
					] }
					onChange={ ( value ) => setParams( { mode: value } ) }
					disabled={ disabled }
				/>
				<TextControl
					label={ __( 'Key', 'menu-control' ) }
					value={ params.key || '' }
					onChange={ ( value ) => setParams( { key: value } ) }
					disabled={ disabled }
				/>
				<TextControl
					label={ __( 'Value', 'menu-control' ) }
					value={ params.value || '' }
					onChange={ ( value ) => setParams( { value } ) }
					disabled={ disabled }
				/>
			</div>
		),
	},
	...UTM_KEYS.map( ( key ) => ( {
		key,
		group: 'campaign',
		label: key
			.replace( 'utm_', 'UTM ' )
			.replace( /\b\w/g, ( char ) => char.toUpperCase() ),
		description: __(
			'Require a matching campaign parameter.',
			'menu-control'
		),
		icon: key,
		defaults: { mode: 'exists', value: '' },
		editor: ( { params, setParams, disabled } ) => (
			<div className="wpmc-advanced__input-row">
				<SelectControl
					label={ __( 'Mode', 'menu-control' ) }
					value={ params.mode || 'exists' }
					options={ [
						{
							value: 'exists',
							label: __( 'Exists', 'menu-control' ),
						},
						{
							value: 'equals',
							label: __( 'Equals', 'menu-control' ),
						},
					] }
					onChange={ ( value ) => setParams( { mode: value } ) }
					disabled={ disabled }
				/>
				<TextControl
					label={ __( 'Value', 'menu-control' ) }
					value={ params.value || '' }
					onChange={ ( value ) => setParams( { value } ) }
					disabled={ disabled }
				/>
			</div>
		),
	} ) ),
];
