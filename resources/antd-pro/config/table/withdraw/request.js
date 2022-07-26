export default [
  {
    title: "No",
    key: "number",
    dataIndex: "number",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "Kode Mitra",
    key: "code",
    scopedSlots: { customRender: "code" }
  },
  {
    title: "Jumlah Request",
    key: "first_balance",
    scopedSlots: { customRender: "first_balance" }
  },
  {
    title: "Jumlah Pencairan",
    key: "amount",
    scopedSlots: { customRender: "amount" }
  },
  {
    title: "Tanggal",
    key: "created_at",
    scopedSlots: { customRender: "created_at" }
  },
  {
    title: "Status",
    key: "action",
    scopedSlots: { customRender: "action" }
  }
];
