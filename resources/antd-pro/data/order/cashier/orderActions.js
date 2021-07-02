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
  STATUS_PACKED,
  STATUS_REVAMP,
  STATUS_WAITING_FOR_PACKING,
  STATUS_WITH_COURIER
} from "../../packageStatus";

const actions = [
  {
    status: [STATUS_ESTIMATED, STATUS_REVAMP],
    payment_status: [PAYMENT_STATUS_DRAFT],
    components: [
      {
        component: ModalPackageSendToCustomer
      }
    ]
  },
  {
    status: [STATUS_ACCEPTED],
    payment_status: [PAYMENT_STATUS_PENDING],
    components: [
      {
        component: OrderModal
      }
    ],
    props: {
      modifiable: false
    }
  },
  {
    status: [STATUS_ACCEPTED, STATUS_WAITING_FOR_PACKING],
    payment_status: [PAYMENT_STATUS_PAID],
    components: [
      {
        component: ModalReceiptToPdf
      }
    ]
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
    components: [
      {
        component: OrderModal
      }
    ],
    props: {
      modifiable: false
    }
  }
];

export { actions };
