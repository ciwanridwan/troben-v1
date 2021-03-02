export default [
  {
    title: "No",
    key: "number",
    dataIndex: "number",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "No HP",
    key: "phone",
    dataIndex: "phone",
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
    dataIndex: "email",
    scopedSlots: { customRender: "email" }
  },
  {
    title: "Jumlah Order",
    dataIndex: "package.count",
    key: "package.count",
    align: "center",
    scopedSlots: { customRender: "count" }
  },
  {
    title: "Invoice",
    key: "package.payment",
    dataIndex: "package.payment",
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
