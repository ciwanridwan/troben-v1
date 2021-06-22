const TYPE_PICKUP = "pickup";
const TYPE_RETURN = "return";
const TYPE_TRANSIT = "transit";
const TYPE_DOORING = "dooring";

const STATUS_PENDING = "pending";
const STATUS_ACCEPTED = "accepted";
const STATUS_CANCELLED = "cancelled";
const STATUS_WAITING_ASSIGN_PACKAGE = "waiting_assign_package";
const STATUS_WAITING_ASSIGN_PARTNER = "waiting_assign_partner";
const STATUS_WAITING_PARTNER_ASSIGN_TRANSPORTER =
  "waiting_partner_assign_transporter";
const STATUS_WAITING_ASSIGN_TRANSPORTER = "waiting_assign_transporter";
const STATUS_WAITING_TRANSPORTER = "waiting_transporter";
const STATUS_LOADING = "loading";
const STATUS_EN_ROUTE = "en-route";
const STATUS_FINISHED = "finished";

const types = [
  {
    type: TYPE_RETURN,
    message: "[PENGEMBALIAN]"
  },
  {
    type: TYPE_PICKUP,
    message: "[PENJEMPUTAN]"
  },
  {
    type: TYPE_TRANSIT,
    message: "[TRANSIT]"
  },
  {
    type: TYPE_DOORING,
    message: "[PENGANTARAN]"
  }
];

const statuses = [
  {
    status: STATUS_PENDING,
    messageType: "warning",
    message: "Menunggu Penjemputan"
  },
  {
    status: STATUS_ACCEPTED,
    messageType: "warning",
    message: "Driver ditugaskan"
  },
  {
    status: STATUS_CANCELLED,
    messageType: "danger",
    message: "Pengantaran dibatalkan"
  },
  {
    status: STATUS_WAITING_ASSIGN_PACKAGE,
    messageType: "danger",
    message: "Menunggu Barang Masuk ke Manifest"
  },
  {
    status: STATUS_WAITING_ASSIGN_PARTNER,
    messageType: "danger",
    message: "Menunggu Assign Mitra"
  },
  {
    status: STATUS_WAITING_PARTNER_ASSIGN_TRANSPORTER,
    messageType: "danger",
    message: "Menunggu Mitra Transporter Menugaskan Transporter"
  },
  {
    status: STATUS_WAITING_ASSIGN_TRANSPORTER,
    messageType: "danger",
    message: "Menunggu Mitra Menugaskan Transporter"
  },
  {
    status: STATUS_WAITING_TRANSPORTER,
    messageType: "danger",
    message: "Menunggu Transporter"
  },
  {
    status: STATUS_LOADING,
    messageType: "danger",
    message: "Sedang Melakukan Loading Barang"
  },
  {
    status: STATUS_EN_ROUTE,
    messageType: "danger",
    message: "Sedang Dalam Perjalanan"
  },
  {
    status: STATUS_FINISHED,
    messageType: "danger",
    message: "Sampai di Tujuan"
  }
];

export {
  statuses,
  types,
  TYPE_PICKUP,
  TYPE_RETURN,
  TYPE_TRANSIT,
  TYPE_DOORING,
  STATUS_PENDING,
  STATUS_ACCEPTED,
  STATUS_CANCELLED,
  STATUS_WAITING_ASSIGN_PACKAGE,
  STATUS_WAITING_ASSIGN_PARTNER,
  STATUS_WAITING_PARTNER_ASSIGN_TRANSPORTER,
  STATUS_WAITING_ASSIGN_TRANSPORTER,
  STATUS_WAITING_TRANSPORTER,
  STATUS_LOADING,
  STATUS_EN_ROUTE,
  STATUS_FINISHED
};
