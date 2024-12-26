import { createRoot, render, StrictMode } from '@wordpress/element';
import { useState } from 'react';
import DisplayConditionsButton from '../components/DisplayConditionsButton';
import ConditionsModal from '../components/ConditionsModal';

const WPMenuConditions = (props) => {
    const [isModalOpen, setIsModalOpen] = useState(false);

    const handleOpenModal = () => {
        setIsModalOpen(true);
    };

    const handleCloseModal = () => {
        setIsModalOpen(false);
    };

    return (
        <>
            <DisplayConditionsButton onClick={handleOpenModal} />
            {isModalOpen && <ConditionsModal pageConditions={props.pageConditions} menuTitle={props.menuTitle} onClose={handleCloseModal} />}
        </>
    );
};

const pageConditions = window.wp_menu_control.page_conditions;

window.wp_menu_control.menu_items.forEach(({ id, title }) => {
    const domElementId = `wp-menu-control-${id}`;
    const domElement = document.getElementById(domElementId);

    if (domElement) {
        if (createRoot) {
            createRoot(domElement).render(
                <StrictMode>
                    <WPMenuConditions pageConditions={pageConditions} menuTitle={title} />
                </StrictMode>
            );
        } else {
            render(
                <StrictMode>
                    <WPMenuConditions pageConditions={pageConditions} menuTitle={title} />
                </StrictMode>,
                domElement
            );
        }
    }
});
