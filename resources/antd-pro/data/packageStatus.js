const STATUS_CANCEL = "cancel";
const STATUS_LOST = "lost";
const STATUS_CREATED = "created";
const STATUS_PENDING = "pending";
const STATUS_WAITING_FOR_PICKUP = "waiting_for_pickup";
const STATUS_PICKED_UP = "picked_up";
const STATUS_WAITING_FOR_ESTIMATING = "waiting_for_estimating";
const STATUS_ESTIMATING = "estimating";
const STATUS_ESTIMATED = "estimated";
const STATUS_WAITING_FOR_APPROVAL = "waiting_for_approval";
const STATUS_REVAMP = "revamp";
const STATUS_ACCEPTED = "accepted";
const STATUS_WAITING_FOR_PACKING = "waiting_for_packing";
const STATUS_WAITING_FOR_PAYMENT = "waiting_for_payment";
const STATUS_PACKING = "packing";
const STATUS_PACKED = "packed";
const STATUS_MANIFESTED = "manifested";
const STATUS_IN_TRANSIT = "in_transit";
const STATUS_WITH_COURIER = "with_courier";
const STATUS_DELIVERED = "delivered";
const STATUS_CANCEL_SELF_PICKUP = "cancel_self_pickup";
const STATUS_CANCEL_DELIVERED = "cancel_delivered";
const STATUS_FINISHED = "finished";
const STATUS_REJECTED = "rejected";

const PAYMENT_STATUS_DRAFT = "draft";
const PAYMENT_STATUS_PENDING = "pending";
const PAYMENT_STATUS_PAID = "paid";
const PAYMENT_STATUS_FAILED = "failed";

const TYPE_WALKIN = "walkin";
const TYPE_APP = "app";

const statuses = [
  {
    status: STATUS_CANCEL,
    messageType: "warning",
    message: "Pesanan dibatalkan"
  },
  {
    status: STATUS_LOST,
    messageType: "warning",
    message: "Pesanan hilang"
  },
  {
    status: STATUS_CREATED,
    messageType: "warning",
    message: "Pesanan dibuat"
  },
  {
    status: STATUS_PENDING,
    messageType: "warning",
    message: "Menunggu driver ditugaskan"
  },
  {
    status: STATUS_WAITING_FOR_PICKUP,
    messageType: "warning",
    message: "Pesanan menunggu dijemput"
  },
  {
    status: STATUS_PICKED_UP,
    messageType: "warning",
    message: "Pesanan dijemput"
  },
  {
    status: STATUS_PICKED_UP,
    messageType: "warning",
    message: "Pesanan menunggu untuk diukur dan timbang"
  },
  {
    status: STATUS_ESTIMATING,
    messageType: "warning",
    message: "Sedang dihitung di Gudang"
  },
  {
    status: STATUS_ESTIMATED,
    messageType: "warning",
    message: "Telah dihitung di Gudang"
  },
  {
    status: STATUS_WAITING_FOR_APPROVAL,
    messageType: "warning",
    message: "Menunggu Konfirmasi Customer"
  },
  {
    status: STATUS_REVAMP,
    messageType: "warning",
    message: "Revisi"
  },
  {
    status: STATUS_ACCEPTED,
    messageType: "warning",
    message: "Pesanan diterima mitra"
  },
  {
    status: STATUS_WAITING_FOR_PAYMENT,
    messageType: "warning",
    message: "Customer Melakukan Proses Pembayaran"
  },
  {
    status: STATUS_WAITING_FOR_PACKING,
    messageType: "warning",
    message: "Pesanan diterima mitra"
  },
  {
    status: STATUS_PACKING,
    messageType: "warning",
    message: "Pesanan sedang dikemas"
  },
  {
    status: STATUS_PACKED,
    messageType: "warning",
    message: "Pesanan telah dikemas"
  },
  {
    status: STATUS_MANIFESTED,
    messageType: "warning",
    message: "Pesanan telah masuk dalam manifest"
  },
  {
    status: STATUS_IN_TRANSIT,
    messageType: "warning",
    message: "Pesanan dalam perjalanan"
  },
  {
    status: STATUS_WITH_COURIER,
    messageType: "warning",
    message: "Pesanan dalam pengantaran kurir"
  },
  {
    status: STATUS_DELIVERED,
    messageType: "warning",
    message: "Pesanan telah sampai tujuan"
  },
  {
    status: STATUS_FINISHED,
    messageType: "warning",
    message: "Pesanan telah sampai tujuan"
  },
  {
    status: STATUS_REJECTED,
    messageType: "warning",
    message: "Pesanan telah dibatalkan"
  },
  {
    status: STATUS_CANCEL_SELF_PICKUP,
    messageType: "warning",
    message: "Pesanan dibatalkan | diambil sendiri"
  },
  {
    status: STATUS_CANCEL_DELIVERED,
    messageType: "warning",
    message: "Pesanan dibatalkan | diantar"
  }
];

const paymentStatuses = [
  {
    payment_status: PAYMENT_STATUS_DRAFT,
    title: "Draft Pembayaran"
  },
  {
    payment_status: PAYMENT_STATUS_PENDING,
    title: "Menunggu pembayaran"
  },
  {
    payment_status: PAYMENT_STATUS_PENDING,
    title: "Terbayar"
  },
  {
    payment_status: PAYMENT_STATUS_FAILED,
    title: "Pembayaran Gagal"
  }
];

const types = [
  {
    type: TYPE_WALKIN,
    title: "Walk In"
  },
  {
    type: TYPE_APP,
    title: "Aplikasi"
  }
];

export {
  STATUS_CANCEL,
  STATUS_LOST,
  STATUS_CREATED,
  STATUS_PENDING,
  STATUS_WAITING_FOR_PICKUP,
  STATUS_PICKED_UP,
  STATUS_WAITING_FOR_ESTIMATING,
  STATUS_ESTIMATING,
  STATUS_ESTIMATED,
  STATUS_WAITING_FOR_APPROVAL,
  STATUS_REVAMP,
  STATUS_ACCEPTED,
  STATUS_WAITING_FOR_PAYMENT,
  STATUS_WAITING_FOR_PACKING,
  STATUS_PACKING,
  STATUS_PACKED,
  STATUS_MANIFESTED,
  STATUS_IN_TRANSIT,
  STATUS_WITH_COURIER,
  STATUS_DELIVERED,
  STATUS_FINISHED,
  STATUS_REJECTED,
  STATUS_CANCEL_SELF_PICKUP,
  STATUS_CANCEL_DELIVERED,
  PAYMENT_STATUS_DRAFT,
  PAYMENT_STATUS_PENDING,
  PAYMENT_STATUS_PAID,
  PAYMENT_STATUS_FAILED,
  statuses,
  types,
  paymentStatuses
};
