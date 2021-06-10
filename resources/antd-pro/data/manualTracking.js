const trackingTypes = [
  { type: "error", title: "Error" },
  { type: "info", title: "Info" },
  { type: "warning", title: "Peringatan" },
  { type: "neutral", title: "Netral" }
];
const trackingStatusTypes = [
  {
    type: "RCP",
    title: "Receipt"
  },
  {
    type: "MNF",
    title: "Manifest"
  }
];

const trackingStatuses = {
  RCP: {
    statuses: [
      { status: "cancel", title: "Dibatalkan" },
      { status: "created", title: "Dibentuk" },
      { status: "pending", title: "Menunggu" },
      { status: "lost", title: "Hilang" },
      { status: "waiting_for_pickup", title: "Menunggu untuk dijemput" },
      { status: "picked_up", title: "Dijemput" },
      { status: "waiting_for_estimating", title: "Menunggu untuk ditimbang" },
      { status: "estimating", title: "Sedang ditimbang" },
      { status: "estimated", title: "Telah ditimbang" },
      { status: "waiting_for_approval", title: "Menunggu untuk ditimbang" },
      { status: "revamp", title: "Direvisi oleh customer" },
      { status: "accepted", title: "Diterima oleh mitra" },
      { status: "waiting_for_packing", title: "Menunggu untuk dipacking" },
      { status: "packing", title: "Sedang dipacking" },
      { status: "packed", title: "Telah dipacking" },
      { status: "manifested", title: "Telah ditambah kedalam Manifest" },
      { status: "in_transit", title: "Dalam proses transit" },
      { status: "with_courier", title: "Dalam pengantaran kurir" },
      { status: "delivered", title: "Telah diantar" }
    ]
  },
  MNF: {
    types: [
      { type: "pickup", title: "Penjemputan Ke Customer Asal" },
      { type: "return", title: "Pengembalian Ke Customer Asal" },
      { type: "transit", title: "Dalam Proses Transit" },
      { type: "dooring", title: "Pengiriman Ke Customer Tujuan" }
    ],
    statuses: [
      { status: "pending", title: "Menunggu" },
      { status: "accepted", title: "Diterima" },
      { status: "cancelled", title: "Dibatalkan" },
      {
        status: "waiting_assign_package",
        title: "Menunggu masuk ke Manifest"
      },
      {
        status: "waiting_assign_transporter",
        title: "Menunggu driver ditugaskan"
      },
      { status: "waiting_transporter", title: "Menunggu driver" },
      { status: "loading", title: "Sedang melakukan loading barang" },
      { status: "en-route", title: "Sedang dalam perjalanan" },
      { status: "finished", title: "Sampai ke tujuan" }
    ]
  }
};

export { trackingStatuses, trackingTypes, trackingStatusTypes };
