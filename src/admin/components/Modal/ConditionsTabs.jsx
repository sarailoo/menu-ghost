import { TabPanel } from '@wordpress/components';

const ConditionsTabs = ( { tabs } ) => {
	const tabLookup = Object.fromEntries(
		tabs.map( ( tab ) => [ tab.name, tab ] )
	);

	return (
		<TabPanel
			className="wpmc-conditions-modal__tabs"
			activeClass="is-active"
			orientation="horizontal"
			tabs={ tabs.map( ( { name, title } ) => ( { name, title } ) ) }
		>
			{ ( tab ) => {
				const active = tabLookup[ tab.name ];

				if ( ! active ) {
					return null;
				}

				return (
					<div className="wpmc-conditions-modal__tab">
						<div className="wpmc-conditions-modal__content">
							{ typeof active.render === 'function'
								? active.render()
								: active.content ?? null }
						</div>
					</div>
				);
			} }
		</TabPanel>
	);
};

export default ConditionsTabs;
