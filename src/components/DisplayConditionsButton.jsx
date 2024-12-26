import { __ } from '@wordpress/i18n';

const DisplayConditionsButton = (props) => {
    return (
        <>
            <span className="button" onClick={props.onClick}>
                { __( 'Display Condition', 'wp-menu-control') }
            </span>
        </>
    );
};

export default DisplayConditionsButton;
