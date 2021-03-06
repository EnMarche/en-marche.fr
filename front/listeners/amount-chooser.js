import React from 'react';
import { render } from 'react-dom';
import AmountChooser from '../components/AmountChooser';

/*
 * Create amount chooser inputs
 */
export default () => {
    findAll(document, 'input.amount-chooser').forEach((element) => {
        const { name } = element;
        const value = parseFloat(element.value) || 0;
        const chooser = document.createElement('div');

        insertAfter(element, chooser);
        remove(element);

        render(
            <AmountChooser
                name={name}
                value={value}
            />,
            chooser
        );
    });
};
