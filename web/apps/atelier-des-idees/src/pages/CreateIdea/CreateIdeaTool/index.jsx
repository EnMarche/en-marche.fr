import React from 'react';
import PropTypes from 'prop-types';
import { FIRST_QUESTIONS, SECOND_QUESTIONS } from '../constants/questions';
import QuestionBlock from '../QuestionBlock';

function CreateIdeaTool(props) {
    return (
        <article className="create-idea-tool">
            <section className="create-idea-tool__start-section">
                <div className="create-idea-tool__section-title">
                    <p className="create-idea-tool__section-subtitle">Pour commencer</p>
                    <h2 className="create-idea-tool__section-title__main">
                        Quelles sont les caractéristiques principales de votre idée ?
                    </h2>
                </div>
                {FIRST_QUESTIONS.map(({ id, label, question, placeholder, canCollapse }, index) => (
                    <QuestionBlock
                        canCollapse={canCollapse}
                        initialContent={props.values[id]}
                        key={id}
                        mode={props.isEditing ? 'edit' : 'contribute'}
                        label={label}
                        question={question}
                        placeholder={placeholder}
                        nbQuestion={index + 1}
                        onTextChange={htmlContent => props.onQuestionTextChange(id, htmlContent)}
                    />
                ))}
            </section>
            <section className="create-idea-tool__continue-section">
                <div className="create-idea-tool__section-title">
                    <p className="create-idea-tool__section-subtitle">Pour aller plus loin</p>
                    <h2 className="create-idea-tool__section-title__main">
                        Votre idée peut-elle être mise en oeuvre ?
                    </h2>
                </div>
                {SECOND_QUESTIONS.map(({ id, label, question, placeholder, canCollapse }, index) => (
                    <QuestionBlock
                        canCollapse={canCollapse}
                        initialContent={props.values[id]}
                        key={id}
                        mode={props.isEditing ? 'edit' : 'contribute'}
                        label={label}
                        question={question}
                        placeholder={placeholder}
                        nbQuestion={FIRST_QUESTIONS.length + index + 1}
                        onTextChange={htmlContent => props.onQuestionTextChange(id, htmlContent)}
                    />
                ))}
            </section>
        </article>
    );
}

CreateIdeaTool.defaultProps = {
    values: {},
    isEditing: false,
};

CreateIdeaTool.propTypes = {
    isEditing: PropTypes.bool,
    onQuestionTextChange: PropTypes.func.isRequired,
    values: PropTypes.object,
};

export default CreateIdeaTool;
