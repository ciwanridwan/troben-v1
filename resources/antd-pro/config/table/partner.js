export default [
  {
    title: "No",
    key: "number",
    dataIndex: "number",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "Jenis Mitra",
    key: "phone",
    scopedSlots: { customRender: "phone" }
  },
  {
    title: "Kode Mitra",
    dataIndex: "name",
    key: "name",
    scopedSlots: { customRender: "name" }
  },
  {
    title: "Nama Owner",
    key: "email",
    scopedSlots: { customRender: "email" }
  },
  {
    title: "No Hp",
    key: "order.count",
    align: "center",
    scopedSlots: { customRender: "count" }
  },
  {
    title: "Email",
    key: "order.total_payment",
    align: "center",
    scopedSlots: { customRender: "payment" }
  },
  {
    title: "Action",
    key: "action",
    align: "center",
    width: "100px",
    scopedSlots: { customRender: "action" }
  }
];
