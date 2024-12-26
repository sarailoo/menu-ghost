import React from 'react';
import SubCondition from './SubCondition';

const ConditionRow = ({ index, condition, updateCondition, removeCondition, pageConditions }) => {
    return (
        <div className="condition-row">
            {/* Condition Type */}
            <select
                value={condition.type}
                onChange={(e) => updateCondition(index, 'type', e.target.value)}
                className="condition-type-select"
            >
                {pageConditions.conditionTypes.map((type) => (
                    <option key={type.value} value={type.value}>
                        {type.label}
                    </option>
                ))}
            </select>

            {/* Scope */}
            <select
                value={condition.scope}
                onChange={(e) => updateCondition(index, 'scope', e.target.value)}
                className="condition-scope-select"
            >
                {pageConditions.scopes.map((scope) => (
                    <option key={scope.value} value={scope.value}>
                        {scope.label}
                    </option>
                ))}
            </select>

            {/* Sub-Condition Options */}
            <SubCondition
                condition={condition}
                index={index}
                updateCondition={updateCondition}
                pageConditions={pageConditions}
            />

            {/* Remove Button */}
            <button
                onClick={() => removeCondition(index)}
                className="condition-delete-button"
            >
                X
            </button>
        </div>
    );
};

export default ConditionRow;