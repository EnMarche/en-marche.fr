import React from 'react';
import { render } from 'react-dom';
import NationalCouncilCandidacyWidget from '../components/NationalCouncilCandidacyWidget';

export default (api, qualityFieldSelector, submitButtonSelector, wrapperSelector) => {
    render(
        <NationalCouncilCandidacyWidget
            api={api}
            qualityFieldSelector={qualityFieldSelector}
            submitButtonSelector={submitButtonSelector}
        />,
        dom(wrapperSelector)
    );
};
