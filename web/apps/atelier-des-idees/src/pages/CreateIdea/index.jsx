import React from 'react';
import PropTypes from 'prop-types';
import TextArea from '../../components/TextArea';
import QuestionBlock from './QuestionBlock';
import CreateIdeaActions from './CreateIdeaActions';
import { FIRST_QUESTIONS, SECOND_QUESTIONS } from './constants/questions';

function getInitialState(questions = []) {
    return questions.reduce((acc, question) => {
        acc[question.id] = '';
        return acc;
    }, {});
}

class CreateIdeaPage extends React.Component {
    constructor(props) {
        super(props);
        this.state = { title: '', ...getInitialState(FIRST_QUESTIONS), ...getInitialState(SECOND_QUESTIONS) };
    }

    onQuestionTextChange(id, htmlContent) {
        this.setState({ [id]: htmlContent });
    }

    render() {
        return (
            <div className="create-idea-page">
                <div className="create-idea-page__header l__wrapper">
                    <button className="button create-idea-actions__back" onClick={() => this.props.onBackClicked()}>
                        ← Retour
                    </button>
                    <CreateIdeaActions
                        onDeleteClicked={this.props.onDeleteClicked}
                        onPublishClicked={() => this.props.onPublichClicked(this.state)}
                        onSaveClicked={this.props.onSaveClicked}
                        mode="header"
                    />
                </div>
                <div className="create-idea-page__content">
                    <div className="create-idea-page__content__main l__wrapper--medium">
                        <section className="create-idea-page__title-section">
                            <TextArea
                                maxLength={120}
                                onChange={value => this.setState({ title: value })}
                                placeholder="Titre de l'idée"
                                value={this.state.title}
                            />
                        </section>
                        <section className="create-idea-page__start-section">
                            <div className="create-idea-page__section-title">
                                <p className="create-idea-page__section-subtitle">Pour commencer</p>
                                <h2 className="create-idea-page__section-title__main">
                                    Quelles sont les caractéristiques principales de votre idée ?
                                </h2>
                            </div>
                            {FIRST_QUESTIONS.map(({ id, label, question, placeholder, canCollapse }, index) => (
                                <QuestionBlock
                                    canCollapse={canCollapse}
                                    key={id}
                                    label={label}
                                    question={question}
                                    placeholder={placeholder}
                                    nbQuestion={index + 1}
                                    onTextChange={htmlContent => this.onQuestionTextChange(id, htmlContent)}
                                />
                            ))}
                        </section>
                        <section className="create-idea-page__continue-section">
                            <div className="create-idea-page__section-title">
                                <p className="create-idea-page__section-subtitle">Pour aller plus loin</p>
                                <h2 className="create-idea-page__section-title__main">
                                    Votre idée peut-elle être mise en oeuvre ?
                                </h2>
                            </div>
                            {SECOND_QUESTIONS.map(({ id, label, question, placeholder, canCollapse }, index) => (
                                <QuestionBlock
                                    canCollapse={canCollapse}
                                    key={id}
                                    label={label}
                                    question={question}
                                    placeholder={placeholder}
                                    nbQuestion={FIRST_QUESTIONS.length + index + 1}
                                    onTextChange={htmlContent => this.onQuestionTextChange(id, htmlContent)}
                                />
                            ))}
                        </section>
                        <div className="create-idea-page__footer">
                            <CreateIdeaActions
                                onDeleteClicked={this.props.onDeleteClicked}
                                onPublishClicked={() => this.props.onPublichClicked(this.state)}
                                onSaveClicked={this.props.onSaveClicked}
                                mode="footer"
                            />
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

// TODO: remove default props when linking to proper callbacks
CreateIdeaPage.defaultProps = {
    onBackClicked: () => alert('Retour'),
    onPublichClicked: () => alert('Publier'),
    onDeleteClicked: () => alert('Supprimer'),
    onSaveClicked: () => alert('Enregistrer'),
};

CreateIdeaPage.propTypes = {
    onBackClicked: PropTypes.func.isRequired,
    onPublichClicked: PropTypes.func.isRequired,
    onDeleteClicked: PropTypes.func.isRequired,
    onSaveClicked: PropTypes.func.isRequired,
};

export default CreateIdeaPage;
