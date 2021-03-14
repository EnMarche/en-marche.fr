import React from 'react';
import { render } from 'react-dom';
import NationalCouncilCandidacyWidget from '../components/NationalCouncilCandidacyWidget';

export default (api, qualityFieldSelector, submitButtonSelector, wrapperSelector, messages, availableGenders, neededQualities, invitations) => {
    render(
        <NationalCouncilCandidacyWidget
            api={api}
            qualityFieldSelector={qualityFieldSelector}
            submitButtonSelector={submitButtonSelector}
            messages={messages}
            availableGenders={availableGenders}
            neededQualities={neededQualities}
            invitations={invitations}
        />,
        dom(wrapperSelector)
    );
};
