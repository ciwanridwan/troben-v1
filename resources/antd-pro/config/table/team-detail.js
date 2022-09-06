export default [
    {
      title: "No",
      key: "number",
      dataIndex: "number",
      scopedSlots: { customRender: "number" }
    },
    {
      title: "Nama",
      key: "name",
      scopedSlots: { customRender: "name" }
    },
    {
      title: "Voucher Share",
      key: "voucher",
      scopedSlots: { customRender: "voucher" }
    },
    {
      title: "Pendapatan",
      key: "income",
      scopedSlots: { customRender: "income" }
    },
  ];
  