import React from 'react';
import PropTypes from 'prop-types';
import Loader from './Loader';
import ReqwestApiClient from '../services/api/ReqwestApiClient';

export default class TerritorialCouncilCandidacyWidget extends React.Component {
    constructor(props) {
        super(props);

        this.qualitySelect = dom(props.qualityFieldSelector);
        this.submitButton = dom(props.submitButtonSelector);

        this.state = {
            isLoading: true,
            quality: this.qualitySelect.value,
            quality_zones: JSON.parse(this.qualitySelect.dataset.qualities),
            searchQuery: null,
            error: null,
            memberships: [],
        };

        this.handleSearchChange = this.handleSearchChange.bind(this);
        this.handleQualityChange = this.handleQualityChange.bind(this);
    }

    componentDidMount() {
        on(this.qualitySelect, 'change', this.handleQualityChange);

        this.loadMemberships();
    }

    render() {
        let content;

        this.submitButton.innerText = 'Envoyer l\'invitation';

        if (this.state.isLoading) {
            content = <Loader wrapperClassName={'text--center space--30-0'} />;
            hide(this.submitButton);
        } else if (this.state.error) {
            content = <p className={'text--body text--gray text--small text--center'}>
                {this.state.error}
            </p>;
            if (
                this.state.success
                && 0 === this.state.memberships.length
                && (
                    -1 !== ['department_councilor', 'regional_councilor'].indexOf(this.state.quality)
                    || ('city_councilor' === this.state.quality
                        && 0 === this.state.quality_zones[this.state.quality].indexOf('Paris '))
                )
            ) {
                this.submitButton.innerText = 'Candidater seul à titre dérogatoire';
                show(this.submitButton);
            } else {
                hide(this.submitButton);
            }
        } else {
            show(this.submitButton);

            content = (
                <div className="membership-container">
                    {this.state.memberships.map((membership, key) => (
                        <div className="form__radio" key={key}>
                            <input
                                type="radio"
                                name={'candidacy_quality[invitations][membership]'}
                                required="required" id={`membership_${membership.uuid}`} value={membership.uuid} />

                            <label className="form form__label required" htmlFor={`membership_${membership.uuid}`}>
                                <div className="l__row identity">
                                    <div
                                        className="avatar-initials avatar--small avatar--style-02 b__nudge--right-small"
                                    >
                                        {membership.adherent.initials}
                                    </div>
                                    <div>
                                        <div className="font-roboto">{membership.adherent.full_name}</div>
                                    </div>
                                </div>
                            </label>
                        </div>)
                    )}
                </div>
            );
        }

        return (
            <div>
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
                        error: 'Impossible de constituer un binôme, aucun membre n\'est disponible.',
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
}

TerritorialCouncilCandidacyWidget.propTypes = {
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
    qualityFieldSelector: PropTypes.string.isRequired,
    submitButtonSelector: PropTypes.string.isRequired,
};
