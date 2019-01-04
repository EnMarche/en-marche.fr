import React from 'react';
import PropTypes from 'prop-types';
import TextEditor from '../../../components/TextEditor';
import Collapse from '../../../components/Collapse';

function QuestionBlockHeader({ label, question, nbQuestion }) {
    return (
        <h3 className="question-block-header">
            <span className="question-block-header__label">{`${nbQuestion}. ${label ? `${label} : ` : ''}`}</span>
            <span className="question-block-header__question">{question}</span>
        </h3>
    );
}

function QuestionBlockBody(props) {
    return (
        <React.Fragment>
            {'edit' === props.mode || props.isEditing ? (
                <React.Fragment>
                    <TextEditor
                        initialContent={props.initialContent}
                        maxLength={1700}
                        onChange={htmlContent => props.onTextChange(htmlContent)}
                        placeholder={props.placeholder}
                    />
                </React.Fragment>
            ) : (
                <React.Fragment>
                    <div dangerouslySetInnerHTML={{ __html: props.initialContent }} />
                </React.Fragment>
            )}
            {'edit' !== props.mode && props.isAuthor && (
                <div className="question-block__editing-footer">
                    {props.isEditing ? (
                        <React.Fragment>
                            <button className="question-block__editing-footer__btn" onClick={props.onCancelAnswer}>
                                Annuler
                            </button>
                            <button
                                className="question-block__editing-footer__btn editing-footer__btn--main"
                                onClick={props.onSaveAnswer}
                            >
                                Enregistrer
                            </button>
                        </React.Fragment>
                    ) : (
                        <button
                            className="question-block__editing-footer__btn editing-footer__btn--main"
                            onClick={props.onEditAnswer}
                        >
                            Editer
                        </button>
                    )}
                </div>
            )}
        </React.Fragment>
    );
}

class QuestionBlock extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isEditing: false,
            value: '',
            initialValue: props.initialContent,
        };
        this.onTextChange = this.onTextChange.bind(this);
        this.onSaveAnswer = this.onSaveAnswer.bind(this);
        this.onCancelAnswer = this.onCancelAnswer.bind(this);
        this.renderBody = this.renderBody.bind(this);
    }

    onTextChange(content) {
        if (this.state.isEditing) {
            this.setState({ value: content });
        } else {
            this.props.onTextChange(content);
        }
    }

    onSaveAnswer() {
        this.props.onTextChange(this.state.value, true);
        this.setState({ isEditing: false, value: '' });
    }

    onCancelAnswer() {
        this.setState({ isEditing: false, value: '' });
    }

    renderBody() {
        const { placeholder, initialContent, isAuthor, mode } = this.props;
        return (
            <QuestionBlockBody
                isAuthor={isAuthor}
                initialContent={initialContent}
                placeholder={placeholder}
                onTextChange={this.onTextChange}
                mode={mode}
                isEditing={this.state.isEditing}
                onEditAnswer={() => this.setState({ isEditing: true })}
                onSaveAnswer={this.onSaveAnswer}
                onCancelAnswer={this.onCancelAnswer}
            />
        );
    }

    render() {
        const { label, question, nbQuestion } = this.props;
        return (
            <div className="question-block">
                {this.props.canCollapse ? (
                    <Collapse title={<QuestionBlockHeader label={label} question={question} nbQuestion={nbQuestion} />}>
                        {this.renderBody()}
                    </Collapse>
                ) : (
                    <React.Fragment>
                        <QuestionBlockHeader label={label} question={question} nbQuestion={nbQuestion} />
                        {this.renderBody()}
                    </React.Fragment>
                )}
            </div>
        );
    }
}

QuestionBlock.defaultProps = {
    canCollapse: false,
    initialContent: '',
    isAuthor: false,
    placeholder: undefined,
};

QuestionBlock.propTypes = {
    isAuthor: PropTypes.bool,
    canCollapse: PropTypes.bool,
    initialContent: PropTypes.string,
    label: PropTypes.string.isRequired,
    mode: PropTypes.oneOf(['edit', 'contribute']),
    nbQuestion: PropTypes.number.isRequired,
    onTextChange: PropTypes.func.isRequired,
    placeholder: PropTypes.string,
    question: PropTypes.string.isRequired,
};

export default QuestionBlock;
