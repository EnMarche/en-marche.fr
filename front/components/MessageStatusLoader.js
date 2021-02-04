import React, { PropTypes } from 'react';
import Loader from './Loader';
import numberFormat from '../utils/number';
import ReqwestApiClient from '../services/api/ReqwestApiClient';

const MAX_API_CALLS = 50;

export default class MessageStatusLoader extends React.Component {
    constructor(props) {
        super(props);

        this.api = props.api;
        this.messageId = props.messageId;
        this.withResetButton = props.withResetButton;

        this.state = {
            synchronized: props.synchronized,
            recipientCount: props.recipientCount,
            calls: 0,
        };
    }

    componentDidMount() {
        if (!this.state.synchronized) {
            this.timerId = setInterval(
                () => this.refreshMessageStatus(),
                5000 // each 5sec
            );
        }
    }

    componentWillUnmount() {
        if (this.timerId) {
            clearInterval(this.timerId);
        }
    }

    refreshMessageStatus() {
        this.api.getMessageStatus(
            this.messageId,
            (data) => {
                this.setState(
                    (state) => ({
                        synchronized: data.synchronized,
                        recipientCount: data.recipient_count,
                        calls: state.calls + 1,
                    })
                );
            },
            () => this.setState({ calls: MAX_API_CALLS })
        );
    }

    renderActionBlock() {
        if (this.state.recipientCount) {
            return <div>
                    {this.props.sendLocked
                        ? <p className="text--medium-small">
                            Vous avez atteint la limite d'envoi de mails pour ce mois-ci
                        </p>
                        : <p className="text--medium-small">
                            Vous allez envoyer un message à&nbsp;
                            <span className="text--bold text--blue--dark">
                                {numberFormat(this.state.recipientCount)}
                            </span>
                            &nbsp;contact{1 < this.state.recipientCount ? 's' : ''} !
                        </p>
                    }
                <p>
                    <a href={this.props.sendLocked ? '#' : './send'}
                       className={`btn btn--large-and-full b__nudge--top em-confirm--trigger ${
                        this.props.sendLocked ? ' btn--disabled' : ' btn--blue'}`}
                       data-confirm-title="Confirmation"
                       data-confirm-content={`Êtes-vous sûr de vouloir envoyer le message à ${
                           this.state.recipientCount} contact${1 < this.state.recipientCount ? 's' : ''} ?`}>
                        Envoyer
                    </a>
                    {this.renderActionButtons()}
                </p>
            </div>;
        }

        return <div>
            <p className="text--medium-small">Votre filtre ne correspond à aucun contact !</p>
            {this.withResetButton
                ? <p>
                    <a href="./filtrer" className="btn btn--ghosting--blue btn--large-and-full b__nudge--top">
                        RECHARGER
                    </a>
                    {this.renderActionButtons()}
                </p>
                : ''
            }
        </div>;
    }

    render() {
        if (this.state.synchronized) {
            this.componentWillUnmount();
        }

        if (this.state.calls >= MAX_API_CALLS) {
            this.componentWillUnmount();

            return <div>
                <p className="alert alert--tips">
                    Nous n'avons pas encore terminé la recherche, veuillez revenir dans quelques instants.
                </p>
            </div>;
        }

        return (
            <div>
                {!this.state.synchronized ? <Loader title="Chargement de vos contacts" /> : this.renderActionBlock()}
            </div>
        );
    }

    renderActionButtons() {
        return (
            <span>
                <a
                    href="./visualiser?f"
                    className="btn btn--ghosting--blue btn--large-and-full b__nudge--top-15"
                >
                    Prévisualiser avant envoi
                </a>
                <a href="./tester" className="btn btn--ghosting--blue btn--large-and-full b__nudge--top-15">
                    M'envoyer un message test
                </a>
            </span>
        );
    }
}

MessageStatusLoader.defaultProps = {
    synchronized: false,
    recipientCount: null,
    withResetButton: false,
    sendLocked: false,
};

MessageStatusLoader.propTypes = {
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
    messageId: PropTypes.string.isRequired,
    synchronized: PropTypes.bool,
    recipientCount: PropTypes.number,
    withResetButton: PropTypes.bool,
    sendLocked: PropTypes.bool,
};
