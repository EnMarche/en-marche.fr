import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { ideaStatus } from '../../../../constants/api';
import Button from '../../../Button';

class MyIdeas extends React.Component {
    constructor(props) {
        super(props);
        this.CAT_IDEAS_FILTER = [
            {
                showCat: 'showDraft',
                label: 'Brouillons',
                ideas: this.props.ideas.filter(idea => 'DRAFT' === idea.status),
            },
            {
                showCat: 'showPending',
                label: 'idées en cours d’élaboration',
                ideas: this.props.ideas.filter(idea => 'PENDING' === idea.status),
            },
            {
                showCat: 'showFinalized',
                label: 'idées finalisées',
                ideas: this.props.ideas.filter(idea => 'FINALIZED' === idea.status),
            },
        ];

        this.state = {
            showDraft: true,
            showPending: true,
            showFinalized: true,
        };
    }

    render() {
        return (
            <div className="my-ideas">
                {this.CAT_IDEAS_FILTER.map(cat => (
                    <React.Fragment>
                        <button
                            className="my-ideas__category__button"
                            onClick={() =>
                                this.setState(prevState => ({
                                    [cat.showCat]: !prevState[cat.showCat],
                                }))
                            }
                        >
                            <span className="my-ideas__category__button__label">{cat.label.toUpperCase()}</span>
                            <img
                                className={classNames('my-ideas__category__button__icon', {
                                    'my-ideas__category__button__icon--rotate': !this.state[cat.showCat],
                                })}
                                src="/assets/img/icn_toggle_content-blue-yonder.svg"
                            />
                        </button>
                        {cat.ideas.map(
                            idea =>
                                this.state[cat.showCat] && (
                                    <React.Fragment>
                                        <div className="my-ideas__category__idea">
                                            <p className="my-ideas__category__idea__date">
                                                Créée le {new Date(idea.created_at).toLocaleDateString()}
                                            </p>
                                            <h4 className="my-ideas__category__idea__name">{idea.name}</h4>
                                            <div className="my-ideas__category__idea__actions">
                                                {'DRAFT' === idea.status && (
                                                    <button className="my-ideas__category__idea__actions__publish button--primary">
                                                        PUBLIER
                                                    </button>
                                                )}
                                                {'FINALIZED' === idea.status && (
                                                    <button className="my-ideas__category__idea__actions__see-note button--secondary">
                                                        VOIR LA NOTE
                                                    </button>
                                                )}
                                                {'FINALIZED' !== idea.status && (
                                                    <button className="my-ideas__category__idea__actions__edit button--secondary">
                                                        EDITER
                                                    </button>
                                                )}
                                                <Button
                                                    className="my-ideas__category__idea__actions__delete button--tertiary"
                                                    label="Supprimer"
                                                    onClick={() => this.props.onDeleteIdea(idea.uuid)}
                                                />
                                            </div>
                                        </div>
                                        <div className="separator" />
                                    </React.Fragment>
                                )
                        )}
                    </React.Fragment>
                ))}
            </div>
        );
    }
}

MyIdeas.propTypes = {
    ideas: PropTypes.arrayOf(
        PropTypes.shape({
            uuid: PropTypes.string.isRequired,
            name: PropTypes.string.isRequired,
            created_at: PropTypes.string.isRequired, // ISO UTC
            status: PropTypes.oneOf(ideaStatus).isRequired,
        })
    ).isRequired,
    onDeleteIdea: PropTypes.func.isRequired,
};

export default MyIdeas;
