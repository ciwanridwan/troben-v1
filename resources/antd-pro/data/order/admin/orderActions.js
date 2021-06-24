import ModalAssignPartner from "../../../components/modals/modal-assign-partner";
import ModalCancelConfirm from "../../../components/modals/modal-cancel-confirm";
import ModalPaymentConfirm from "../../../components/modals/modal-payment-confirm";
import {
  PAYMENT_STATUS_DRAFT,
  PAYMENT_STATUS_PENDING,
  STATUS_ACCEPTED,
  STATUS_CREATED,
  STATUS_WAITING_FOR_APPROVAL
} from "../../packageStatus";

const actions = [
  {
    status: [STATUS_WAITING_FOR_APPROVAL],
    payment_status: [PAYMENT_STATUS_DRAFT],
    components: [
      {
        component: ModalCancelConfirm
      }
    ]
  },
  {
    status: [STATUS_CREATED],
    payment_status: [PAYMENT_STATUS_DRAFT],
    components: [
      {
        component: ModalCancelConfirm
      },
      {
        component: ModalAssignPartner
      }
    ]
  },
  {
    status: [STATUS_ACCEPTED],
    payment_status: [PAYMENT_STATUS_PENDING],
    components: [
      {
        component: ModalPaymentConfirm
      }
    ]
  }
];

export { actions };
