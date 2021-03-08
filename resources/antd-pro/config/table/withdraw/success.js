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
    key: "balance",
    dataIndex: "balance",
    scopedSlots: { customRender: "balance" }
  },
  {
    title: "Jumlah Pencairan",
    dataIndex: "withdraw_balance",
    key: "withdraw_balance",
    align: "center",
    scopedSlots: { customRender: "withdraw_balance" }
  },
  {
    title: "Tanggal",
    key: "created_at",
    dataIndex: "created_at",
    align: "center",
    scopedSlots: { customRender: "created_at" }
  },
  {
    title: "status",
    key: "status",
    align: "center",
    width: "100px",
    scopedSlots: { customRender: "status" }
  }
];
