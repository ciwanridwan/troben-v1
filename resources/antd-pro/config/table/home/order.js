export default [
  {
    title: "No",
    dataIndex: "number",
  },
  {
    title: "ID Order",
    dataIndex: "barcode",
    key: "barcode",
  },
  {
    title: "Mitra Penerima",
    dataIndex: "receiver_name",
  },
  {
    title: "Lokasi Pengiriman",
    key: "address",
    scopedSlots: { customRender: "address" },
  },
  {
    title: "Order By",
    key: "order_by",
    scopedSlots: { customRender: "order_by" },
    sorter: true,
  },
  {
    title: "Tanggal Order",
    dataIndex: "created_at",
  },
];
