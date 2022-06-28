export default [
  {
    title: "ID",
    key: "number",
    dataIndex: "number",
    align : "center",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "Kode Mitra",
    key: "code",
    dataIndex: "partner.code",
    align : "center",
    scopedSlots: { customRender: "code" }
  },
  // {
  //   title: "Jumlah Request",
  //   dataIndex: "id",
  //   key: "id",

  //   scopedSlots: { customRender: "id" }
  // },
  {
    title: "Jumlah Request",
    key: "first_balance",
    dataIndex: "first_balance",
    align : "center", 
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
    title: "Status",
    key: "action",
    align: "center",
    width: "100px",
    scopedSlots: { customRender: "action" }
  }
];
