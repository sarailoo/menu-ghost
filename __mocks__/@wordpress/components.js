const React = require( 'react' );

const Button = ( { label, children, ...props } ) =>
	React.createElement(
		'button',
		{ ...props, 'aria-label': props[ 'aria-label' ] || label },
		children
	);

const Card = ( props ) =>
	React.createElement(
		'div',
		{ ...props, className: props.className || '' },
		props.children
	);

const SelectControl = ( { value, options = [], onChange = () => {} } ) =>
	React.createElement(
		'select',
		{
			value,
			onChange: ( event ) => onChange( event.target.value ),
		},
		options.map( ( option ) =>
			React.createElement(
				'option',
				{ key: option.value, value: option.value },
				option.label
			)
		)
	);

const TabPanel = ( { tabs, children } ) => {
	const [ active, setActive ] = React.useState( tabs[ 0 ] );

	return React.createElement(
		'div',
		{ className: 'mock-tab-panel' },
		React.createElement(
			'div',
			{ className: 'mock-tab-buttons' },
			tabs.map( ( tab ) =>
				React.createElement(
					'button',
					{
						key: tab.name,
						onClick: () => setActive( tab ),
						'aria-current': active.name === tab.name,
					},
					tab.title
				)
			)
		),
		React.createElement(
			'div',
			{ className: 'mock-tab-content' },
			children( active )
		)
	);
};

module.exports = {
	Button,
	Card,
	SelectControl,
	TabPanel,
};
