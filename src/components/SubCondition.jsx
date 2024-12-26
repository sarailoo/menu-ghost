import React from 'react';
import SubOptions from './SubOptions';

const SubCondition = ({ condition, index, updateCondition, pageConditions }) => {
    const { scope, subScope } = condition;

    // Retrieve relevant data based on the selected scope
    const scopeData = pageConditions.scopes.find((item) => item.value === scope) || {};
    const options = scopeData.options || [];
    const additionalData = (scopeData.additionalData && scopeData.additionalData[subScope]) || { list: [], selected: 'All' };

    return (
        <SubOptions
            subScope={subScope}
            options={options}
            additionalData={additionalData}
            updateCondition={(key, value) => updateCondition(index, key, value)}
        />
    );
};

export default SubCondition;