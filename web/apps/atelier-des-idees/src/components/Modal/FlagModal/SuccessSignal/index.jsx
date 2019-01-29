import React from 'react';
import icn_state_success from './../../../../img/icn_state_success.svg';

class SuccessSignal extends React.PureComponent {
    render() {
        return (
            <div className="success-signal">
                <img className="success-signal__img" src={icn_state_success} />
                <h3 className="success-signal__title">Merci</h3>
                <p className="success-signal__text">
					Votre signalement a bien été pris en compte et va être analysé par les
					équipes de LaREM dans les plus brefs délais.
                </p>
            </div>
        );
    }
}

export default SuccessSignal;
