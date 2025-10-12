export const DEFAULT_CONDITION = Object.freeze( {
	type: 'include',
	scope: 'entire_site',
	subScope: '',
	additional: '',
	additionalLabel: '',
} );

const createKey = ( prefix = 'row' ) =>
	`${ prefix }-${ Date.now() }-${ Math.random()
		.toString( 36 )
		.slice( 2, 8 ) }`;

const ensureKey = ( condition, index = 0 ) => {
	if ( condition && condition._key ) {
		return condition._key;
	}

	return createKey( `row-${ index }` );
};

export const stripConditionExtras = ( condition = {} ) => {
	const { _key, ...rest } = condition;
	return rest;
};

export const stripConditions = ( conditions = [] ) =>
	conditions.map( stripConditionExtras );

export const hydrateConditions = ( conditions = [] ) => {
	const source = Array.isArray( conditions ) ? conditions : [];

	if ( source.length === 0 ) {
		return [
			{
				...DEFAULT_CONDITION,
				_key: createKey(),
			},
		];
	}

	return source.map( ( condition, index ) => ( {
		...DEFAULT_CONDITION,
		...condition,
		_key: ensureKey( condition, index ),
	} ) );
};

export const createEmptyCondition = () => ( {
	...DEFAULT_CONDITION,
	_key: createKey(),
} );
