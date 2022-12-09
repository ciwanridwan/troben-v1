export default [
  {
    title: "No",
    key: "number",
    dataIndex: "number",
    scopedSlots: { customRender: "number" },
  },
  {
    title: "Nama",
    dataIndex: "name",
    key: "name",
    scopedSlots: { customRender: "name" },
  },
  {
    title: "Nomor Telepon",
    dataIndex: "phone",
    key: "phone",
    scopedSlots: { customRender: "phone" },
  },
  {
    title: "Tanggal",
    dataIndex: "created_at",
    key: "created_at",
    scopedSlots: { customRender: "created_at" },
  },
  {
    title: "Alamat",
    dataIndex: "address",
    key: "address",
    scopedSlots: { customRender: "address" },
  },
  {
    title: "Accept",
    key: "status",
    width: "180px",
    scopedSlots: { customRender: "status" },
  },
];
