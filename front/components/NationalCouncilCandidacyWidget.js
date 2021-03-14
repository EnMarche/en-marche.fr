import React from 'react';
import PropTypes from 'prop-types';
import Loader from './Loader';
import ReqwestApiClient from '../services/api/ReqwestApiClient';
import _ from 'lodash';

export default class NationalCouncilCandidacyWidget extends React.Component {
    constructor(props) {
        super(props);

        this.qualitySelect = dom(props.qualityFieldSelector);
        this.submitButton = dom(props.submitButtonSelector);

        this.totalMembres = 3;

        this.state = {
            isLoading: true,
            quality: this.qualitySelect.value,
            quality_zones: JSON.parse(this.qualitySelect.dataset.qualities),
            searchQuery: null,
            error: null,
            memberships: [],
            activeMembre: 2,
            activeMemberships: props.invitations && props.invitations.length ? {
                2: props.invitations[0],
                3: props.invitations[1],
            } : {},
        };

        this.handleSearchChange = this.handleSearchChange.bind(this);
        this.handleCandidacyClick = this.handleCandidacyClick.bind(this);
        this.handleQualityChange = this.handleQualityChange.bind(this);
    }

    componentDidMount() {
        on(this.qualitySelect, 'change', this.handleQualityChange);

        this.loadMemberships();
    }

    render() {
        let content;

        this.submitButton.innerText = 'Envoyer les invitations';

        const isValidQualities = this.isValidQualities();
        const isValidGenders = this.isValidGenders();

        hide(this.submitButton);

        if (this.state.isLoading) {
            content = <Loader wrapperClassName={'text--center space--30-0'} />;
        } else if (this.state.error) {
            content = <p className={'text--body text--gray text--small text--center'}>
                {this.state.error}
            </p>;
        } else {
            if (isValidQualities && isValidGenders) {
                show(this.submitButton);
            }

            content = (
                <div>
                    <div className="membership-container">
                        {this.state.memberships.map((membership, key) => (
                            <div className={'form__radio'} key={key}>
                                <input
                                    type="radio"
                                    name={'candidacy_quality[invitation][membership]'}
                                    onChange={() => this.handleCandidacyClick(membership)}
                                    required="required" id={`membership_${membership.uuid}`} value={membership.uuid} />

                                <label className="form form__label required" htmlFor={`membership_${membership.uuid}`}>
                                    <div className="l__row identity">
                                        <div className="avatar-initials avatar--small avatar--style-02 b__nudge--right-small">
                                            {membership.adherent.initials}
                                        </div>
                                        <div>
                                            <div className="font-roboto">
                                                {membership.adherent.full_name}

                                                <span className="candidate-gender l__col l__col--center b__nudge--left">
                                                    {'female' === membership.adherent.gender ? 'F' : 'H' }
                                                </span>
                                            </div>

                                            <div className="text--smallest font-roboto">{membership.qualities.map(row => this.transQuality(row.name)).join(', ')}</div>
                                        </div>
                                    </div>
                                </label>
                            </div>)
                        )}
                    </div>
                </div>
            );
        }

        const members = [];

        for (let i = 2; i <= this.totalMembres; i++) {
            members.push(
                <div className="l__col l__col--half" key={i}>
                    <div className={`cursor--pointer ${this.state.activeMembre === i ? 'active' : ''} ${this.state.activeMemberships[i] ? (isValidQualities && isValidGenders ? 'is-valid' : 'is-error') : ''}`}
                        onClick={() => this.setState({ activeMembre: i })}>
                        <h4 className="b__nudge--bottom">Membre {i}</h4>

                        {this.state.activeMemberships[i] ?
                            <>
                                <input type="hidden" name={`candidacy_quality[invitations][${i - 2}][membership]`} value={this.state.activeMemberships[i].uuid} />
                                <div className="l__row identity">
                                    <div className="avatar-initials avatar--small avatar--style-02 b__nudge--right-small">
                                        {this.state.activeMemberships[i].adherent.initials}
                                    </div>
                                    <div>
                                        <div className="font-roboto">
                                            {this.state.activeMemberships[i].adherent.full_name}

                                            <span className={`candidate-gender l__col l__col--center b__nudge--left ${!isValidGenders ? 'text--error' : ''}`}>
                                                {'female' === this.state.activeMemberships[i].adherent.gender ? 'F' : 'H' }
                                            </span>
                                        </div>

                                        <div className="text--smallest font-roboto">{this.state.activeMemberships[i].qualities.map(row => <span key={row.name} className={`quality ${1 === this.state.activeMemberships[i].qualities.length ? 'active' : ''}`}>{this.transQuality(row.name)}</span>)}</div>
                                    </div>
                                </div>
                            </>
                            : <div>à choisir</div>}
                        <div className={'check-img'}>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor"
                                className="bi bi-check-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path
                                    d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                            </svg>
                        </div>
                        <div className={'nok-img'}>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor"
                                className="bi bi-x-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path
                                    d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            );
        }

        return (
            <div className={'national-council-candidacy--container'}>
                <div className="l__row b__nudge--top b__nudge--bottom-large selected-memberships--row">
                    {members}
                </div>

                <div className="em-form__group">
                    <div className="em-form__field--ctn">
                        <input type="search" placeholder="Rechercher un membre..."
                            id="member-search"
                            className="em-form__field form form__field"
                            onChange={this.handleSearchChange}
                        />
                    </div>
                </div>

                {content}
            </div>
        );
    }

