import React from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import { Mobile, NotMobile } from '../../../helpers/responsive';

class VotingFooter extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            toggleVotes: false,
        };

        // ref of voting footer
        this.footerRef = React.createRef();

        this.toggleOutsideHover = this.toggleOutsideHover.bind(this);
        this.handleHoverOutside = this.handleHoverOutside.bind(this);
    }

    toggleOutsideHover() {
        this.state.toggleVotes
            ? document.addEventListener('mouseover', this.handleHoverOutside)
            : document.removeEventListener('mouseover', this.handleHoverOutside);
    }

    handleHoverOutside(event) {
        if (this.footerRef) {
            // Check if the postion of the mouse is outside of the footer
            const isOutofFooter = !this.footerRef.current.contains(event.target);
            if (isOutofFooter) {
                // Cancel the voting mode
                this.setState({ toggleVotes: false });
            }
        }
    }

    render() {
        return (
            <div className="voting-footer" ref={this.footerRef}>
                <Mobile>
                    <div className="voting-footer__label">
                        <p className="voting-footer__label__text">Je vote :</p>
                        <button
                            className={classnames('voting-footer__label__action', {
                                rotate: this.state.toggleVotes,
                            })}
                            onClick={() =>
                                this.setState(prevState => ({
                                    toggleVotes: !prevState.toggleVotes,
                                }))
                            }
                        >
                            <div className="voting-footer__label__action__arrow" />
                        </button>
                    </div>
                </Mobile>

                <NotMobile>
                    {!this.state.toggleVotes && (
                        <div className="voting-footer__action">
                            <p className="voting-footer__action__total-votes">
                                {this.props.totalVotes} votes
                            </p>
                            <div className="voting-footer__action__button">
                                <button
                                    className="button--secondary"
                                    onClick={() =>
                                        this.setState(
                                            prevState => ({ toggleVotes: !prevState.toggleVotes }),
                                            () => this.toggleOutsideHover()
                                        )
                                    }
                                >
                                    <img
                                        className="voting-footer__action__button__icon"
                                        src="/assets/img/icn_20px_thumb.svg"
                                    />
									Je vote
                                </button>
                            </div>
                        </div>
                    )}
                    {this.state.toggleVotes && (
                        <p className="voting-footer__text">Je vote: </p>
                    )}
                </NotMobile>

                {this.state.toggleVotes &&
					this.props.votes.map(vote => (
					    <button
					        key={vote.id}
					        className={classnames('voting-footer__vote', {
					            'voting-footer__vote--selected': vote.isSelected,
					        })}
					        onClick={() => this.props.onSelected(vote.id)}
					    >
					        <span className="voting-footer__vote__name">{vote.name}</span>
					        <span className="voting-footer__vote__count">{vote.count}</span>
					    </button>
					))}
            </div>
        );
    }
}

VotingFooter.propTypes = {
    votes: PropTypes.arrayOf(
        PropTypes.shape({
            id: PropTypes.string.isRequired,
            name: PropTypes.string.isRequired,
            count: PropTypes.number.isRequired,
            isSelected: PropTypes.bool.isRequired,
        })
    ).isRequired,
    totalVotes: PropTypes.number.isRequired,
    onSelected: PropTypes.func.isRequired,
};

export default VotingFooter;
