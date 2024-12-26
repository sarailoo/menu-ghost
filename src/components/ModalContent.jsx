import React, { useRef, useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import PagesTab from './PagesTab';
import ConditionsTab from './ConditionsTab';
import './styles/ModalContent.scss';

const ModalContent = (props) => {
    const overlayRef = useRef();
    const [activeTab, setActiveTab] = useState('page');

    const handleClickOutside = (event) => {
        if (overlayRef.current && event.target === overlayRef.current) {
            props.onClose();
        }
    };

    useEffect(() => {
        document.addEventListener('click', handleClickOutside);
        return () => {
            document.removeEventListener('click', handleClickOutside);
        };
    }, []);

    return (
        <div className="modal-overlay" onClick={props.onClose}>
            <div className="modal-content" onClick={(e) => e.stopPropagation()}>
                <button className="close-button" onClick={props.onClose}>
                    &times;
                </button>
                <div>{props.menuTitle}</div>
                <div className="tabs">
                    <button
                        className={activeTab === 'page' ? 'active' : ''}
                        onClick={() => setActiveTab('page')}
                    >
                        { __('Pages', 'wp-menu-control') }
                    </button>
                    <button
                        className={activeTab === 'condition' ? 'active' : ''}
                        onClick={() => setActiveTab('condition')}
                    >
                        { __('Conditions', 'wp-menu-control') }
                    </button>
                </div>
                <div className="tab-content">
                    {activeTab === 'page' ? (
                        <PagesTab pageConditions={props.pageConditions} />
                    ) : (
                        <ConditionsTab />
                    )}
                </div>
            </div>
        </div>
    );
};

export default ModalContent;
