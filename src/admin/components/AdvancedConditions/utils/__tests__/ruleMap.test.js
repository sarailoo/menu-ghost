import { hydrateRuleMap, serializeRules } from '../ruleMap';

describe( 'ruleMap utilities', () => {
	const definitionMap = new Map(
		[
			[
				'user_role',
				{ defaults: { roles: [], operator: 'any' } },
			],
			[
				'login_status',
				{ defaults: { state: 'any' } },
			],
		]
	);

	it( 'hydrates rule map with defaults and overrides from saved rules', () => {
		const hydrated = hydrateRuleMap(
			[
				{
					key: 'user_role',
					enabled: '1',
					params: { roles: [ 'customer' ] },
				},
				{
					key: 'unknown_rule',
					enabled: true,
				},
			],
			definitionMap
		);

		expect( Object.keys( hydrated ) ).toEqual( [ 'user_role', 'login_status' ] );
		expect( hydrated.user_role.enabled ).toBe( true );
		expect( hydrated.user_role.params ).toEqual( { roles: [ 'customer' ], operator: 'any' } );
		expect( hydrated.login_status.params ).toEqual( { state: 'any' } );
	} );

	it( 'serializes maps back into API payloads', () => {
		const map = hydrateRuleMap( [], definitionMap );
		map.user_role.enabled = true;
		map.user_role.params.roles = [ 'administrator' ];

		const payload = serializeRules( map );

		expect( payload ).toContainEqual( {
			key: 'user_role',
			enabled: true,
			params: { roles: [ 'administrator' ], operator: 'any' },
		} );
	} );
} );
