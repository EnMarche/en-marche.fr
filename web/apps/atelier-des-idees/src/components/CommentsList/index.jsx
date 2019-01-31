import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import Comment from './Comment';
import TextArea from '../TextArea';
import Button from '../Button';
import icn_20px_replies from './../../img/icn_20px_replies.svg';
import icn_toggle_content from './../../img/icn_toggle_content.svg';
import icn_toggle_content_big from './../../img/icn_toggle_content_big.svg';

class CommentsList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            comment: '',
            errorComment: '',
            replyingTo: '',
            showComments: false,
            showForm: !!props.comments.length || props.showForm,
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
            <div
                className={classNames('comments-list', {
                    'comments-list--closed': !this.state.showForm,
                })}
            >
                {!!this.props.comments.length && (
                    <button
                        className="comments-list__collapse-button"
                        onClick={() =>
                            this.setState(prevState => ({
                                showComments: !prevState.showComments,
                                replyingTo: '',
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
                {this.props.comments.length
                    ? this.state.showComments &&
                      this.props.comments.map(comment => (
                          <React.Fragment>
                              <Comment
                                  {...comment}
                                  hasActions={this.props.isAuthenticated}
                                  isAuthor={this.props.currentUserId === comment.author.uuid}
                                  onReply={() => this.setState({ replyingTo: comment.uuid })}
                                  onDelete={() => this.props.onDeleteComment(comment.uuid)}
                                  onApprove={() => this.props.onApproveComment(comment.uuid)}
                                  onReport={() => this.props.onReportComment(comment.uuid)}
                                  canAnswer={
                                      !this.props.parentId &&
                                      (!comment.replies.length && this.state.replyingTo !== comment.uuid)
                                  }
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
                                              collapseLabel="réponse"
                                              placeholder="Écrivez votre réponse"
                                              emptyLabel={null}
                                              total={comment.nbReplies}
                                              isSendingComment={this.props.sendingReplies.includes(comment.uuid)}
                                              isFormActive={this.state.replyingTo === comment.uuid}
                                              isAuthenticated={this.props.isAuthenticated}
                                              showForm={true}
                                              ownerId={this.props.ownerId}
                                              currentUserId={this.props.currentUserId}
                                          />
                                      </div>
                                  )}
                          </React.Fragment>
                      ))
                    : !this.props.parentId && (
                        <button
                            className="comments-list__empty"
                            onClick={() => this.setState(prevState => ({ showForm: !prevState.showForm }))}
                        >
                            <span className="comments-list__empty__label">
                                  Soyez <span className="comments-list__empty--highlight">le premier</span> à contribuer
                                  sur cette partie
                                <img className="comments-list__empty__toggle" src={icn_toggle_content_big} />
                            </span>
                        </button>
                    )}
                {this.state.showComments && 0 < this.props.total - this.props.comments.length && (
                    <div className="comments-list__more">
                        <button
                            className="comments-list__more-btn"
                            onClick={() => this.props.onLoadMore()}
                        >{`Afficher plus de réponses (${this.props.total - this.props.comments.length})`}</button>
                    </div>
                )}
                {this.state.showForm &&
                    (this.props.isAuthenticated ? (
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
                                autofocus={this.props.isFormActive}
                            />
                            <Button
                                type="submit"
                                className="comments-list__form__button button--primary"
                                label="Envoyer"
                                isLoading={this.props.isSendingComment}
                            />
                        </form>
                    ) : (
                        !this.props.parentId && (
                            <div className="comments-list__contribute">
                                <p className="comments-list__contribute__main">
                                    Pour ajouter votre contribution,{' '}
                                    <a
                                        className="comments-list__contribute__link"
                                        href="?anonymous_authentication_intention=/connexion"
                                    >
                                        connectez-vous
                                    </a>{' '}
                                    ou{' '}
                                    <a
                                        className="comments-list__contribute__link"
                                        href="?anonymous_authentication_intention=/adhesion"
                                    >
                                        créez un compte
                                    </a>
                                </p>
                            </div>
                        )
                    ))}
            </div>
        );
    }
}

CommentsList.defaultProps = {
    collapseLabel: 'contribution',
    comments: [],
    emptyLabel: '',
    isAuthenticated: false,
    isFormActive: false,
    isSendingComment: false,
    parentId: undefined,
    placeholder: 'Ajoutez votre contribution',
    sendingReplies: [],
    showForm: false,
    total: 0,
};

CommentsList.propTypes = {
    collapseLabel: PropTypes.string,
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
    currentUserId: PropTypes.string.isRequired,
    emptyLabel: PropTypes.string,
    isAuthenticated: PropTypes.bool,
    isFormActive: PropTypes.bool,
    isSendingComment: PropTypes.bool,
    onApproveComment: PropTypes.func.isRequired,
    onDeleteComment: PropTypes.func.isRequired,
    onLoadMore: PropTypes.func.isRequired,
    onReportComment: PropTypes.func.isRequired,
    onSendComment: PropTypes.func.isRequired,
    ownerId: PropTypes.string.isRequired,
    parentId: PropTypes.string,
    placeholder: PropTypes.string,
    sendingReplies: PropTypes.array,
    showForm: PropTypes.bool,
    total: PropTypes.number,
};

export default CommentsList;
