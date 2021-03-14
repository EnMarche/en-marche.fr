import React from 'react';
import PropTypes from 'prop-types';
import Loader from './Loader';
import ReqwestApiClient from '../services/api/ReqwestApiClient';

export default class CommitteeCandidacyWidget extends React.Component {
    constructor(props) {
        super(props);

        this.membershipInput = dom(props.membershipFieldSelector);
        this.submitButton = dom(props.submitButtonSelector);

        this.state = {
            isLoading: false,
            searchQuery: null,
            error: null,
            memberships: [],
        };

        this.handleSearchChange = this.handleSearchChange.bind(this);
    }

    render() {
        let content;

        this.submitButton.innerText = 'Envoyer l\'invitation';
        hide(this.submitButton);

        if (this.state.isLoading) {
            content = <Loader wrapperClassName={'text--center space--30-0'} />;
        } else if (this.state.error) {
            content = <p className={'text--body text--gray text--small text--center'}>
                {this.state.error}
            </p>;
        } else {
            if (0 !== this.state.memberships.length) {
                show(this.submitButton);
            }

            content = (
                <div className="membership-container">
                    {this.state.memberships.map((membership, key) => (
                        <div className="form__radio" key={key}>
                            <input
                                type="radio"
                                name={'candidacy_binome[invitation][membership]'}
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
                            onKeyPress={event => 'Enter' === event.key && event.preventDefault()}
                            onChange={this.handleSearchChange}
                        />
                    </div>
                </div>

                {content}
            </div>
        );
    }

    loadMemberships() {
        const query = this.state.searchQuery;

        this.props.api.getCommitteeAvailableMemberships(
            { slug: this.props.slug, query },
            (data) => {
                if (query !== this.state.searchQuery) {
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
                if (query !== this.state.searchQuery) {
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
}

CommitteeCandidacyWidget.propTypes = {
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
    slug: PropTypes.string.isRequired,
    membershipFieldSelector: PropTypes.string.isRequired,
    submitButtonSelector: PropTypes.string.isRequired,
};
