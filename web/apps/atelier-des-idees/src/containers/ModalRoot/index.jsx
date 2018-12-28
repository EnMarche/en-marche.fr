import React from 'react';
import { connect } from 'react-redux';
import ReactModal from 'react-modal';
import { hideModal } from '../../redux/actions/modal';
import { selectModalData } from '../../redux/selectors/modal';

// components
import ReportsModal from '../../components/Modal/ReportsModal';
import PublishIdeaForm from '../../components/Modal/PublishIdeaForm';

const MODAL_COMPONENTS = {
    // to use a modal, just add it below with its corresponding type
    // ex:
    // modalTypes.TEST_MODAL: TestModal,
    REPORTS_MODAL: ReportsModal,
    PUBLISH_IDEA_MODAL: PublishIdeaForm,
};

class ModalRoot extends React.Component {
    constructor(props) {
        super(props);
        this.closeModal = this.closeModal.bind(this);
    }

    closeModal() {
        this.props.hideModal();
    }

    render() {
        const { modalType, modalProps, isOpen } = this.props;
        if (!modalType) {
            return null;
        }

        const SpecificModal = MODAL_COMPONENTS[modalType];
        return (
            <ReactModal
                className="modal"
                overlayClassName="modal-overlay"
                isOpen={isOpen}
                onRequestClose={this.closeModal}
                ariaHideApp={false}
            >
                <button className="modal-closeBtn" onClick={this.closeModal}>
                    <img src="/assets/img/icn_close.svg" />
                </button>
                <div className="modal-content-wrapper">
                    <SpecificModal closeModal={this.closeModal} {...modalProps} />
                </div>
            </ReactModal>
        );
    }
}

function mapStateToProps(state) {
    const modalData = selectModalData(state);
    return { ...modalData };
}

export default connect(
    mapStateToProps,
    { hideModal }
)(ModalRoot);
