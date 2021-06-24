import ModalPackageSendToCustomer from "../../../components/orders/actions/modal-package-send-to-customer";
import ModalReceiptToPdf from "../../../components/orders/actions/modal-receipt-to-pdf";
import OrderModal from "../../../components/orders/modal/order-modal";
import {
  PAYMENT_STATUS_DRAFT,
  PAYMENT_STATUS_PAID,
  PAYMENT_STATUS_PENDING,
  STATUS_ACCEPTED,
  STATUS_CANCEL,
  STATUS_CANCEL_DELIVERED,
  STATUS_CANCEL_SELF_PICKUP,
  STATUS_ESTIMATED,
  STATUS_IN_TRANSIT,
  STATUS_REVAMP,
  STATUS_WITH_COURIER
} from "../../packageStatus";

const actions = [
  {
    status: [STATUS_ESTIMATED, STATUS_REVAMP],
    payment_status: [PAYMENT_STATUS_DRAFT],
    component: ModalPackageSendToCustomer
  },
  {
    status: [STATUS_ACCEPTED],
    payment_status: [PAYMENT_STATUS_PENDING],
    component: OrderModal,
    props: {
      modifiable: false
    }
  },
  {
    status: [STATUS_ACCEPTED],
    payment_status: [PAYMENT_STATUS_PAID],
    component: ModalReceiptToPdf
  },
  {
    status: [
      STATUS_CANCEL,
      STATUS_CANCEL_DELIVERED,
      STATUS_CANCEL_SELF_PICKUP,
      STATUS_IN_TRANSIT,
      STATUS_WITH_COURIER
    ],
    payment_status: [PAYMENT_STATUS_PAID],
    component: OrderModal,
    props: {
      modifiable: false
    }
  }
];

export { actions };
