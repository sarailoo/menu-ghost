export const hydrateRuleMap = ( rules = [], definitionMap ) => {
	const baseMap = {};

	definitionMap.forEach( ( definition, key ) => {
		const defaults = definition.defaults || {};
		baseMap[ key ] = {
			key,
			enabled: false,
			params: { ...defaults },
		};
	} );

	rules.forEach( ( rule ) => {
		if ( ! rule?.key || ! definitionMap.has( rule.key ) ) {
			return;
		}

		baseMap[ rule.key ] = {
			...baseMap[ rule.key ],
			...rule,
			enabled: Boolean( rule.enabled ),
			params: {
				...baseMap[ rule.key ].params,
				...( rule.params || {} ),
			},
		};
	} );

	return baseMap;
};

export const serializeRules = ( ruleMap ) =>
	Object.values( ruleMap ).map( ( { key, enabled, params } ) => ( {
		key,
		enabled,
		params,
	} ) );
