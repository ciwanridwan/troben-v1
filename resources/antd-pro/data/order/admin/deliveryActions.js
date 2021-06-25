import {
  STATUS_WAITING_ASSIGN_PARTNER,
  TYPE_DOORING,
  TYPE_TRANSIT
} from "../../../data/deliveryStatus";
import ModalAssignPartnerTransporter from "../../../components/modals/modal-assign-partner-transporter.vue";

const actions = [
  {
    type: [TYPE_TRANSIT, TYPE_DOORING],
    status: [STATUS_WAITING_ASSIGN_PARTNER],
    components: [
      {
        component: ModalAssignPartnerTransporter
      }
    ]
  }
];

export { actions };
