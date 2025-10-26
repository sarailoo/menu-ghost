import { useEffect, useState } from '@wordpress/element';

const useSyncedState = ( value ) => {
	const [ state, setState ] = useState( value );

	useEffect( () => {
		setState( value );
	}, [ value ] );

	return [ state, setState ];
};

export default useSyncedState;