    loadMemberships() {
        const quality = this.state.quality;
        const query = this.state.searchQuery;

        this.props.api.getTerritorialCouncilAvailableMemberships(
            { quality, query },
            (data) => {
                if (quality !== this.state.quality || query !== this.state.searchQuery) {
                    return;
                }

                if (!Array.isArray(data) || 1 > data.length) {
                    this.setState({
                        isLoading: false,
                        error: 'Impossible de constituer un trinôme, aucun membre n\'est disponible.',
                        memberships: [],
                        success: true,
                    });
                } else {
                    this.setState({
                        isLoading: false,
                        error: null,
                        memberships: data,
                        success: true,
                    });
                }
            },
            (response) => {
                if (quality !== this.state.quality || query !== this.state.searchQuery) {
                    return;
                }

                this.setState({
                    isLoading: false,
                    error: response.message || 'Une erreur est survenue',
                    success: false,
                });
            }
        );
    }

    handleSearchChange(event) {
        this.setState(
            {
                isLoading: true,
                searchQuery: event.target.value,
            },
            this.loadMemberships
        );
    }

    handleQualityChange(event) {
        this.setState(
            {
                isLoading: true,
                quality: event.target.value,
            },
            this.loadMemberships
        );
    }

    isValidQualities() {
        const members = {
            ...{ 1: { qualities: [{ name: this.state.quality }] } },
            ...this.state.activeMemberships,
        };

        const foundKeys = [];

        for (const [i, y] of Object.entries(members)) {
            for (const k in this.props.neededQualities) {
                if (0 < _.intersection(this.props.neededQualities[k], _.map(y.qualities, 'name')).length) {
                    if (-1 === foundKeys.indexOf(k)) {
                        foundKeys.push(k);
                    } else {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    handleCandidacyClick(membership) {
        this.setState((state) => {
            const a = {};
            a[state.activeMembre] = membership;
            return {
                activeMemberships: {
                    ...state.activeMemberships,
                    ...a,
                },
            };
        });
    }

    isValidGenders() {
        const genders = _.clone(this.props.availableGenders);

        for (const [i, y] of Object.entries(this.state.activeMemberships)) {
            if (0 > --genders[y.adherent.gender]) {
                return false;
            }
        }

        return true;
    }

    transQuality(quality) {
        for (const row of this.props.messages) {
            if (row.key === quality) {
                return row.label;
            }
        }

        return null;
    }
}

NationalCouncilCandidacyWidget.propTypes = {
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
    qualityFieldSelector: PropTypes.string.isRequired,
    submitButtonSelector: PropTypes.string.isRequired,
    messages: PropTypes.arrayOf(PropTypes.object).isRequired,
    availableGenders: PropTypes.object.isRequired,
    neededQualities: PropTypes.array.isRequired,
    invitations: PropTypes.arrayOf(PropTypes.object),
};
