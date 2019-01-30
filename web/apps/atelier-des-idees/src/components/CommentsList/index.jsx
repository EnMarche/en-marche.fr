import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import Comment from './Comment';
import TextArea from '../TextArea';
import Button from '../Button';
import icn_20px_replies from './../../img/icn_20px_replies.svg';
import icn_toggle_content from './../../img/icn_toggle_content.svg';

class CommentsList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            comment: '',
            errorComment: '',
            replyingTo: undefined,
            showComments: false,
        };
    }

    handleSendComment() {
        // Check if empty
        if (!this.state.comment) {
            this.setState({ errorComment: 'Veuillez remplir ce champ' });
            return;
        }
        this.props.onSendComment(this.state.comment);
        this.setState({ comment: '' });
    }

    handleCommentChange(value) {
        this.setState({ comment: value, errorComment: '' });
    }

    render() {
        return (
            <div className="comments-list">
                {!!this.props.comments.length && (
                    <button
                        className="comments-list__collapse-button"
                        onClick={() =>
                            this.setState(prevState => ({
                                showComments: !prevState.showComments,
                            }))
                        }
                    >
                        <img className="comments-list__collapse-button__icon-replies" src={icn_20px_replies} />
                        <span className="comments-list__collapse-button__label">
                            {this.props.comments.length} {this.props.collapseLabel}
                            {1 < this.props.comments.length && 's'}
                        </span>
                        <img
                            className={classNames('comments-list__collapse-button__icon-toggle', {
                                'comments-list__collapse-button__icon-toggle--rotate': !this.state.showComments,
                            })}
                            src={icn_toggle_content}
                        />
                    </button>
                )}
                {this.props.comments.length ? (
                    this.state.showComments &&
                    this.props.comments.map(comment => (
                        <React.Fragment>
                            <Comment
                                {...comment}
                                hasActions={this.props.hasActions}
                                isAuthor={this.props.currentUserId === comment.author.uuid}
                                onReply={() => this.setState({ replyingTo: comment.uuid })}
                                onDelete={() => this.props.onDeleteComment(comment.uuid)}
                                onApprove={() => this.props.onApproveComment(comment.uuid)}
                                onReport={() => this.props.onReportComment(comment.uuid)}
                                canAnswer={!this.props.parentId}
                                canApprove={this.props.currentUserId === this.props.ownerId}
                            />
                            {((comment.replies && !!comment.replies.length) ||
                                this.state.replyingTo === comment.uuid) && (
                                    <div className="comments-list__replies">
                                        <CommentsList
                                            comments={comment.replies}
                                            onSendComment={value =>
                                            // send parent comment id as (optional) second parameter
                                                this.props.onSendComment(value, comment.uuid)
                                            }
                                            onDeleteComment={commentId =>
                                                this.props.onDeleteComment(commentId, comment.uuid)
                                            }
                                            onApproveComment={commentId =>
                                                this.props.onApproveComment(commentId, comment.uuid)
                                            }
                                            onReportComment={commentId =>
                                                this.props.onReportComment(commentId, comment.uuid)
                                            }
                                            onLoadMore={() => this.props.onLoadMore(comment.uuid)}
                                            parentId={comment.uuid}
                                            showForm={this.props.showForm}
                                            collapseLabel="réponse"
                                            placeholder="Écrivez votre réponse"
                                            emptyLabel={null}
                                            total={comment.nbReplies}
                                            isSendingComment={this.props.sendingReplies.includes(comment.uuid)}
                                            hasActions={this.props.hasActions}
                                            ownerId={this.props.ownerId}
                                            currentUserId={this.props.currentUserId}
                                        />
                                    </div>
                                )}
                        </React.Fragment>
                    ))
                ) : (
                    <React.Fragment>
                        {!this.props.parentId && (
                            <p className="comments-list__empty">
                                Soyez <span className="comments-list__empty--highlight">le premier</span> à contribuer
                                sur cette partie
                            </p>
                        )}
                    </React.Fragment>
                )}
                {this.state.showComments && 0 < this.props.total - this.props.comments.length && (
                    <div className="comments-list__more">
                        <button
                            className="comments-list__more-btn"
                            onClick={() => this.props.onLoadMore()}
                        >{`Afficher plus de réponses (${this.props.total - this.props.comments.length})`}</button>
                    </div>
                )}
                {this.props.showForm && (
                    <form
                        className="comments-list__form"
                        onSubmit={(e) => {
                            e.preventDefault();
                            this.handleSendComment();
                        }}
                    >
                        <TextArea
                            value={this.state.comment}
                            onChange={value => this.handleCommentChange(value)}
                            placeholder={this.props.placeholder}
                            error={this.state.errorComment}
                        />
                        <Button
                            type="submit"
                            className="comments-list__form__button button--primary"
                            label="Envoyer"
                            isLoading={this.props.isSendingComment}
                        />
                    </form>
                )}
            </div>
        );
    }
}

CommentsList.defaultProps = {
    comments: [],
    isSendingComment: false,
    sendingReplies: [],
    showForm: true,
    parentId: undefined,
    emptyLabel: '',
    placeholder: 'Ajoutez votre contribution',
    collapseLabel: 'contribution',
    total: 0,
    hasActions: false,
};

CommentsList.propTypes = {
    comments: PropTypes.arrayOf(
        PropTypes.shape({
            uuid: PropTypes.string.isRequired,
            author: PropTypes.object.isRequired,
            content: PropTypes.string.isRequired,
            created_at: PropTypes.string.isRequired, // iso date
            replies: PropTypes.array,
            approved: PropTypes.bool,
            nbReplies: PropTypes.number,
        })
    ),
    isSendingComment: PropTypes.bool,
    sendingReplies: PropTypes.array,
    onSendComment: PropTypes.func.isRequired,
    onDeleteComment: PropTypes.func.isRequired,
    onReportComment: PropTypes.func.isRequired,
    onApproveComment: PropTypes.func.isRequired,
    onLoadMore: PropTypes.func.isRequired,
    currentUserId: PropTypes.string.isRequired,
    ownerId: PropTypes.string.isRequired,
    showForm: PropTypes.bool,
    parentId: PropTypes.string,
    emptyLabel: PropTypes.string,
    placeholder: PropTypes.string,
    total: PropTypes.number,
    hasActions: PropTypes.bool,
};

export default CommentsList;
