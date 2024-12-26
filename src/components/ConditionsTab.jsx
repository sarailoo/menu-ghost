import React, { useRef, useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import ConditionRow from './ConditionRow'

const ConditionsTab = (props) => {
    const [conditions, setConditions] = useState([
        { type: 'Include', scope: 'Entire Site' },
    ]);

    const addCondition = () => {
        setConditions([...conditions, { type: 'Include', scope: 'Entire Site' }]);
    };

    const removeCondition = (index) => {
        setConditions(conditions.filter((_, i) => i !== index));
    };

    const updateCondition = (index, key, value) => {
        const updatedConditions = [...conditions];
        updatedConditions[index][key] = value;
        setConditions(updatedConditions);
    };

    return (
        <div className="tab-content">
            <h3>Where Do You Want to Display Your Template?</h3>
            <p>
                Set the conditions that determine where your template is used throughout your site.
            </p>
            {conditions.map((condition, index) => (
                <ConditionRow
                    key={index}
                    index={index}
                    condition={condition}
                    updateCondition={updateCondition}
                    removeCondition={removeCondition}
                />
            ))}
            <button onClick={addCondition} className="add-condition-button">
                Add Condition
            </button>
        </div>
    );
};

export default ConditionsTab;