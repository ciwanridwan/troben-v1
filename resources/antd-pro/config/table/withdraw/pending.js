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
    dataIndex: "partner.code",
    scopedSlots: { customRender: "code" }
  },
  {
    title: "ID Request",
    dataIndex: "id",
    key: "id",

    scopedSlots: { customRender: "id" }
  },
  {
    title: "Saldo",
    key: "first_balance",
    dataIndex: "first_balance",
    scopedSlots: { customRender: "first_balance" }
  },
  {
    title: "Jumlah Pencairan",
    dataIndex: "amount",
    key: "amount",
    align: "center",
    scopedSlots: { customRender: "amount" }
  },
  {
    title: "Tanggal",
    key: "created_at",
    dataIndex: "created_at",
    align: "center",
    scopedSlots: { customRender: "created_at" }
  },
  {
    title: "Action",
    key: "action",
    align: "center",
    width: "100px",
    scopedSlots: { customRender: "action" }
  }
];
