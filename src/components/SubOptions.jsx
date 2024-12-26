import React from 'react';

const SubOptions = ({ subScope, options, additionalData, updateCondition }) => {
    return (
        <div className="sub-options">
            {/* Sub-Scope Dropdown */}
            <select
                value={subScope || ''}
                onChange={(e) => updateCondition('subScope', e.target.value)}
                className="sub-scope-select"
            >
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>

            {/* Additional Data Dropdown */}
            {additionalData.list.length > 0 && (
                <select
                    value={additionalData.selected || 'All'}
                    onChange={(e) => updateCondition('additional', e.target.value)}
                    className="additional-select"
                >
                    <option value="All">All</option>
                    {additionalData.list.map((item) => (
                        <option key={item.value} value={item.value}>
                            {item.label}
                        </option>
                    ))}
                </select>
            )}
        </div>
    );
};

export default SubOptions;