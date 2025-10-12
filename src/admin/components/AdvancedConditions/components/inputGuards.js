const nonTypingKeys = new Set( [
	'Tab',
	'Shift',
	'ArrowLeft',
	'ArrowRight',
	'ArrowUp',
	'ArrowDown',
	'Home',
	'End',
] );

export const preventManualEntry = ( event ) => {
	if ( event.ctrlKey || event.metaKey || event.altKey ) {
		return;
	}

	if ( nonTypingKeys.has( event.key ) ) {
		return;
	}

	event.preventDefault();
};

export const preventPaste = ( event ) => {
	event.preventDefault();
};

export const openNativePicker = ( event, disabled ) => {
	if ( disabled ) {
		return;
	}

	if ( typeof event.currentTarget.showPicker === 'function' ) {
		event.currentTarget.showPicker();
	}
};
