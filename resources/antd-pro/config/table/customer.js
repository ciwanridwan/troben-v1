export default [
  {
    title: "No",
    key: "id",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "No HP",
    key: "phone",
    scopedSlots: { customRender: "phone" }
  },
  {
    title: "Name",
    dataIndex: "name",
    key: "name",
    scopedSlots: { customRender: "name" }
  },
  {
    title: "Email",
    key: "email",
    scopedSlots: { customRender: "email" }
  },
  {
    title: "Jumlah Order",
    key: "order.count",
    scopedSlots: { customRender: "count" }
  },
  {
    title: "Invoice",
    key: "order.total_payment",
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
