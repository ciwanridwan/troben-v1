export default [
    {
      title: "No",
      key: "number",
      dataIndex: "number",
      scopedSlots: { customRender: "number" }
    },
    {
      title: "Order Id",
      key: "id",
      scopedSlots: { customRender: "id" }
    },
    {
      title: "Type",
      key: "type",
      scopedSlots: { customRender: "type" }
    },
    {
      title: "Pembayaran",
      key: "payment",
      scopedSlots: { customRender: "payment" }
    },
    {
      title: "Komisi",
      key: "komisi",
      scopedSlots: { customRender: "komisi" }
    },
  ];
  