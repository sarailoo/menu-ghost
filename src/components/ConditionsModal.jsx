import React, { useEffect } from 'react';
import ReactDOM from 'react-dom';
import ModalContent from './ModalContent'; // Import the modal content component

const ConditionsModal = (props) => {
    useEffect(() => {
        // Disable scrolling when the modal is open
        document.body.style.overflow = 'hidden';

        // Re-enable scrolling when the modal is closed
        return () => {
            document.body.style.overflow = '';
        };
    }, []);

    // Render the modal content inside #wpwrap using React portal
    return ReactDOM.createPortal(<ModalContent pageConditions={props.pageConditions} menuTitle={props.menuTitle} onClose={props.onClose} />, document.body);
};

export default ConditionsModal;
