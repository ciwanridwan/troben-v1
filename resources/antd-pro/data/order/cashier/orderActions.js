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
  STATUS_PACKING,
  STATUS_PACKED,
  STATUS_MANIFESTED,
  STATUS_WAITING_FOR_APPROVAL,
  STATUS_REVAMP,
  STATUS_WAITING_FOR_PACKING,
  STATUS_WITH_COURIER,
  STATUS_DELIVERED,
  STATUS_WAITING_FOR_PAYMENT
} from "../../packageStatus";

const actions = [
  {
    status: [
      STATUS_ESTIMATED,
      STATUS_REVAMP
    ],
    payment_status: [PAYMENT_STATUS_DRAFT],
    components: [
      {
        component: ModalPackageSendToCustomer
      }
    ]
  },
  {
    status: [
      STATUS_WAITING_FOR_APPROVAL
    ],
    payment_status: [PAYMENT_STATUS_DRAFT],
    components: [
      {
        component: OrderModal
      }
    ],
  },
  {
    status: [
      STATUS_ACCEPTED,
      STATUS_WAITING_FOR_PAYMENT,
      STATUS_WAITING_FOR_APPROVAL
    ],
    payment_status: [PAYMENT_STATUS_PENDING],
    components: [
      {
        component: OrderModal
      }
    ],
    // props: {
    //   modifiable: false
    // }
  },
  {
    status: [
      STATUS_CANCEL,
      STATUS_CANCEL_DELIVERED,
      STATUS_CANCEL_SELF_PICKUP,
    ],
    payment_status: [PAYMENT_STATUS_PAID],
    components: [
      {
        component: OrderModal
      }
    ],
    // props: {
    //   modifiable: false
    // }
  },
  {
    status: [
      STATUS_WAITING_FOR_PACKING,
      STATUS_PACKING,
      STATUS_PACKED,
      STATUS_MANIFESTED,
      STATUS_IN_TRANSIT,
      STATUS_WITH_COURIER,
      STATUS_DELIVERED
    ],
    payment_status: [PAYMENT_STATUS_PAID],
    components: [
      {
        component: OrderModal
      },
      {
        component: ModalReceiptToPdf
      }
    ],
    // props: {
    //   modifiable: false
    // },
  }
];

export { actions };
