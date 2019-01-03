import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { DELETE_IDEA_MODAL } from '../../constants/modalTypes';
import { showModal } from '../../redux/actions/modal';
import { deleteIdea } from '../../redux/thunk/ideas';
import { selectMyIdeas } from '../../redux/selectors/myIdeas';
import { selectMyContributions } from '../../redux/selectors/myContributions';
import MyIdeasModal from '../../components/Modal/MyIdeasModal';

function MyIdeasContainer(props) {
    const { data, ...otherProps } = props;
    const { myIdeasData, myContributionsData } = data;
    const { tabActive } = props;
    return (
        <MyIdeasModal my_ideas={myIdeasData} my_contribs={myContributionsData} tabActive={tabActive} {...otherProps} />
    );
}

MyIdeasContainer.propTypes = {
    data: PropTypes.object.isRequired,
};

function mapStateToProps(state) {
    const myIdeasData = selectMyIdeas(state);
    const myContributionsData = selectMyContributions(state);
    return {
        data: { myIdeasData, myContributionsData },
    };
}

function mapDispatchToProps(dispatch) {
    return {
        onDeleteIdea: id =>
            dispatch(
                showModal(DELETE_IDEA_MODAL, {
                    onConfirmDelete: () => dispatch(deleteIdea(id)),
                })
            ),
    };
}

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(MyIdeasContainer);
