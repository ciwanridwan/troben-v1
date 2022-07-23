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
      title: "Pencairan Saldo",
      key: "saldo",
      scopedSlots: { customRender: "saldo" }
    },
    {
      title: "Periode",
      key: "periode",
      scopedSlots: { customRender: "periode" }
    },
    {
      title: "Status",
      key: "status",
      scopedSlots: { customRender: "status" }
    }
  ];
  